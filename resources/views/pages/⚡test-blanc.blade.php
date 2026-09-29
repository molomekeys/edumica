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
    /** Durée par défaut du test blanc si l'épreuve n'en définit pas, en minutes. */
    public const DUREE_PAR_DEFAUT = 30;

    #[Locked]
    public Epreuve $epreuve;

    /** @var list<int> questions, regroupées par catégorie */
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

    public function mount(Epreuve $epreuve): void
    {
        $this->epreuve = $epreuve;

        // Les catégories apparaissent dans l'ordre de leur première question.
        $this->ids = $epreuve->questions()->get()
            ->groupBy('categorie')
            ->flatMap(fn (Collection $questions) => $questions->pluck('id'))
            ->all();

        $this->fin = now()->addMinutes($epreuve->duree_test ?? self::DUREE_PAR_DEFAUT)->getTimestamp();
    }

    public function render()
    {
        return $this->view()->title('Test blanc · '.$this->epreuve->nom);
    }

    /** @return Collection<int, Question> */
    #[Computed]
    public function questions(): Collection
    {
        $questions = Question::findMany($this->ids)->keyBy('id');

        return collect($this->ids)->map(fn (int $id) => $questions[$id])->values();
    }

    /** @return Collection<string, Collection<int, Question>> catégorie => [index => question] */
    #[Computed]
    public function categories(): Collection
    {
        return $this->questions->groupBy('categorie', preserveKeys: true);
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

    /** @return array{bonnes: int, total: int, score: int, niveau: ?string, categories: array<string, array{bonnes: int, total: int}>} */
    #[Computed]
    public function resultat(): array
    {
        $categories = [];
        $bonnes = 0;

        foreach ($this->questions as $i => $question) {
            $correcte = $question->estCorrecte($this->reponses[$i] ?? null);
            $bonnes += (int) $correcte;
            $categories[$question->categorie]['total'] = ($categories[$question->categorie]['total'] ?? 0) + 1;
            $categories[$question->categorie]['bonnes'] = ($categories[$question->categorie]['bonnes'] ?? 0) + (int) $correcte;
        }

        $total = count($this->ids);
        $note = isset(Nclc::EPREUVES[$this->epreuve->code]);
        $score = $note && $total ? (int) round($bonnes / $total * Nclc::maximum($this->epreuve->code)) : 0;

        return [
            'bonnes' => $bonnes,
            'total' => $total,
            'score' => $score,
            'niveau' => $note ? Nclc::niveauPour($this->epreuve->code, $score) : null,
            'categories' => $categories,
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

<div class="flex min-h-dvh flex-col bg-brume" x-data="{ panneau: false, confirmer: false }" @keydown.escape.window="panneau = false; confirmer = false">
    @php($lettres = ['A', 'B', 'C', 'D', 'E', 'F'])
    @php($total = count($ids))

    {{-- En-tête --}}
    <header class="sticky top-0 z-20 border-b-[1.5px] border-ligne bg-white">
        <div class="flex h-16 items-center justify-between gap-3 px-3 md:px-6">
            <div class="flex items-center gap-2">
                <a href="{{ route('espace') }}" wire:navigate aria-label="Quitter le test blanc" class="flex size-11 items-center justify-center rounded-xl text-foret hover:bg-brume">
                    <x-icone nom="croix" class="size-6" />
                </a>
                <div class="flex flex-col">
                    <span class="text-[12px] font-bold tracking-wide text-mousse uppercase">Test blanc</span>
                    <span class="text-[15px] leading-tight font-bold">{{ $epreuve->nom }}</span>
                </div>
            </div>

            @if (! $termine && $total)
                <div class="flex items-center gap-2">
                    <span class="hidden text-sm font-semibold text-mousse sm:inline">{{ count($reponses) }} / {{ $total }} répondues</span>
                    <div x-data="chrono({{ $fin }}, () => $wire.terminer())" role="timer" aria-label="Temps restant"
                        class="flex items-center gap-1.5 rounded-full px-3.5 py-2 text-[15px] font-bold tabular-nums"
                        :class="reste <= 300 ? 'bg-peche text-foret' : 'bg-brume'">
                        <x-icone nom="horloge" class="size-[18px]" />
                        <span x-text="affichage">{{ gmdate('i:s', max(0, $fin - now()->getTimestamp())) }}</span>
                    </div>
                    <button type="button" @click="confirmer = true" class="hidden h-10 rounded-xl bg-foret px-4 text-sm font-bold text-white hover:bg-vert sm:block">Terminer</button>
                </div>
            @endif
        </div>
    </header>

    @if (! $total)
        <main class="flex flex-1 flex-col items-center justify-center gap-4 p-5 text-center">
            <h1 class="font-titre text-[22px] leading-tight">Pas encore de test blanc pour cette épreuve</h1>
            <a href="{{ route('espace') }}" wire:navigate class="flex h-12 items-center rounded-2xl bg-foret px-6 font-bold text-white hover:text-white">Retour à mon espace</a>
        </main>
    @elseif ($termine)
        {{-- Résultats --}}
        @php($resultat = $this->resultat)

        <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 px-4 py-8">
            <section class="relative isolate flex flex-col gap-3 overflow-hidden rounded-[28px] bg-menthe px-6 py-7">
                <x-arcs class="-top-[120px] -right-[120px] size-[300px]" />
                <div class="text-[13px] font-bold text-mousse-fonce">Test blanc terminé · {{ $epreuve->nom }}</div>
                <div class="font-titre text-[56px] leading-none tracking-[-1.5px]">{{ $resultat['bonnes'] }}<span class="text-[28px] text-mousse-fonce"> / {{ $resultat['total'] }}</span></div>
                @if ($resultat['score'])
                    <p class="text-lg font-bold">
                        Score estimé : {{ $resultat['score'] }} / {{ \App\Support\Nclc::maximum($epreuve->code) }}
                        · {{ $resultat['niveau'] ? 'NCLC '.$resultat['niveau'] : 'sous le NCLC 5' }}
                    </p>
                @endif
                <p class="text-sm text-mousse-fonce">Estimation indicative calculée sur des questions d'entraînement.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="font-titre text-[22px] tracking-[-0.5px]">Par catégorie</h2>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($resultat['categories'] as $categorie => $stats)
                        <div wire:key="stat-{{ $loop->index }}" class="flex flex-col gap-2 rounded-2xl border-[1.5px] border-ligne bg-white p-4">
                            <div class="flex justify-between gap-2 text-[15px] font-bold"><span>{{ $categorie }}</span><span class="tabular-nums">{{ $stats['bonnes'] }} / {{ $stats['total'] }}</span></div>
                            <div class="h-2 rounded-full bg-jauge-vide"><div class="h-2 rounded-full bg-jauge" style="width: {{ $stats['bonnes'] / $stats['total'] * 100 }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="flex flex-col gap-3">
                <h2 class="font-titre text-[22px] tracking-[-0.5px]">Correction</h2>
                <ol class="flex flex-col gap-2">
                    @foreach ($this->questions as $i => $q)
                        @php($choix = $reponses[$i] ?? null)
                        <li wire:key="correction-{{ $q->id }}" class="flex items-start gap-3 rounded-2xl border-[1.5px] border-ligne bg-white px-4 py-3">
                            @if ($q->estCorrecte($choix))
                                <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-vert text-white"><x-icone nom="coche" :epaisseur="3" class="size-4" /></span>
                            @elseif ($choix !== null)
                                <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-peche text-foret"><x-icone nom="croix" :epaisseur="3" class="size-4" /></span>
                            @else
                                <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-brume text-[13px] font-bold text-mousse">–</span>
                            @endif
                            <div class="flex flex-1 flex-col gap-0.5">
                                <span class="text-[12px] font-bold text-mousse">Q{{ $i + 1 }} · {{ $q->categorie }}</span>
                                <span class="text-[15px] font-semibold">{{ $q->enonce }}</span>
                                <span class="text-[13px] text-mousse">
                                    @if ($choix === null) Sans réponse · @elseif (! $q->estCorrecte($choix)) Ta réponse : {{ $q->choix[$choix] ?? '?' }} · @endif
                                    Bonne réponse : {{ $q->choix[$q->bonne_reponse] }}
                                </span>
                                <span class="text-[13px] leading-normal text-mousse-fonce">{{ $q->explication }}</span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <div class="flex flex-col gap-2.5 sm:flex-row">
                <a href="{{ route('test-blanc', $epreuve) }}" wire:navigate class="flex h-14 flex-1 items-center justify-center gap-2 rounded-2xl bg-foret text-[17px] font-bold text-white hover:bg-vert hover:text-white">
                    <x-icone nom="relancer" class="size-5" />Recommencer
                </a>
                <a href="{{ route('espace') }}" wire:navigate class="flex h-14 flex-1 items-center justify-center rounded-2xl border-[1.5px] border-foret bg-white text-base font-bold text-foret">Retour à mon espace</a>
            </div>
        </main>
    @else
        @php($question = $this->question)
        @php($choix = $reponses[$index] ?? null)
        @php($marquee = in_array($index, $marquees, true))

        <div class="flex flex-1">
            {{-- Questions par catégorie (tiroir sur mobile) --}}
            <div x-show="panneau" x-cloak x-transition.opacity @click="panneau = false" class="fixed inset-0 z-30 bg-foret/30 lg:hidden"></div>
            <aside id="panneau-questions" aria-label="Questions par catégorie"
                :class="panneau && 'max-lg:translate-x-0!'"
                class="fixed inset-y-0 left-0 z-40 flex w-[290px] flex-col border-r-[1.5px] border-ligne bg-white transition-transform max-lg:-translate-x-full lg:sticky lg:top-16 lg:z-0 lg:h-[calc(100dvh-4rem)]">
                <div class="flex items-center justify-between px-4 pt-4 pb-2">
                    <span class="text-[15px] font-extrabold">Questions</span>
                    <button type="button" @click="panneau = false" class="flex size-9 items-center justify-center rounded-lg hover:bg-brume lg:hidden" aria-label="Fermer la liste">
                        <x-icone nom="croix" class="size-5" />
                    </button>
                </div>

                <div class="flex flex-1 flex-col gap-4 overflow-y-auto px-4 pb-4">
                    @foreach ($this->categories as $categorie => $questionsCategorie)
                        @php($repondues = collect($questionsCategorie->keys())->filter(fn ($i) => isset($reponses[$i]))->count())
                        <div wire:key="categorie-{{ $loop->index }}" class="flex flex-col gap-2">
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-[13px] font-bold tracking-wide text-mousse-fonce uppercase">{{ $categorie }}</span>
                                <span class="text-[12px] font-semibold text-mousse tabular-nums">{{ $repondues }}/{{ $questionsCategorie->count() }}</span>
                            </div>
                            <div class="grid grid-cols-5 gap-1.5">
                                @foreach ($questionsCategorie as $i => $q)
                                    <button type="button" wire:key="nav-{{ $q->id }}" wire:click="aller({{ $i }})" @click="panneau = false"
                                        aria-label="Question {{ $i + 1 }}{{ isset($reponses[$i]) ? ', répondue' : '' }}{{ in_array($i, $marquees, true) ? ', à revoir' : '' }}"
                                        @if ($i === $index) aria-current="step" @endif
                                        @class([
                                            'relative flex h-10 items-center justify-center rounded-lg text-sm font-bold tabular-nums transition-colors',
                                            'bg-foret text-white' => isset($reponses[$i]),
                                            'border-[1.5px] border-ligne bg-white text-foret hover:border-mousse' => ! isset($reponses[$i]),
                                            'ring-2 ring-vert ring-offset-2' => $i === $index,
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
                </div>

                <div class="flex flex-col gap-1.5 border-t-[1.5px] border-ligne px-4 py-3 text-[12px] text-mousse">
                    <div class="flex items-center gap-2"><span class="size-3 rounded bg-foret"></span>Répondue</div>
                    <div class="flex items-center gap-2"><span class="size-3 rounded border-[1.5px] border-ligne"></span>Sans réponse</div>
                    <div class="flex items-center gap-2"><span class="size-3 rounded-full bg-peche"></span>À revoir</div>
                    <button type="button" @click="panneau = false; confirmer = true" class="mt-2 h-11 rounded-xl bg-foret text-sm font-bold text-white sm:hidden">Terminer le test</button>
                </div>
            </aside>

            {{-- Question en cours --}}
            <div class="flex min-w-0 flex-1 flex-col">
                <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 px-4 py-6" wire:key="question-{{ $question->id }}">
                    <div class="flex items-center justify-between gap-2">
                        <button type="button" @click="panneau = true" aria-controls="panneau-questions" class="flex h-9 items-center gap-1.5 rounded-lg border-[1.5px] border-ligne bg-white px-3 text-sm font-bold lg:hidden">
                            <x-icone nom="menu" class="size-4" />Questions
                        </button>
                        <span class="text-sm font-bold text-mousse">Question {{ $index + 1 }} sur {{ $total }} · {{ $question->categorie }}</span>
                    </div>

                    @if ($question->transcription || $question->audio)
                        <x-lecteur :question="$question" :ecoutee="in_array($index, $ecoutees, true)" :sur-debut="'() => $wire.marquerEcoutee('.$index.')'" />
                    @endif

                    @if ($question->support)
                        <div class="rounded-[20px] border-[1.5px] border-ligne bg-white p-4 text-[15px] leading-[1.6] whitespace-pre-line">{{ $question->support }}</div>
                    @endif

                    <h1 id="enonce" class="font-titre text-[22px] leading-[1.2] tracking-[-0.5px]">{{ $question->enonce }}</h1>

                    <div role="radiogroup" aria-labelledby="enonce" class="flex flex-col gap-2.5">
                        @foreach ($question->choix as $i => $libelle)
                            <button type="button" role="radio" aria-checked="{{ $choix === $i ? 'true' : 'false' }}" wire:click="choisir({{ $i }})" @class([
                                'flex min-h-14 items-center gap-3 rounded-2xl px-4 text-left text-base text-foret transition-colors',
                                'border-2 border-foret bg-menthe font-bold' => $choix === $i,
                                'border-[1.5px] border-ligne bg-white hover:border-mousse' => $choix !== $i,
                            ])>
                                <span @class([
                                    'flex size-7 shrink-0 items-center justify-center rounded-lg text-sm font-bold',
                                    'bg-foret text-white' => $choix === $i,
                                    'bg-brume' => $choix !== $i,
                                ])>{{ $lettres[$i] }}</span>
                                <span class="py-3">{{ $libelle }}</span>
                            </button>
                        @endforeach
                    </div>

                    @if ($choix !== null)
                        <button type="button" wire:click="effacer" class="self-start text-sm font-semibold text-mousse underline underline-offset-4 hover:text-foret">Effacer ma réponse</button>
                    @endif
                </main>

                {{-- Navigation --}}
                <div class="sticky bottom-0 border-t-[1.5px] border-ligne bg-white">
                    <div class="mx-auto flex max-w-2xl items-center gap-2 px-4 pt-3 pb-5">
                        <button type="button" wire:click="precedente" @disabled($index === 0) aria-label="Question précédente"
                            class="flex h-12 items-center gap-1 rounded-2xl border-[1.5px] border-ligne bg-white px-3 font-bold text-foret hover:border-mousse disabled:opacity-40 sm:px-4">
                            <x-icone nom="gauche" class="size-5" /><span class="hidden sm:inline">Précédente</span>
                        </button>
                        <button type="button" wire:click="basculerMarque" aria-pressed="{{ $marquee ? 'true' : 'false' }}" @class([
                            'flex h-12 flex-1 items-center justify-center gap-2 rounded-2xl px-3 text-sm font-bold',
                            'bg-peche text-foret' => $marquee,
                            'border-[1.5px] border-ligne bg-white text-foret hover:border-mousse' => ! $marquee,
                        ])>
                            <span @class(['size-2.5 rounded-full', 'bg-foret' => $marquee, 'bg-peche' => ! $marquee])></span>
                            <span class="sm:hidden">À revoir</span>
                            <span class="hidden sm:inline">{{ $marquee ? 'Marquée à revoir' : 'Marquer à revoir' }}</span>
                        </button>
                        @if ($index + 1 < $total)
                            <button type="button" wire:click="suivante" class="flex h-12 items-center gap-1 rounded-2xl bg-foret px-4 font-bold text-white hover:bg-vert">
                                <span>Suivante</span><x-icone nom="droite" class="size-5" />
                            </button>
                        @else
                            <button type="button" @click="confirmer = true" class="flex h-12 items-center rounded-2xl bg-foret px-4 font-bold text-white hover:bg-vert">Terminer</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Confirmation de fin --}}
        <div x-show="confirmer" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-foret/40 p-4" role="dialog" aria-modal="true" aria-labelledby="titre-confirmer">
            <div @click.outside="confirmer = false" class="flex w-full max-w-sm flex-col gap-4 rounded-[24px] bg-white p-6">
                <h2 id="titre-confirmer" class="font-titre text-xl">Terminer le test&nbsp;?</h2>
                <p class="text-[15px] text-mousse">
                    @if ($total - count($reponses) > 0)
                        Il te reste {{ $total - count($reponses) }} {{ $total - count($reponses) > 1 ? 'questions' : 'question' }} sans réponse.
                    @else
                        Tu as répondu à toutes les questions.
                    @endif
                    Tu ne pourras plus modifier tes réponses.
                </p>
                <div class="flex gap-2">
                    <button type="button" @click="confirmer = false" class="h-12 flex-1 rounded-2xl border-[1.5px] border-ligne font-bold">Continuer</button>
                    <button type="button" wire:click="terminer" @click="confirmer = false" class="h-12 flex-1 rounded-2xl bg-foret font-bold text-white hover:bg-vert">Terminer</button>
                </div>
            </div>
        </div>
    @endif
</div>
