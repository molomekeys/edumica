<?php

use App\Models\Epreuve;
use App\Models\Question;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    /** Nombre de questions par quiz. */
    public const TAILLE = 10;

    /** Temps accordé par question, en secondes. */
    public const SECONDES_PAR_QUESTION = 60;

    #[Locked]
    public Epreuve $epreuve;

    /** @var list<int> */
    #[Locked]
    public array $ids = [];

    #[Locked]
    public int $index = 0;

    #[Locked]
    public ?int $choix = null;

    /**
     * Index de question => choix (null : question passée). La correction
     * n'est calculée qu'à la fin, pour ne rien dévoiler pendant le quiz.
     *
     * @var array<int, ?int>
     */
    #[Locked]
    public array $reponses = [];

    /** Horodatage Unix de fin du chrono. */
    #[Locked]
    public int $fin = 0;

    #[Locked]
    public bool $termine = false;

    public function mount(Epreuve $epreuve): void
    {
        $this->epreuve = $epreuve;
        $this->ids = $epreuve->questions()->inRandomOrder()->limit(self::TAILLE)->pluck('id')->all();
        $this->fin = now()->addSeconds(count($this->ids) * self::SECONDES_PAR_QUESTION)->getTimestamp();
    }

    public function render()
    {
        return $this->view()->title(__('Quiz · :epreuve', ['epreuve' => __($this->epreuve->nom)]));
    }

    #[Computed]
    public function question(): ?Question
    {
        return isset($this->ids[$this->index]) ? Question::find($this->ids[$this->index]) : null;
    }

    /** @return Collection<int, Question> */
    #[Computed]
    public function questions(): Collection
    {
        return Question::findMany($this->ids)->sortBy(fn (Question $q) => array_search($q->id, $this->ids))->values();
    }

    #[Computed]
    public function total(): int
    {
        return count($this->ids);
    }

    /**
     * Correction de chaque question : 'bonne', 'fausse', 'passee' ou 'vide' (temps écoulé).
     *
     * @return list<string>
     */
    #[Computed]
    public function corrections(): array
    {
        return $this->questions->map(function (Question $q, int $i) {
            if (! array_key_exists($i, $this->reponses)) {
                return 'vide';
            }

            if ($this->reponses[$i] === null) {
                return 'passee';
            }

            return $q->estCorrecte($this->reponses[$i]) ? 'bonne' : 'fausse';
        })->all();
    }

    #[Computed]
    public function bonnes(): int
    {
        return count(array_keys($this->corrections, 'bonne', true));
    }

    public function choisir(int $choix): void
    {
        if ($this->termine || ! isset($this->question->choix[$choix])) {
            return;
        }

        $this->choix = $choix;
    }

    public function suivante(): void
    {
        if ($this->choix === null || $this->termine) {
            return;
        }

        $this->enregistrer($this->choix);
    }

    public function passer(): void
    {
        if ($this->termine) {
            return;
        }

        $this->enregistrer(null);
    }

    public function terminer(): void
    {
        if ($this->termine) {
            return;
        }

        // Le chrono s'arrête : on garde la réponse sélectionnée sur la question en cours.
        if ($this->choix !== null && ! array_key_exists($this->index, $this->reponses)) {
            $this->reponses[$this->index] = $this->choix;
        }

        $this->termine = true;
    }

    private function enregistrer(?int $choix): void
    {
        if ($this->tempsEcoule()) {
            $this->terminer();

            return;
        }

        $this->reponses[$this->index] = $choix;

        if ($this->index + 1 >= $this->total) {
            $this->termine = true;

            return;
        }

        $this->index++;
        $this->choix = null;
        unset($this->question);
    }

    private function tempsEcoule(): bool
    {
        // Petite marge pour la latence réseau.
        return now()->getTimestamp() > $this->fin + 5;
    }
};
?>

