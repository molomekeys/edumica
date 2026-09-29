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

    #[Locked]
    public bool $corrige = false;

    /** @var array<int, array{choix: ?int, correcte: bool}> index de question => réponse (choix null : question passée) */
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
        return $this->view()->title('Quiz · '.$this->epreuve->nom);
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

    #[Computed]
    public function bonnes(): int
    {
        return collect($this->reponses)->where('correcte', true)->count();
    }

    public function choisir(int $choix): void
    {
        if ($this->corrige || $this->termine || ! isset($this->question->choix[$choix])) {
            return;
        }

        $this->choix = $choix;
    }

    public function valider(): void
    {
        if ($this->choix === null || $this->corrige || $this->termine) {
            return;
        }

        if ($this->tempsEcoule()) {
            $this->terminer();

            return;
        }

        $this->reponses[$this->index] = ['choix' => $this->choix, 'correcte' => $this->question->estCorrecte($this->choix)];
        $this->corrige = true;
    }

    public function passer(): void
    {
        if ($this->corrige || $this->termine) {
            return;
        }

        $this->reponses[$this->index] = ['choix' => null, 'correcte' => false];
        $this->suivante();
    }

    public function suivante(): void
    {
        if ($this->termine) {
            return;
        }

        if ($this->index + 1 >= $this->total || $this->tempsEcoule()) {
            $this->terminer();

            return;
        }

        $this->index++;
        $this->choix = null;
        $this->corrige = false;
        unset($this->question);
    }

    public function terminer(): void
    {
        $this->termine = true;
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

    {{-- En-tête --}}
    <header class="border-b-[1.5px] border-ligne">
        <div class="mx-auto flex max-w-2xl flex-col gap-2 pt-2 pr-3 pb-3 pl-2">
            <div class="flex items-center justify-between">
                <a href="{{ route('accueil') }}" wire:navigate aria-label="Quitter le quiz" class="flex size-12 items-center justify-center text-foret">
                    <x-icone nom="croix" class="size-6" />
                </a>
                <div class="text-[15px] font-bold">{{ $epreuve->nom }}</div>

                @if ($termine || $this->total === 0)
                    <div class="w-12"></div>
                @elseif ($corrige)
                    <div class="flex items-center gap-1.5 rounded-full bg-brume px-3 py-1.5 text-sm font-bold tabular-nums">
                        <x-icone nom="coche" :epaisseur="2.6" class="size-4 text-vert" />
                        <span><span class="sr-only">Bonnes réponses : </span>{{ $this->bonnes }} / {{ count($reponses) }}</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-bold tabular-nums"
                        :class="reste <= 60 ? 'bg-peche-clair' : 'bg-brume'" role="timer" aria-label="Temps restant">
                        <x-icone nom="horloge" class="size-4" />
                        <span x-text="affichage">{{ gmdate('i:s', max(0, $fin - now()->getTimestamp())) }}</span>
                    </div>
                @endif
            </div>

            @if (! $termine && $this->total > 0)
                <div class="flex items-center gap-3 pl-3">
                    <div class="h-2 flex-1 rounded-full bg-brume" role="progressbar" aria-label="Progression" aria-valuemin="1" aria-valuemax="{{ $this->total }}" aria-valuenow="{{ $index + 1 }}">
                        <div class="h-2 rounded-full bg-vert transition-[width] duration-300" style="width: {{ ($index + 1) / $this->total * 100 }}%"></div>
                    </div>
                    <div class="text-[13px] font-bold text-mousse tabular-nums">{{ $index + 1 }} / {{ $this->total }}</div>
                </div>
            @endif
        </div>
    </header>

    @if ($this->total === 0)
        {{-- Aucun quiz pour cette épreuve --}}
        <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col items-center justify-center gap-4 p-5 text-center">
            <div class="flex size-16 items-center justify-center rounded-full bg-vert text-white"><x-icone :nom="$epreuve->icone" class="size-7" /></div>
            <h1 class="font-titre text-[22px] leading-tight tracking-[-0.5px]">Pas encore de quiz pour cette épreuve</h1>
            <p class="max-w-sm text-base leading-normal text-mousse">Les quiz d'{{ mb_strtolower($epreuve->nom) }} ne sont pas encore disponibles. Entraîne-toi sur une autre épreuve en attendant.</p>
            <a href="{{ route('epreuves') }}" wire:navigate class="mt-2 flex h-14 items-center justify-center rounded-2xl bg-foret px-8 text-[17px] font-bold text-white hover:bg-vert hover:text-white">Choisir une autre épreuve</a>
        </main>
    @elseif ($termine)
        {{-- Résultat du quiz --}}
        @php($passees = collect($reponses)->whereNull('choix')->count())
        @php($taux = $this->bonnes / $this->total)

        <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-6 px-3 pt-4 pb-10">
            <section class="relative isolate flex flex-col gap-3 overflow-hidden rounded-[28px] bg-menthe px-5 py-6">
                <x-arcs class="-top-[120px] -right-[120px] size-[300px]" />
                <div class="text-[13px] font-bold text-mousse-fonce">Quiz terminé · {{ $epreuve->nom }}</div>
                <div class="font-titre text-[56px] leading-none tracking-[-1.5px]">{{ $this->bonnes }}<span class="text-[28px] text-mousse-fonce"> / {{ $this->total }}</span></div>
                <h1 class="font-titre text-[26px] leading-[1.1] tracking-[-0.5px]">
                    @if ($taux >= 0.8) Très bon travail.
                    @elseif ($taux >= 0.5) C'est un bon début.
                    @else Continue à t'entraîner.
                    @endif
                </h1>
                <p class="text-base leading-normal text-mousse-fonce">
                    {{ $this->bonnes }} {{ $this->bonnes > 1 ? 'bonnes réponses' : 'bonne réponse' }} sur {{ $this->total }}@if ($passees) · {{ $passees }} {{ $passees > 1 ? 'questions passées' : 'question passée' }}@endif.
                    @if (count($reponses) < $this->total) Le temps est écoulé avant la fin. @endif
                </p>
            </section>

            <section class="flex flex-col gap-3 px-2">
                <h2 class="font-titre text-[22px] tracking-[-0.5px]">Tes réponses</h2>
                <ol class="flex flex-col gap-2">
                    @foreach ($this->questions as $i => $q)
                        @php($reponse = $reponses[$i] ?? null)
                        <li wire:key="recap-{{ $q->id }}" class="flex items-center gap-3 rounded-2xl border-[1.5px] border-ligne px-3.5 py-3">
                            @if ($reponse && $reponse['correcte'])
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-vert text-white"><x-icone nom="coche" :epaisseur="3" class="size-4" /></span>
                            @elseif ($reponse && $reponse['choix'] !== null)
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-peche text-foret"><x-icone nom="croix" :epaisseur="3" class="size-4" /></span>
                            @else
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brume text-mousse"><x-icone nom="droite" :epaisseur="2.6" class="size-4" /></span>
                            @endif
                            <div class="flex flex-1 flex-col">
                                <span class="text-[15px] font-semibold">{{ $q->enonce }}</span>
                                <span class="text-[13px] text-mousse">
                                    @if (! $reponse) Pas répondu
                                    @elseif ($reponse['correcte']) Réussie · {{ $q->choix[$q->bonne_reponse] }}
                                    @elseif ($reponse['choix'] === null) Passée · réponse : {{ $q->choix[$q->bonne_reponse] }}
                                    @else À revoir · réponse : {{ $q->choix[$q->bonne_reponse] }}
                                    @endif
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="flex flex-col gap-2.5 px-2">
                <a href="{{ route('quiz', $epreuve) }}" wire:navigate class="flex h-14 items-center justify-center gap-2 rounded-2xl bg-foret text-[17px] font-bold text-white hover:bg-vert hover:text-white">
                    <x-icone nom="relancer" class="size-5" />Refaire un quiz
                </a>
                <a href="{{ route('epreuves') }}" wire:navigate class="flex h-[52px] items-center justify-center rounded-2xl border-[1.5px] border-foret text-base font-bold text-foret">Choisir une autre épreuve</a>
            </section>

            <section class="mx-2 flex flex-col gap-3 rounded-[20px] bg-peche-clair p-[18px]">
                <div class="font-titre text-xl tracking-[-0.5px]">Tu veux connaître ton niveau&nbsp;?</div>
                <p class="text-[15px] leading-normal">Un test blanc reprend les 4 épreuves avec le chrono de l'examen et te donne un niveau estimé CECRL et NCLC.</p>
                <a href="{{ route('bilan') }}" wire:navigate class="self-start text-[15px] font-bold text-foret underline decoration-2 underline-offset-4">Voir un exemple de bilan</a>
            </section>
        </main>
    @else
        @php($question = $this->question)

        <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 p-5" wire:key="question-{{ $question->id }}">
            @if ($corrige)
                {{-- Correction --}}
                @php($correcte = $reponses[$index]['correcte'])

                <div role="status" @class(['flex items-center gap-3 rounded-2xl px-4 py-3.5', 'bg-menthe' => $correcte, 'bg-peche-clair' => ! $correcte])>
                    <div @class(['flex size-9 shrink-0 items-center justify-center rounded-full', 'bg-vert text-white' => $correcte, 'bg-peche text-foret' => ! $correcte])>
                        <x-icone :nom="$correcte ? 'coche' : 'croix'" :epaisseur="2.6" class="size-5" />
                    </div>
                    <div>
                        <div class="text-[17px] font-extrabold">{{ $correcte ? 'Bonne réponse' : 'Pas tout à fait' }}</div>
                        <div class="text-sm text-mousse-fonce">
                            {{ $correcte ? $question->feedback : 'La bonne réponse était la '.$lettres[$question->bonne_reponse].'.' }}
                        </div>
                    </div>
                </div>

                <h1 class="font-titre text-xl leading-[1.2] tracking-[-0.5px]">{{ $question->enonce }}</h1>

                <ul class="flex flex-col gap-2">
                    @foreach ($question->choix as $i => $libelle)
                        @php($estBonne = $i === $question->bonne_reponse)
                        @php($estChoisie = $i === $choix)
                        <li @class([
                            'flex items-center gap-3 rounded-[14px] px-3.5',
                            'min-h-[52px] border-2 border-vert bg-brume text-base font-bold' => $estBonne,
                            'min-h-[52px] border-2 border-peche bg-peche-clair text-base font-bold' => $estChoisie && ! $estBonne,
                            'min-h-12 border-[1.5px] border-ligne text-[15px] text-mousse' => ! $estBonne && ! $estChoisie,
                        ])>
                            @if ($estBonne)
                                <span class="flex size-[26px] shrink-0 items-center justify-center rounded-lg bg-vert text-white"><x-icone nom="coche" :epaisseur="3" class="size-4" /></span>
                            @elseif ($estChoisie)
                                <span class="flex size-[26px] shrink-0 items-center justify-center rounded-lg bg-peche text-foret"><x-icone nom="croix" :epaisseur="3" class="size-4" /></span>
                            @else
                                <span class="flex size-[26px] shrink-0 items-center justify-center rounded-lg bg-brume text-[13px] font-bold text-foret">{{ $lettres[$i] }}</span>
                            @endif
                            <span class="flex-1 py-2">{{ $libelle }}</span>
                            @if ($estChoisie)
                                <span @class(['text-[13px]', 'text-vert' => $estBonne, 'text-foret' => ! $estBonne])>Ta réponse</span>
                            @elseif ($estBonne)
                                <span class="text-[13px] text-vert">Bonne réponse</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <div class="flex flex-col gap-2 rounded-2xl border-[1.5px] border-ligne p-4" x-data="{ transcription: false }">
                    <div class="text-[15px] font-extrabold">Pourquoi ?</div>
                    <p class="text-[15px] leading-[1.55] text-mousse">{{ $question->explication }}</p>
                    @if ($question->transcription)
                        <button type="button" @click="transcription = !transcription" :aria-expanded="transcription" aria-controls="transcription"
                            class="flex min-h-11 items-center gap-1.5 self-start text-[15px] font-bold text-vert">
                            <span x-text="transcription ? 'Masquer la transcription' : 'Lire la transcription'">Lire la transcription</span>
                            <x-icone nom="bas" class="size-[18px] transition-transform" x-bind:class="transcription && 'rotate-180'" />
                        </button>
                        <p id="transcription" x-show="transcription" x-collapse x-cloak class="rounded-xl bg-brume p-3.5 text-[15px] leading-[1.55] whitespace-pre-line">{{ $question->transcription }}</p>
                    @endif
                </div>
            @else
                {{-- Question --}}
                @if ($question->transcription || $question->audio)
                    <x-lecteur :question="$question" />
                @endif

                @if ($question->support)
                    <div class="rounded-[20px] bg-brume p-4 text-[15px] leading-[1.6] whitespace-pre-line">{{ $question->support }}</div>
                @endif

                <h1 id="enonce" class="font-titre text-[22px] leading-[1.2] tracking-[-0.5px]">{{ $question->enonce }}</h1>

                <div role="radiogroup" aria-labelledby="enonce" class="flex flex-col gap-2.5">
                    @foreach ($question->choix as $i => $libelle)
                        <button type="button" role="radio" aria-checked="{{ $choix === $i ? 'true' : 'false' }}" wire:click="choisir({{ $i }})" @class([
                            'flex min-h-14 items-center gap-3 rounded-2xl px-4 text-left text-base text-foret transition-colors',
                            'border-2 border-foret bg-brume font-bold' => $choix === $i,
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
            @endif
        </main>

        {{-- Actions --}}
        <div class="sticky bottom-0 border-t-[1.5px] border-ligne bg-white">
            <div class="mx-auto flex max-w-2xl gap-2.5 px-4 pt-3 pb-7">
                @if ($corrige)
                    <button type="button" wire:click="suivante" class="h-14 w-full rounded-2xl bg-foret text-[17px] font-bold text-white hover:bg-vert">
                        {{ $index + 1 >= $this->total ? 'Voir mon résultat' : 'Question suivante' }}
                    </button>
                @else
                    <button type="button" wire:click="passer" class="h-14 rounded-2xl border-[1.5px] border-ligne bg-white px-[18px] text-base font-bold text-foret hover:border-mousse">Passer</button>
                    <button type="button" wire:click="valider" @disabled($choix === null) class="h-14 flex-1 rounded-2xl bg-foret text-[17px] font-bold text-white hover:bg-vert disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-foret">Valider ma réponse</button>
                @endif
            </div>
        </div>
    @endif
</div>
