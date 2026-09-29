<?php

use App\Models\Epreuve;
use App\Models\Question;
use App\Support\Nclc;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new class extends Component
{
    /** Durée par défaut d'une épreuve si elle n'en définit pas, en minutes. */
    public const DUREE_PAR_DEFAUT = 30;

    /** Épreuve du test, ou null pour le test complet qui enchaîne toutes les épreuves. */
    #[Locked]
    public ?Epreuve $epreuve = null;

    /** @var list<int> questions, regroupées par épreuve puis par catégorie */
    #[Locked]
    public array $ids = [];

    #[Locked]
    public int $index = 0;

    /** @var array<int, int> index de question => choix */
    #[Locked]
    public array $reponses = [];

    /** @var list<int> index des questions marquées « à revoir » */
    #[Locked]
    public array $marquees = [];

    /** @var list<int> index des questions dont l'audio a déjà été lancé */
    #[Locked]
    public array $ecoutees = [];

    /** Horodatage Unix de fin du chrono. */
    #[Locked]
    public int $fin = 0;

    #[Locked]
    public bool $termine = false;

    public function mount(?Epreuve $epreuve = null): void
    {
        $this->epreuve = $epreuve;

        $epreuves = $this->epreuves->load('questions');

        // Les épreuves se suivent dans leur ordre ; dans chacune, les catégories
        // apparaissent dans l'ordre de leur première question.
        $this->ids = $epreuves
            ->flatMap(fn (Epreuve $e) => $e->questions->groupBy('categorie')->flatMap(fn (Collection $questions) => $questions->pluck('id')))
            ->values()
            ->all();

        $minutes = $epreuves->sum(fn (Epreuve $e) => $e->duree_test ?? self::DUREE_PAR_DEFAUT);
        $this->fin = now()->addMinutes($minutes)->getTimestamp();
    }

    public function render()
    {
        return $this->view()->title($this->epreuve ? 'Test blanc · '.$this->epreuve->nom : 'Test blanc complet');
    }

    /** @return Collection<int, Epreuve> épreuves du test (avec des questions), par id */
    #[Computed]
    public function epreuves(): Collection
    {
        return Epreuve::query()
            ->when($this->epreuve, fn ($requete) => $requete->whereKey($this->epreuve->id))
            ->has('questions')
            ->orderBy('ordre')
            ->get()
            ->keyBy('id');
    }

    /** @return Collection<int, Question> */
    #[Computed]
    public function questions(): Collection
    {
        $questions = Question::findMany($this->ids)->keyBy('id');

        return collect($this->ids)->map(fn (int $id) => $questions[$id])->values();
    }

    /** @return Collection<int, Collection<string, Collection<int, Question>>> épreuve => catégorie => [index => question] */
    #[Computed]
    public function sections(): Collection
    {
        return $this->questions
            ->groupBy('epreuve_id', preserveKeys: true)
            ->map(fn (Collection $questions) => $questions->groupBy('categorie', preserveKeys: true));
    }

    #[Computed]
    public function question(): ?Question
    {
        return $this->questions[$this->index] ?? null;
    }

    public function aller(int $index): void
    {
        if ($this->termine || ! isset($this->ids[$index])) {
            return;
        }

        $this->index = $index;
    }

    public function precedente(): void
    {
        $this->aller($this->index - 1);
    }

    public function suivante(): void
    {
        $this->aller($this->index + 1);
    }

    public function choisir(int $choix): void
    {
        if ($this->verrouille() || ! isset($this->question->choix[$choix])) {
            return;
        }

        $this->reponses[$this->index] = $choix;
    }

    public function effacer(): void
    {
        if ($this->verrouille()) {
            return;
        }

        unset($this->reponses[$this->index]);
    }

    public function basculerMarque(): void
    {
        if ($this->verrouille()) {
            return;
        }

        $this->marquees = in_array($this->index, $this->marquees, true)
            ? array_values(array_diff($this->marquees, [$this->index]))
            : [...$this->marquees, $this->index];
    }

    /** L'audio a été lancé : il reste bloqué même si on revient sur la question. */
    #[Renderless]
    public function marquerEcoutee(int $index): void
    {
        if (isset($this->ids[$index]) && ! in_array($index, $this->ecoutees, true)) {
            $this->ecoutees[] = $index;
        }
    }

    public function terminer(): void
    {
        $this->termine = true;
    }

    /** Quitte le test sans résultat : les réponses ne sont pas gardées. */
    public function abandonner(): void
    {
        $this->redirectRoute('espace', navigate: true);
    }

    /**
     * @return array{
     *     bonnes: int,
     *     total: int,
     *     epreuves: array<int, array{bonnes: int, total: int, score: ?int, niveau: ?string, categories: array<string, array{bonnes: int, total: int}>}>
     * }
     */
    #[Computed]
    public function resultat(): array
    {
        $epreuves = [];

        foreach ($this->questions as $i => $question) {
            $correcte = (int) $question->estCorrecte($this->reponses[$i] ?? null);
            $stats = $epreuves[$question->epreuve_id] ?? ['bonnes' => 0, 'total' => 0, 'categories' => []];
            $categorie = $stats['categories'][$question->categorie] ?? ['bonnes' => 0, 'total' => 0];

            $stats['bonnes'] += $correcte;
            $stats['total']++;
            $stats['categories'][$question->categorie] = ['bonnes' => $categorie['bonnes'] + $correcte, 'total' => $categorie['total'] + 1];
            $epreuves[$question->epreuve_id] = $stats;
        }

        foreach ($epreuves as $id => $stats) {
            $code = $this->epreuves[$id]->code;
            $score = isset(Nclc::EPREUVES[$code]) ? (int) round($stats['bonnes'] / $stats['total'] * Nclc::maximum($code)) : null;

            $epreuves[$id] += [
                'score' => $score,
                'niveau' => $score === null ? null : Nclc::niveauPour($code, $score),
            ];
        }

        return [
            'bonnes' => array_sum(array_column($epreuves, 'bonnes')),
            'total' => count($this->ids),
            'epreuves' => $epreuves,
        ];
    }

    private function verrouille(): bool
    {
        if ($this->termine) {
            return true;
        }

        // Petite marge pour la latence réseau.
        if (now()->getTimestamp() > $this->fin + 5) {
            $this->terminer();

            return true;
        }

        return false;
    }
};
?>