<div class="flex min-h-dvh flex-col" x-data="chrono({{ $fin }}, () => $wire.terminer())">
    @php($lettres = ['A', 'B', 'C', 'D', 'E', 'F'])
    @php($enCours = ! $termine && $this->total > 0)

    {{-- En-tête --}}
    <header class="sticky top-0 z-20 border-b-[1.5px] border-ligne bg-white/95 pt-[env(safe-area-inset-top)] backdrop-blur">
        <div class="mx-auto flex max-w-5xl flex-col gap-1.5 px-2 pb-2.5 md:gap-2 md:px-6 md:pb-3">
            <div class="flex h-12 items-center gap-2 md:h-14">
                {{-- En plein quiz, on demande confirmation avant de perdre ses réponses. --}}
                <a href="{{ route('accueil') }}" aria-label="{{ __('Quitter le quiz') }}"
                    @if ($enCours) onclick="return confirm({{ \Illuminate\Support\Js::from(__('Quitter le quiz ? Tes réponses ne seront pas gardées.')) }})" @else wire:navigate @endif
                    class="flex size-11 shrink-0 items-center justify-center rounded-full text-foret hover:bg-brume">
                    <x-icone nom="croix" class="size-6" />
                </a>
                <div class="min-w-0 flex-1 truncate text-[15px] font-bold md:text-base">{{ __($epreuve->nom) }}</div>

                @if ($enCours)
                    <div class="flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-bold tabular-nums transition-colors"
                        :class="reste <= 60 ? 'bg-peche-clair' : 'bg-brume'" role="timer" aria-label="{{ __('Temps restant') }}" dir="ltr">
                        <x-icone nom="horloge" class="size-4" />
                        <span x-text="affichage">{{ gmdate('i:s', max(0, $fin - now()->getTimestamp())) }}</span>
                    </div>
                @endif
            </div>

            @if ($enCours)
                {{-- Progression : un segment par question, sans dévoiler la correction --}}
                <div class="flex items-center gap-3 px-2">
                    <ol class="flex flex-1 gap-1" role="progressbar" aria-label="{{ __('Progression') }}" aria-valuemin="1" aria-valuemax="{{ $this->total }}" aria-valuenow="{{ $index + 1 }}">
                        @for ($i = 0; $i < $this->total; $i++)
                            <li @class([
                                'h-1.5 flex-1 rounded-full transition-colors duration-300 md:h-2',
                                'bg-foret' => $i === $index,
                                'bg-vert' => $i < $index && ($reponses[$i] ?? null) !== null,
                                'bg-mousse/30' => $i < $index && ($reponses[$i] ?? null) === null,
                                'bg-brume' => $i > $index,
                            ])></li>
                        @endfor
                    </ol>
                    <div class="text-[13px] font-bold text-mousse tabular-nums" dir="ltr">{{ $index + 1 }}<span class="font-semibold"> / {{ $this->total }}</span></div>
                </div>
            @endif
        </div>
    </header>

    @if ($this->total === 0)
        {{-- Aucun quiz pour cette épreuve --}}
        <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col items-center justify-center gap-4 p-5 text-center">
            <div class="flex size-16 items-center justify-center rounded-full bg-vert text-white"><x-icone :nom="$epreuve->icone" class="size-7" /></div>
            <h1 class="font-titre text-[22px] leading-tight tracking-[-0.5px] md:text-[28px]">{{ __('Pas encore de quiz pour cette épreuve') }}</h1>
            <p class="max-w-sm text-base leading-normal text-mousse">{{ __("Les quiz d':epreuve ne sont pas encore disponibles. Entraîne-toi sur une autre épreuve en attendant.", ['epreuve' => mb_strtolower(__($epreuve->nom))]) }}</p>
            <a href="{{ route('epreuves') }}" wire:navigate class="mt-2 flex h-14 w-full items-center justify-center rounded-2xl bg-foret px-8 text-[17px] font-bold text-white hover:bg-vert hover:text-white sm:w-auto">{{ __('Choisir une autre épreuve') }}</a>
        </main>
    @elseif ($termine)
        {{-- Résultat et correction complète --}}
        @php($corrections = $this->corrections)
        @php($fausses = count(array_keys($corrections, 'fausse', true)))
        @php($passees = count(array_keys($corrections, 'passee', true)) + count(array_keys($corrections, 'vide', true)))
        @php($taux = $this->bonnes / $this->total)

        <main class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 px-3 pt-4 pb-[calc(2.5rem+env(safe-area-inset-bottom))] md:px-6 md:pt-8 lg:grid lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:items-start lg:gap-10"
            x-data="{ filtre: 'toutes' }">
            <div class="flex flex-col gap-4 lg:sticky lg:top-28">
                <section class="relative isolate flex flex-col gap-3 overflow-hidden rounded-[28px] bg-menthe px-5 py-6 md:px-7 md:py-8">
                    <x-arcs class="-top-[120px] -right-[120px] size-[300px]" />
                    <div class="text-[13px] font-bold text-mousse-fonce">{{ __('Quiz terminé · :epreuve', ['epreuve' => __($epreuve->nom)]) }}</div>
                    <div class="font-titre text-[56px] leading-none tracking-[-1.5px] md:text-[72px]" dir="ltr">{{ $this->bonnes }}<span class="text-[28px] text-mousse-fonce md:text-[34px]"> / {{ $this->total }}</span></div>
                    <h1 class="font-titre text-[26px] leading-[1.1] tracking-[-0.5px]">
                        @if ($taux >= 0.8) {{ __('Très bon travail.') }}
                        @elseif ($taux >= 0.5) {{ __("C'est un bon début.") }}
                        @else {{ __("Continue à t'entraîner.") }}
                        @endif
                    </h1>
                    <p class="text-base leading-normal text-mousse-fonce">
                        {{ trans_choice(':count bonne réponse sur :total.|:count bonnes réponses sur :total.', $this->bonnes, ['total' => $this->total]) }}
                        @if (count($reponses) < $this->total) {{ __('Le temps est écoulé avant la fin.') }} @endif
                    </p>

                    <dl class="mt-1 grid grid-cols-3 gap-2 text-center">
                        @foreach ([[__('Réussies'), $this->bonnes, 'text-vert'], [__('À revoir'), $fausses, 'text-foret'], [__('Sans réponse'), $passees, 'text-mousse']] as [$libelle, $nombre, $couleur])
                            <div class="flex flex-col-reverse rounded-2xl bg-white/70 px-2 py-2.5">
                                <dt class="text-[12px] font-semibold text-mousse-fonce md:text-[13px]">{{ $libelle }}</dt>
                                <dd class="font-titre text-[22px] leading-tight {{ $couleur }}">{{ $nombre }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <section class="flex flex-col gap-2.5 px-2 lg:px-0">
                    <a href="{{ route('quiz', $epreuve) }}" wire:navigate class="flex h-14 items-center justify-center gap-2 rounded-2xl bg-foret text-[17px] font-bold text-white hover:bg-vert hover:text-white">
                        <x-icone nom="relancer" class="size-5" />{{ __('Refaire un quiz') }}
                    </a>
                    <a href="{{ route('epreuves') }}" wire:navigate class="flex h-[52px] items-center justify-center rounded-2xl border-[1.5px] border-foret text-base font-bold text-foret hover:bg-foret/5">{{ __('Choisir une autre épreuve') }}</a>
                </section>
            </div>

            <section class="flex flex-col gap-3 px-1 md:px-0" aria-labelledby="titre-correction">
                <div class="flex flex-col gap-3 px-1 sm:flex-row sm:items-end sm:justify-between">
                    <h2 id="titre-correction" class="font-titre text-[22px] tracking-[-0.5px] md:text-[26px]">{{ __('La correction') }}</h2>

                    {{-- Filtres --}}
                    <div class="flex gap-1 rounded-full bg-brume p-1 text-sm font-bold" role="group" aria-label="{{ __('Filtrer la correction') }}">
                        @foreach (['toutes' => __('Toutes'), 'revoir' => __('À revoir'), 'reussies' => __('Réussies')] as $cle => $libelle)
                            <button type="button" @click="filtre = '{{ $cle }}'" :aria-pressed="filtre === '{{ $cle }}'"
                                class="min-h-9 flex-1 rounded-full px-3.5 transition-colors sm:flex-none"
                                :class="filtre === '{{ $cle }}' ? 'bg-foret text-white' : 'text-foret hover:bg-white'">{{ $libelle }}</button>
                        @endforeach
                    </div>
                </div>

                <ol class="flex flex-col gap-2.5">
                    @foreach ($this->questions as $i => $q)
                        @php($etat = $corrections[$i])
                        @php($choisi = $reponses[$i] ?? null)
                        <li wire:key="correction-{{ $q->id }}"
                            x-show="filtre === 'toutes' || (filtre === 'reussies') === {{ $etat === 'bonne' ? 'true' : 'false' }}"
                            x-data="{ ouvert: {{ $etat === 'bonne' ? 'false' : 'true' }} }"
                            @class([
                                'overflow-hidden rounded-[20px] border-[1.5px]',
                                'border-ligne' => $etat === 'bonne',
                                'border-peche' => $etat === 'fausse',
                                'border-ligne bg-brume/40' => in_array($etat, ['passee', 'vide']),
                            ])>
                            <h3>
                                <button type="button" @click="ouvert = !ouvert" :aria-expanded="ouvert" aria-controls="correction-{{ $i }}"
                                    class="flex w-full items-start gap-3 px-3.5 py-3.5 text-start md:px-4">
                                    @if ($etat === 'bonne')
                                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-vert text-white"><x-icone nom="coche" :epaisseur="3" class="size-4" /></span>
                                    @elseif ($etat === 'fausse')
                                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-peche text-foret"><x-icone nom="croix" :epaisseur="3" class="size-4" /></span>
                                    @else
                                        <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brume text-mousse"><x-icone nom="droite" :epaisseur="2.6" class="size-4" /></span>
                                    @endif
                                    <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                                        <span class="text-[12px] font-bold tracking-wide text-mousse uppercase">
                                            {{ __('Question :numero', ['numero' => $i + 1]) }} ·
                                            @switch($etat)
                                                @case('bonne') {{ __('Réussie') }} @break
                                                @case('fausse') {{ __('À revoir') }} @break
                                                @case('passee') {{ __('Passée') }} @break
                                                @default {{ __('Pas répondu') }}
                                            @endswitch
                                        </span>
                                        <span lang="fr" dir="ltr" class="text-start text-[15px] leading-snug font-semibold md:text-base">{{ $q->enonce }}</span>
                                    </span>
                                    <x-icone nom="bas" class="mt-1 size-5 shrink-0 text-mousse transition-transform" x-bind:class="ouvert && 'rotate-180'" />
                                </button>
                            </h3>

                            <div id="correction-{{ $i }}" x-show="ouvert" x-collapse @if ($etat === 'bonne') x-cloak @endif class="border-t-[1.5px] border-ligne">
                                <div class="flex flex-col gap-3 px-3.5 pt-3.5 pb-4 md:px-4">
                                    <ul lang="fr" dir="ltr" class="flex flex-col gap-2">
                                        @foreach ($q->choix as $c => $libelle)
                                            @php($estBonne = $c === $q->bonne_reponse)
                                            @php($estChoisie = $c === $choisi)
                                            <li @class([
                                                'flex items-center gap-3 rounded-[14px] px-3',
                                                'min-h-12 border-2 border-vert bg-brume text-[15px] font-bold' => $estBonne,
                                                'min-h-12 border-2 border-peche bg-peche-clair text-[15px] font-bold' => $estChoisie && ! $estBonne,
                                                'min-h-11 border-[1.5px] border-ligne text-[15px] text-mousse' => ! $estBonne && ! $estChoisie,
                                            ])>
                                                @if ($estBonne)
                                                    <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-vert text-white"><x-icone nom="coche" :epaisseur="3" class="size-3.5" /></span>
                                                @elseif ($estChoisie)
                                                    <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-peche text-foret"><x-icone nom="croix" :epaisseur="3" class="size-3.5" /></span>
                                                @else
                                                    <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-brume text-[12px] font-bold text-foret">{{ $lettres[$c] }}</span>
                                                @endif
                                                <span class="min-w-0 flex-1 py-2">{{ $libelle }}</span>
                                                @if ($estChoisie)
                                                    <span @class(['shrink-0 text-[12px]', 'text-vert' => $estBonne, 'text-foret' => ! $estBonne])>{{ __('Ta réponse') }}</span>
                                                @elseif ($estBonne)
                                                    <span class="shrink-0 text-[12px] text-vert">{{ __('Bonne réponse') }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="flex flex-col gap-1.5 rounded-2xl bg-brume p-3.5">
                                        @if ($etat === 'bonne')
                                            <div lang="fr" dir="ltr" class="text-[15px] font-extrabold">{{ $q->feedback }}</div>
                                        @else
                                            <div class="text-[15px] font-extrabold">{{ __('Pourquoi ?') }}</div>
                                        @endif
                                        <p lang="fr" dir="ltr" class="text-[15px] leading-[1.55] text-mousse-fonce">{{ $q->explication }}</p>
                                    </div>

                                    @if ($q->transcription || $q->support)
                                        <div x-data="{ document: false }">
                                            <button type="button" @click="document = !document" :aria-expanded="document" aria-controls="document-{{ $i }}"
                                                class="flex min-h-11 items-center gap-1.5 text-[15px] font-bold text-vert">
                                                <span x-text="document ? @js($q->transcription ? __('Masquer la transcription') : __('Masquer le document')) : @js($q->transcription ? __('Lire la transcription') : __('Relire le document'))">{{ $q->transcription ? __('Lire la transcription') : __('Relire le document') }}</span>
                                                <x-icone nom="bas" class="size-[18px] transition-transform" x-bind:class="document && 'rotate-180'" />
                                            </button>
                                            <p id="document-{{ $i }}" lang="fr" dir="ltr" x-show="document" x-collapse x-cloak class="rounded-xl border-[1.5px] border-ligne p-3.5 text-[15px] leading-[1.55] whitespace-pre-line">{{ $q->transcription ?: $q->support }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>

                <p x-show="filtre === 'revoir' && {{ $fausses + $passees }} === 0" x-cloak class="rounded-2xl bg-menthe p-4 text-center text-[15px] font-semibold">{{ __('Rien à revoir, tout est juste.') }}</p>
                <p x-show="filtre === 'reussies' && {{ $this->bonnes }} === 0" x-cloak class="rounded-2xl bg-brume p-4 text-center text-[15px] font-semibold">{{ __('Aucune bonne réponse cette fois. Relis les explications et retente ta chance.') }}</p>
            </section>

            <section class="mx-2 flex flex-col gap-3 rounded-[20px] bg-peche-clair p-[18px] md:mx-0 md:p-6 lg:col-span-2 lg:flex-row lg:items-center lg:justify-between lg:gap-8">
                <div class="flex flex-col gap-2">
                    <div class="font-titre text-xl tracking-[-0.5px]">{{ __('Tu veux connaître ton niveau ?') }}</div>
                    <p class="text-[15px] leading-normal">{{ __('Un test blanc reprend les 4 épreuves avec le chrono de l\'examen et te donne un niveau estimé CECRL et NCLC.') }}</p>
                </div>
                <a href="{{ route('bilan') }}" wire:navigate class="shrink-0 self-start text-[15px] font-bold text-foret underline decoration-2 underline-offset-4 lg:self-center">{{ __('Voir un exemple de bilan') }}</a>
            </section>
        </main>
    @else
        @php($question = $this->question)
        @php($avecDocument = $question->transcription || $question->audio || $question->support)
        @php($derniere = $index + 1 >= $this->total)

        <main wire:key="question-{{ $question->id }}"
            x-data="{ touches(e) {
                if (e.ctrlKey || e.metaKey || e.altKey || e.target.closest('input, textarea, select, [contenteditable]')) return;
                const i = 'abcdef'.indexOf(e.key.toLowerCase());
                if (e.key.length === 1 && i > -1 && i < {{ count($question->choix) }}) $wire.choisir(i);
                else if (e.key === 'Enter' && ! e.target.closest('button, a') && {{ $choix === null ? 'false' : 'true' }}) $wire.suivante();
            } }"
            @keydown.window="touches($event)"
            @class([
                'mx-auto flex w-full flex-1 flex-col gap-4 px-4 pt-4 pb-6 md:gap-5 md:px-6 md:pt-8',
                'max-w-5xl lg:grid lg:grid-cols-2 lg:items-start lg:gap-10' => $avecDocument,
                'max-w-2xl' => ! $avecDocument,
            ])>
            @if ($avecDocument)
                <div class="flex flex-col gap-3 lg:sticky lg:top-32">
                    @if ($question->transcription || $question->audio)
                        <x-lecteur :question="$question" class="md:p-5" />
                    @endif

                    @if ($question->support)
                        <div lang="fr" dir="ltr" class="max-h-[45dvh] overflow-y-auto overscroll-contain rounded-[20px] bg-brume p-4 text-[15px] leading-[1.6] whitespace-pre-line md:p-5 md:text-base lg:max-h-[calc(100dvh-16rem)]">{{ $question->support }}</div>
                    @endif
                </div>
            @endif

            <div class="flex flex-col gap-4 md:gap-5">
                <h1 id="enonce" lang="fr" dir="ltr" class="font-titre text-xl leading-[1.2] tracking-[-0.5px] md:text-[26px]">{{ $question->enonce }}</h1>

                <div role="radiogroup" aria-labelledby="enonce" lang="fr" dir="ltr" class="flex flex-col gap-2.5">
                    @foreach ($question->choix as $i => $libelle)
                        <button type="button" role="radio" aria-checked="{{ $choix === $i ? 'true' : 'false' }}" wire:click="choisir({{ $i }})" @class([
                            'flex min-h-14 w-full items-center gap-3 rounded-2xl px-4 text-start text-base text-foret transition-[colors,transform] active:scale-[0.99]',
                            'border-2 border-foret bg-brume font-bold' => $choix === $i,
                            'border-[1.5px] border-ligne bg-white hover:border-mousse' => $choix !== $i,
                        ])>
                            <span @class([
                                'flex size-7 shrink-0 items-center justify-center rounded-lg text-sm font-bold',
                                'bg-foret text-white' => $choix === $i,
                                'bg-brume' => $choix !== $i,
                            ])>{{ $lettres[$i] }}</span>
                            <span class="min-w-0 flex-1 py-3">{{ $libelle }}</span>
                        </button>
                    @endforeach
                </div>

                <p class="hidden text-[13px] text-mousse md:block">
                    {!! __("Raccourcis : :premiere–:derniere pour choisir, :entree pour continuer. La correction s'affiche à la fin du quiz.", [
                        'premiere' => '<kbd class="rounded bg-brume px-1.5 py-0.5 font-sans font-bold">A</kbd>',
                        'derniere' => '<kbd class="rounded bg-brume px-1.5 py-0.5 font-sans font-bold">'.$lettres[count($question->choix) - 1].'</kbd>',
                        'entree' => '<kbd class="rounded bg-brume px-1.5 py-0.5 font-sans font-bold">'.e(__('Entrée')).'</kbd>',
                    ]) !!}
                </p>
            </div>
        </main>

        {{-- Actions --}}
        <div class="sticky bottom-0 z-10 border-t-[1.5px] border-ligne bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-2xl gap-2.5 px-4 pt-3 pb-[max(1rem,env(safe-area-inset-bottom))] md:pb-6">
                <button type="button" wire:click="passer" wire:loading.attr="disabled" class="h-14 shrink-0 rounded-2xl border-[1.5px] border-ligne bg-white px-[18px] text-base font-bold text-foret hover:border-mousse">{{ __('Passer') }}</button>
                <button type="button" wire:click="suivante" wire:loading.attr="disabled" @disabled($choix === null)
                    class="flex h-14 min-w-0 flex-1 items-center justify-center gap-2 rounded-2xl bg-foret px-4 text-[17px] font-bold text-white hover:bg-vert disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-foret">
                    <span class="truncate">{{ $derniere ? __('Terminer et voir la correction') : __('Question suivante') }}</span>
                    @unless ($derniere)<x-icone nom="droite" :epaisseur="2.6" class="size-5 shrink-0" />@endunless
                </button>
            </div>
        </div>
    @endif
</div>