<div class="flex min-h-dvh flex-col bg-papier text-foret"
    x-data="{
        panneau: false,
        volet: $persist(true).as('test-blanc-volet'),
        confirmer: false,
        basculerVolet() {
            if (window.matchMedia('(min-width: 64rem)').matches) this.volet = ! this.volet;
            else this.panneau = ! this.panneau;
        },
    }"
    @keydown.escape.window="panneau = false; confirmer = false">
    @php($lettres = ['A', 'B', 'C', 'D', 'E', 'F'])
    @php($total = count($ids))
    @php($enCours = ! $termine && $total > 0)
    @php($complet = $epreuve === null)

    {{-- En-tête : pas de sortie directe, on passe par « Terminer » (qui propose d'abandonner). --}}
    <header class="sticky top-0 z-30 bg-foret pt-[env(safe-area-inset-top)] text-white">
        <div class="flex h-16 items-center gap-2 px-3 md:gap-3 md:px-5">
            @if ($enCours)
                <button type="button" @click="basculerVolet()" aria-controls="panneau-questions"
                    :aria-expanded="(panneau || (volet && window.matchMedia('(min-width: 64rem)').matches)).toString()"
                    aria-label="Afficher ou masquer la liste des questions"
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl text-white/80 transition-colors hover:bg-white/10 hover:text-white">
                    <x-icone nom="menu" class="size-5" />
                </button>
            @else
                <x-logo inverse class="ml-1 size-8 shrink-0" />
            @endif

            <div class="flex min-w-0 flex-1 flex-col">
                <span class="truncate text-[11px] font-bold tracking-[0.14em] text-peche uppercase">{{ $complet ? 'Test blanc complet' : 'Test blanc' }}</span>
                <span class="truncate text-[15px] leading-tight font-bold">{{ $complet ? 'Toutes les épreuves' : $epreuve->nom }}</span>
            </div>

            @if ($enCours)
                <span class="hidden text-sm font-semibold text-white/70 tabular-nums md:inline">{{ count($reponses) }} / {{ $total }} répondues</span>
                <div x-data="chrono({{ $fin }}, () => $wire.terminer())" role="timer" aria-label="Temps restant"
                    class="flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-[15px] font-bold tabular-nums transition-colors"
                    :class="reste <= 300 ? 'bg-peche text-foret' : 'bg-white/10'">
                    <x-icone nom="horloge" class="size-[18px]" />
                    <span x-text="affichage">{{ gmdate('i:s', max(0, $fin - now()->getTimestamp())) }}</span>
                </div>
                <button type="button" @click="confirmer = true" class="h-10 shrink-0 rounded-xl bg-peche px-3.5 text-sm font-bold text-foret transition-colors hover:bg-white sm:px-4">Terminer</button>
            @endif
        </div>

        @if ($enCours)
            <div class="h-1 bg-white/10" aria-hidden="true">
                <div class="h-1 bg-peche transition-[width] duration-500" style="width: {{ count($reponses) / $total * 100 }}%"></div>
            </div>
        @endif
    </header>

    @if (! $total)
        <main class="flex flex-1 flex-col items-center justify-center gap-4 p-5 text-center">
            <h1 class="font-titre text-[22px] leading-tight">Pas encore de test blanc {{ $complet ? 'disponible' : 'pour cette épreuve' }}</h1>
            <a href="{{ route('espace') }}" wire:navigate class="flex h-12 items-center rounded-2xl bg-foret px-6 font-bold text-white hover:text-white">Retour à mon espace</a>
        </main>
    @elseif ($termine)
        {{-- Résultats --}}
        @php($resultat = $this->resultat)

        <main class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-8 px-4 py-8 md:py-12">
            <section class="relative isolate flex flex-col gap-4 overflow-hidden rounded-[28px] bg-foret px-6 py-8 text-white md:px-10 md:py-10">
                <x-arcs couleur="#FFB59E" class="-top-[110px] -right-[110px] size-[320px]" />
                <div class="text-[12px] font-bold tracking-[0.14em] text-peche uppercase">Test blanc terminé</div>
                <h1 class="font-titre text-[26px] leading-tight tracking-[-0.5px] md:text-[32px]">{{ $complet ? 'Toutes les épreuves' : $epreuve->nom }}</h1>
                <div class="flex flex-wrap items-end gap-x-8 gap-y-3">
                    <div class="flex flex-col gap-1">
                        <div class="font-titre text-[60px] leading-none tracking-[-1.5px] md:text-[72px]">{{ $resultat['bonnes'] }}<span class="text-[26px] text-white/50 md:text-[32px]"> / {{ $resultat['total'] }}</span></div>
                        <div class="text-sm font-semibold text-white/70">bonnes réponses</div>
                    </div>
                    @if (! $complet && ($stats = $resultat['epreuves'][$epreuve->id] ?? null) && $stats['score'] !== null)
                        <div class="flex flex-col gap-1 pb-1">
                            <span class="self-start rounded-full bg-peche px-3.5 py-1 text-sm font-extrabold text-foret">{{ $stats['niveau'] ? 'NCLC '.$stats['niveau'] : 'Sous le NCLC 5' }}</span>
                            <span class="text-sm font-semibold text-white/70">Score estimé : {{ $stats['score'] }} / {{ \App\Support\Nclc::maximum($epreuve->code) }}</span>
                        </div>
                    @endif
                </div>
                <p class="text-[13px] text-white/55">Estimation indicative calculée sur des questions d'entraînement.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="font-titre text-[22px] tracking-[-0.5px]">{{ $complet ? 'Par épreuve' : 'Par catégorie' }}</h2>
                <div @class(['grid gap-3', 'md:grid-cols-2' => $complet])>
                    @foreach ($resultat['epreuves'] as $epreuveId => $stats)
                        @php($ep = $this->epreuves[$epreuveId])
                        <article wire:key="resultat-{{ $epreuveId }}" class="flex flex-col gap-4 rounded-[20px] border-[1.5px] border-trait bg-white p-5">
                            <div class="flex items-center gap-3">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-[14px] bg-foret text-peche"><x-icone :nom="$ep->icone" class="size-5" /></span>
                                <div class="flex min-w-0 flex-1 flex-col">
                                    <span class="truncate text-[16px] font-bold">{{ $ep->nom }}</span>
                                    <span class="text-sm text-cendre tabular-nums">
                                        {{ $stats['bonnes'] }} / {{ $stats['total'] }} bonnes réponses
                                        @if ($stats['score'] !== null) · {{ $stats['score'] }} / {{ \App\Support\Nclc::maximum($ep->code) }} @endif
                                    </span>
                                </div>
                                @if ($stats['score'] !== null)
                                    <span class="shrink-0 rounded-full bg-peche-clair px-3 py-1 text-[13px] font-extrabold">{{ $stats['niveau'] ? 'NCLC '.$stats['niveau'] : '< NCLC 5' }}</span>
                                @endif
                            </div>
                            <div @class(['grid gap-3', 'sm:grid-cols-2' => ! $complet])>
                                @foreach ($stats['categories'] as $categorie => $parCategorie)
                                    <div wire:key="stat-{{ $epreuveId }}-{{ $loop->index }}" class="flex flex-col gap-1.5">
                                        <div class="flex justify-between gap-2 text-sm font-semibold"><span>{{ $categorie }}</span><span class="text-cendre tabular-nums">{{ $parCategorie['bonnes'] }} / {{ $parCategorie['total'] }}</span></div>
                                        <div class="h-2 rounded-full bg-papier"><div class="h-2 rounded-full bg-foret" style="width: {{ $parCategorie['bonnes'] / $parCategorie['total'] * 100 }}%"></div></div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="font-titre text-[22px] tracking-[-0.5px]">Correction</h2>
                @foreach ($this->sections as $epreuveId => $categories)
                    <div wire:key="correction-epreuve-{{ $epreuveId }}" class="flex flex-col gap-2">
                        @if ($complet)
                            <h3 class="mt-2 text-[13px] font-bold tracking-[0.12em] text-cendre uppercase">{{ $this->epreuves[$epreuveId]->nom }}</h3>
                        @endif
                        <ol class="flex flex-col gap-2">
                            @foreach ($categories->flatten(1) as $q)
                                @php($i = array_search($q->id, $ids, true))
                                @php($choix = $reponses[$i] ?? null)
                                <li wire:key="correction-{{ $q->id }}" class="flex items-start gap-3 rounded-2xl border-[1.5px] border-trait bg-white px-4 py-3.5">
                                    @if ($q->estCorrecte($choix))
                                        <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-foret text-white"><x-icone nom="coche" :epaisseur="3" class="size-4" /></span>
                                    @elseif ($choix !== null)
                                        <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-peche text-foret"><x-icone nom="croix" :epaisseur="3" class="size-4" /></span>
                                    @else
                                        <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-papier text-[13px] font-bold text-cendre">–</span>
                                    @endif
                                    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                        <span class="text-[12px] font-bold text-cendre">Q{{ $i + 1 }} · {{ $q->categorie }}</span>
                                        <span class="text-[15px] font-semibold">{{ $q->enonce }}</span>
                                        <span class="text-[13px] text-cendre">
                                            @if ($choix === null) Sans réponse · @elseif (! $q->estCorrecte($choix)) Ta réponse : {{ $q->choix[$choix] ?? '?' }} · @endif
                                            Bonne réponse : <span class="font-bold text-foret">{{ $q->choix[$q->bonne_reponse] }}</span>
                                        </span>
                                        <span class="mt-1 text-[13px] leading-normal">{{ $q->explication }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            </section>

            <div class="flex flex-col gap-2.5 sm:flex-row">
                <a href="{{ $complet ? route('test-blanc.complet') : route('test-blanc', $epreuve) }}" wire:navigate class="flex h-14 flex-1 items-center justify-center gap-2 rounded-2xl bg-foret text-[17px] font-bold text-white hover:bg-foret/90 hover:text-white">
                    <x-icone nom="relancer" class="size-5" />Recommencer
                </a>
                <a href="{{ route('espace') }}" wire:navigate class="flex h-14 flex-1 items-center justify-center rounded-2xl border-[1.5px] border-foret bg-white text-base font-bold text-foret hover:bg-peche-clair">Retour à mon espace</a>
            </div>
        </main>
    @else
        @php($question = $this->question)
        @php($choix = $reponses[$index] ?? null)
        @php($marquee = in_array($index, $marquees, true))

        {{-- Avertit avant de recharger ou fermer la page en plein test. --}}
        <div class="flex flex-1" @beforeunload.window="$event.preventDefault(); $event.returnValue = ''">
            {{-- Volet des questions : tiroir sur mobile, rétractable sur grand écran --}}
            <div x-show="panneau" x-cloak x-transition.opacity @click="panneau = false" class="fixed inset-0 z-40 bg-foret/40 lg:hidden"></div>
            <aside id="panneau-questions" aria-label="Liste des questions"
                :class="{ 'max-lg:translate-x-0!': panneau, 'lg:w-14': ! volet }"
                class="fixed inset-y-0 left-0 z-50 w-[300px] shrink-0 overflow-hidden border-r-[1.5px] border-trait bg-white transition-[translate,width] duration-300 ease-ressort max-lg:-translate-x-full lg:sticky lg:top-[68px] lg:z-0 lg:h-[calc(100dvh-68px)]">
                {{-- Volet replié (grand écran) : une fine bande reste visible, la flèche le rouvre --}}
                <div x-show="! volet" x-cloak class="absolute inset-0 hidden flex-col items-center gap-4 pt-4 lg:flex">
                    <button type="button" @click="volet = true" aria-controls="panneau-questions" aria-label="Afficher la liste des questions"
                        class="flex size-9 items-center justify-center rounded-lg text-cendre hover:bg-papier hover:text-foret">
                        <x-icone nom="droite" class="size-5" />
                    </button>
                    <span class="text-[12px] font-bold text-cendre tabular-nums [writing-mode:vertical-rl]">{{ count($reponses) }} / {{ $total }}</span>
                </div>

                <div :class="{ 'lg:invisible lg:opacity-0': ! volet }" class="flex h-full w-[300px] flex-col transition-[opacity,visibility] duration-200">
                    <div class="flex items-center justify-between gap-2 px-4 pt-4 pb-3">
                        <div class="flex flex-col">
                            <span class="text-[15px] font-extrabold">Questions</span>
                            <span class="text-[12px] font-semibold text-cendre tabular-nums">{{ count($reponses) }} sur {{ $total }} répondues</span>
                        </div>
                        <button type="button" @click="panneau = false; volet = false" aria-label="Masquer la liste des questions"
                            class="flex size-9 items-center justify-center rounded-lg text-cendre hover:bg-papier hover:text-foret">
                            <x-icone nom="gauche" class="size-5" />
                        </button>
                    </div>

                    <div class="flex flex-1 flex-col gap-5 overflow-y-auto px-4 pb-4">
                        @foreach ($this->sections as $epreuveId => $categories)
                            <section wire:key="section-{{ $epreuveId }}" class="flex flex-col gap-3.5">
                                @if ($complet)
                                    @php($ep = $this->epreuves[$epreuveId])
                                    <div class="flex items-center gap-2 border-b-[1.5px] border-trait pb-2">
                                        <span class="flex size-7 items-center justify-center rounded-lg bg-foret text-peche"><x-icone :nom="$ep->icone" class="size-4" /></span>
                                        <span class="text-sm font-extrabold">{{ $ep->nom }}</span>
                                    </div>
                                @endif

                                @foreach ($categories as $categorie => $questionsCategorie)
                                    @php($repondues = $questionsCategorie->keys()->filter(fn ($i) => isset($reponses[$i]))->count())
                                    <div wire:key="categorie-{{ $epreuveId }}-{{ $loop->index }}" class="flex flex-col gap-2">
                                        <div class="flex items-baseline justify-between gap-2">
                                            <span class="text-[12px] font-bold tracking-[0.08em] text-cendre uppercase">{{ $categorie }}</span>
                                            <span class="text-[12px] font-semibold text-cendre tabular-nums">{{ $repondues }}/{{ $questionsCategorie->count() }}</span>
                                        </div>
                                        <div class="grid grid-cols-5 gap-1.5">
                                            @foreach ($questionsCategorie as $i => $q)
                                                <button type="button" wire:key="nav-{{ $q->id }}" wire:click="aller({{ $i }})" @click="panneau = false"
                                                    aria-label="Question {{ $i + 1 }}{{ isset($reponses[$i]) ? ', répondue' : '' }}{{ in_array($i, $marquees, true) ? ', à revoir' : '' }}"
                                                    @if ($i === $index) aria-current="step" @endif
                                                    @class([
                                                        'relative flex h-10 items-center justify-center rounded-lg text-sm font-bold tabular-nums transition-colors',
                                                        'bg-foret text-white' => isset($reponses[$i]),
                                                        'border-[1.5px] border-trait bg-white text-foret hover:border-cendre' => ! isset($reponses[$i]),
                                                        'outline-2 outline-offset-2 outline-peche' => $i === $index,
                                                    ])>
                                                    {{ $i + 1 }}
                                                    @if (in_array($i, $marquees, true))
                                                        <span class="absolute -top-1 -right-1 size-3 rounded-full border-2 border-white bg-peche"></span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </section>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap gap-x-4 gap-y-1.5 border-t-[1.5px] border-trait px-4 py-3 text-[12px] text-cendre">
                        <div class="flex items-center gap-1.5"><span class="size-3 rounded bg-foret"></span>Répondue</div>
                        <div class="flex items-center gap-1.5"><span class="size-3 rounded border-[1.5px] border-trait"></span>Sans réponse</div>
                        <div class="flex items-center gap-1.5"><span class="size-3 rounded-full bg-peche"></span>À revoir</div>
                    </div>
                </div>
            </aside>

            {{-- Question en cours --}}
            <div class="flex min-w-0 flex-1 flex-col">
                <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-5 px-4 py-6 md:py-10" wire:key="question-{{ $question->id }}">
                    <div class="flex flex-wrap items-center gap-2 text-[13px] font-bold">
                        <span class="rounded-full bg-foret px-3 py-1 text-white tabular-nums">Question {{ $index + 1 }} / {{ $total }}</span>
                        @if ($complet)
                            <span class="rounded-full bg-peche-clair px-3 py-1">{{ $this->epreuves[$question->epreuve_id]->nom }}</span>
                        @endif
                        <span class="text-cendre">{{ $question->categorie }}</span>
                    </div>

                    @if ($question->transcription || $question->audio)
                        <x-lecteur :question="$question" fond="border-[1.5px] border-trait bg-white" :ecoutee="in_array($index, $ecoutees, true)" :sur-debut="'() => $wire.marquerEcoutee('.$index.')'" />
                    @endif

                    @if ($question->support)
                        <div class="rounded-[20px] border-[1.5px] border-trait bg-white p-5 text-[15px] leading-[1.65] whitespace-pre-line">{{ $question->support }}</div>
                    @endif

                    <h1 id="enonce" class="font-titre text-[22px] leading-[1.2] tracking-[-0.5px]">{{ $question->enonce }}</h1>

                    <div role="radiogroup" aria-labelledby="enonce" class="flex flex-col gap-2.5">
                        @foreach ($question->choix as $i => $libelle)
                            <button type="button" role="radio" aria-checked="{{ $choix === $i ? 'true' : 'false' }}" wire:click="choisir({{ $i }})" @class([
                                'flex min-h-14 items-center gap-3 rounded-2xl px-4 text-left text-base text-foret transition-colors',
                                'border-2 border-foret bg-peche-clair font-bold' => $choix === $i,
                                'border-[1.5px] border-trait bg-white hover:border-cendre' => $choix !== $i,
                            ])>
                                <span @class([
                                    'flex size-7 shrink-0 items-center justify-center rounded-lg text-sm font-bold',
                                    'bg-foret text-white' => $choix === $i,
                                    'bg-papier' => $choix !== $i,
                                ])>{{ $lettres[$i] }}</span>
                                <span class="py-3">{{ $libelle }}</span>
                            </button>
                        @endforeach
                    </div>

                    @if ($choix !== null)
                        <button type="button" wire:click="effacer" class="self-start text-sm font-semibold text-cendre underline underline-offset-4 hover:text-foret">Effacer ma réponse</button>
                    @endif
                </main>

                {{-- Navigation --}}
                <div class="sticky bottom-0 border-t-[1.5px] border-trait bg-white/95 backdrop-blur">
                    <div class="mx-auto flex max-w-2xl items-center gap-2 px-4 pt-3 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                        <button type="button" wire:click="precedente" @disabled($index === 0) aria-label="Question précédente"
                            class="flex h-12 items-center gap-1 rounded-2xl border-[1.5px] border-trait bg-white px-3 font-bold text-foret hover:border-cendre disabled:opacity-40 sm:px-4">
                            <x-icone nom="gauche" class="size-5" /><span class="hidden sm:inline">Précédente</span>
                        </button>
                        <button type="button" wire:click="basculerMarque" aria-pressed="{{ $marquee ? 'true' : 'false' }}" @class([
                            'flex h-12 flex-1 items-center justify-center gap-2 rounded-2xl px-3 text-sm font-bold',
                            'bg-peche text-foret' => $marquee,
                            'border-[1.5px] border-trait bg-white text-foret hover:border-cendre' => ! $marquee,
                        ])>
                            <span @class(['size-2.5 rounded-full', 'bg-foret' => $marquee, 'bg-peche' => ! $marquee])></span>
                            <span class="sm:hidden">À revoir</span>
                            <span class="hidden sm:inline">{{ $marquee ? 'Marquée à revoir' : 'Marquer à revoir' }}</span>
                        </button>
                        @if ($index + 1 < $total)
                            <button type="button" wire:click="suivante" class="flex h-12 items-center gap-1 rounded-2xl bg-foret px-4 font-bold text-white hover:bg-foret/90">
                                <span>Suivante</span><x-icone nom="droite" class="size-5" />
                            </button>
                        @else
                            <button type="button" @click="confirmer = true" class="flex h-12 items-center rounded-2xl bg-peche px-4 font-bold text-foret hover:bg-foret hover:text-white">Terminer</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Fin du test : terminer pour voir ses résultats, ou abandonner sans rien garder --}}
        <div x-show="confirmer" x-cloak x-transition.opacity class="fixed inset-0 z-[60] flex items-end justify-center bg-foret/50 p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="titre-confirmer">
            <div @click.outside="confirmer = false" x-data="{ abandon: false }" x-effect="if (! confirmer) abandon = false"
                class="flex w-full max-w-md flex-col gap-5 rounded-[24px] bg-white p-6">
                <div class="flex flex-col gap-2">
                    <h2 id="titre-confirmer" class="font-titre text-xl">Terminer le test&nbsp;?</h2>
                    <p class="text-[15px] text-cendre">
                        @if ($total - count($reponses) > 0)
                            Il te reste {{ $total - count($reponses) }} {{ $total - count($reponses) > 1 ? 'questions' : 'question' }} sans réponse.
                        @else
                            Tu as répondu à toutes les questions.
                        @endif
                        Tu ne pourras plus modifier tes réponses.
                    </p>
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="confirmer = false" class="h-12 flex-1 rounded-2xl border-[1.5px] border-trait font-bold hover:border-cendre">Continuer</button>
                    <button type="button" wire:click="terminer" @click="confirmer = false" class="h-12 flex-1 rounded-2xl bg-foret font-bold text-white hover:bg-foret/90">Terminer</button>
                </div>

                <div class="flex items-center justify-between gap-3 border-t-[1.5px] border-trait pt-4">
                    <p class="text-[13px] leading-snug text-cendre" x-text="abandon ? 'Sûr ? Tes réponses seront perdues.' : 'Tu veux arrêter là, sans résultat ?'">Tu veux arrêter là, sans résultat&nbsp;?</p>
                    <button type="button" x-show="! abandon" @click="abandon = true" class="h-10 shrink-0 rounded-xl border-[1.5px] border-peche px-4 text-sm font-bold text-foret hover:bg-peche-clair">Abandonner</button>
                    <button type="button" x-show="abandon" x-cloak wire:click="abandonner" class="h-10 shrink-0 rounded-xl bg-peche px-4 text-sm font-bold text-foret hover:bg-foret hover:text-white">Oui, abandonner</button>
                </div>
            </div>
        </div>
    @endif
</div>
