<?php

use App\Models\Epreuve;
use App\Models\Question;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    public const CHOIX_MIN = 2;

    public const CHOIX_MAX = 6;

    #[Locked]
    public ?int $questionId = null;

    public ?int $epreuve_id = null;

    public string $categorie = '';

    public string $enonce = '';

    public string $support = '';

    public string $audio = '';

    public ?int $duree_audio = null;

    public string $transcription = '';

    /** @var list<string> */
    public array $choix = ['', '', '', ''];

    public int $bonne_reponse = 0;

    public string $feedback = '';

    public string $explication = '';

    public int $ordre = 0;

    public function mount(?Question $question = null): void
    {
        if ($question?->exists) {
            $this->questionId = $question->id;
            $this->fill($question->only(['epreuve_id', 'categorie', 'enonce', 'duree_audio', 'choix', 'bonne_reponse', 'ordre']));
            $this->fill(array_map(fn ($v) => (string) $v, $question->only(['support', 'audio', 'transcription', 'feedback', 'explication'])));

            return;
        }

        $epreuve = Epreuve::where('slug', request()->query('epreuve'))->first() ?? Epreuve::orderBy('ordre')->first();
        $this->epreuve_id = $epreuve?->id;
        $this->ordre = (int) Question::where('epreuve_id', $this->epreuve_id)->max('ordre') + 1;
    }

    public function render()
    {
        return $this->view()->title($this->questionId ? 'Admin · Modifier la question' : 'Admin · Nouvelle question');
    }

    #[Computed]
    public function epreuves()
    {
        return Epreuve::orderBy('ordre')->get();
    }

    /** @return list<string> catégories existantes, pour les suggestions */
    #[Computed]
    public function categories(): array
    {
        return Question::where('epreuve_id', $this->epreuve_id)->distinct()->orderBy('categorie')->pluck('categorie')->all();
    }

    public function ajouterChoix(): void
    {
        if (count($this->choix) < self::CHOIX_MAX) {
            $this->choix[] = '';
        }
    }

    public function retirerChoix(int $i): void
    {
        if (count($this->choix) <= self::CHOIX_MIN || ! isset($this->choix[$i])) {
            return;
        }

        array_splice($this->choix, $i, 1);

        if ($this->bonne_reponse === $i) {
            $this->bonne_reponse = 0;
        } elseif ($this->bonne_reponse > $i) {
            $this->bonne_reponse--;
        }
    }

    public function enregistrer()
    {
        $donnees = $this->validate([
            'epreuve_id' => ['required', Rule::exists('epreuves', 'id')],
            'categorie' => ['required', 'string', 'max:80'],
            'enonce' => ['required', 'string', 'max:255'],
            'support' => ['nullable', 'string', 'max:5000'],
            'audio' => ['nullable', 'string', 'max:255'],
            'duree_audio' => ['nullable', 'integer', 'min:1', 'max:600'],
            'transcription' => ['nullable', 'string', 'max:5000'],
            'choix' => ['required', 'array', 'min:'.self::CHOIX_MIN, 'max:'.self::CHOIX_MAX],
            'choix.*' => ['required', 'string', 'max:255'],
            'bonne_reponse' => ['required', 'integer', 'min:0', 'max:'.(count($this->choix) - 1)],
            'feedback' => ['nullable', 'string', 'max:255'],
            'explication' => ['required', 'string', 'max:5000'],
            'ordre' => ['required', 'integer', 'min:0'],
        ], attributes: [
            'epreuve_id' => 'épreuve',
            'enonce' => 'énoncé',
            'choix.*' => 'choix',
            'bonne_reponse' => 'bonne réponse',
        ]);

        // Champs texte vides : on stocke null.
        $donnees = array_map(fn ($v) => $v === '' ? null : $v, $donnees);
        $donnees['choix'] = array_values($this->choix);

        Question::updateOrCreate(['id' => $this->questionId], $donnees);

        session()->flash('message', $this->questionId ? 'Question mise à jour.' : 'Question créée.');

        return $this->redirectRoute('admin.questions', navigate: true);
    }
};
?>

<div class="flex min-h-dvh flex-col bg-brume">
    <x-espace.barre />
    @php($lettres = ['A', 'B', 'C', 'D', 'E', 'F'])
    @php($champ = 'w-full rounded-xl border-[1.5px] border-ligne bg-white px-3.5 py-2.5 text-[15px] outline-none focus:border-vert')

    <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-5 px-4 py-8">
        <div>
            <a href="{{ route('admin.questions') }}" wire:navigate class="inline-flex items-center gap-1 text-sm font-bold text-mousse"><x-icone nom="gauche" class="size-4" />Toutes les questions</a>
            <h1 class="font-titre text-[28px] leading-tight tracking-[-0.5px]">{{ $questionId ? 'Modifier la question' : 'Nouvelle question' }}</h1>
        </div>

        <form wire:submit="enregistrer" class="flex flex-col gap-5">
            {{-- Classement --}}
            <section class="grid gap-4 rounded-[20px] border-[1.5px] border-ligne bg-white p-5 sm:grid-cols-3">
                <label class="flex flex-col gap-1.5 text-sm font-bold">Épreuve
                    <select wire:model.live="epreuve_id" class="{{ $champ }} font-normal">
                        @foreach ($this->epreuves as $epreuve)
                            <option value="{{ $epreuve->id }}" wire:key="ep-{{ $epreuve->id }}">{{ $epreuve->nom }}</option>
                        @endforeach
                    </select>
                    @error('epreuve_id') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                </label>
                <label class="flex flex-col gap-1.5 text-sm font-bold">Catégorie
                    <input type="text" wire:model="categorie" list="categories" placeholder="Ex. Vie quotidienne" class="{{ $champ }} font-normal">
                    <datalist id="categories">
                        @foreach ($this->categories as $c) <option value="{{ $c }}"></option> @endforeach
                    </datalist>
                    @error('categorie') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                </label>
                <label class="flex flex-col gap-1.5 text-sm font-bold">Ordre
                    <input type="number" min="0" wire:model="ordre" class="{{ $champ }} font-normal">
                    @error('ordre') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                </label>
            </section>

            {{-- Support --}}
            <section class="flex flex-col gap-4 rounded-[20px] border-[1.5px] border-ligne bg-white p-5">
                <h2 class="text-[15px] font-extrabold">Support</h2>
                <label class="flex flex-col gap-1.5 text-sm font-bold">Transcription / texte lu à voix haute
                    <span class="font-normal text-mousse">Sans fichier audio, ce texte est lu une seule fois par la voix de synthèse du navigateur.</span>
                    <textarea wire:model="transcription" rows="4" class="{{ $champ }} font-normal"></textarea>
                    @error('transcription') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                </label>
                <div class="grid gap-4 sm:grid-cols-[1fr_160px]">
                    <label class="flex flex-col gap-1.5 text-sm font-bold">Fichier audio (optionnel)
                        <input type="text" wire:model="audio" placeholder="audios/question-12.mp3 ou https://…" class="{{ $champ }} font-normal">
                        @error('audio') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="flex flex-col gap-1.5 text-sm font-bold">Durée (s)
                        <input type="number" min="1" wire:model="duree_audio" placeholder="30" class="{{ $champ }} font-normal">
                        @error('duree_audio') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                    </label>
                </div>
                <label class="flex flex-col gap-1.5 text-sm font-bold">Document à lire (compréhension écrite)
                    <textarea wire:model="support" rows="4" class="{{ $champ }} font-normal"></textarea>
                    @error('support') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                </label>
            </section>

            {{-- QCM --}}
            <section class="flex flex-col gap-4 rounded-[20px] border-[1.5px] border-ligne bg-white p-5">
                <label class="flex flex-col gap-1.5 text-sm font-bold">Énoncé
                    <input type="text" wire:model="enonce" class="{{ $champ }} font-normal">
                    @error('enonce') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                </label>

                <fieldset class="flex flex-col gap-2">
                    <legend class="mb-1.5 text-sm font-bold">Choix <span class="font-normal text-mousse">— coche la bonne réponse</span></legend>
                    @foreach ($choix as $i => $libelle)
                        <div wire:key="choix-{{ $i }}" class="flex items-center gap-2">
                            <input type="radio" wire:model="bonne_reponse" value="{{ $i }}" name="bonne_reponse" aria-label="Bonne réponse : choix {{ $lettres[$i] }}" class="size-5 accent-vert">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brume text-sm font-bold">{{ $lettres[$i] }}</span>
                            <input type="text" wire:model="choix.{{ $i }}" aria-label="Choix {{ $lettres[$i] }}" class="{{ $champ }}">
                            <button type="button" wire:click="retirerChoix({{ $i }})" @disabled(count($choix) <= 2) aria-label="Retirer le choix {{ $lettres[$i] }}"
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg text-mousse hover:bg-peche-clair disabled:opacity-30">
                                <x-icone nom="croix" class="size-4" />
                            </button>
                        </div>
                        @error('choix.'.$i) <span class="pl-[76px] text-sm text-red-700">{{ $message }}</span> @enderror
                    @endforeach
                    @error('bonne_reponse') <span class="text-sm text-red-700">{{ $message }}</span> @enderror
                    @if (count($choix) < 6)
                        <button type="button" wire:click="ajouterChoix" class="self-start rounded-lg px-2 py-1.5 text-sm font-bold text-vert hover:bg-brume">+ Ajouter un choix</button>
                    @endif
                </fieldset>
            </section>

            {{-- Correction --}}
            <section class="flex flex-col gap-4 rounded-[20px] border-[1.5px] border-ligne bg-white p-5">
                <label class="flex flex-col gap-1.5 text-sm font-bold">Explication
                    <textarea wire:model="explication" rows="3" class="{{ $champ }} font-normal"></textarea>
                    @error('explication') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                </label>
                <label class="flex flex-col gap-1.5 text-sm font-bold">Message si bonne réponse (quiz)
                    <input type="text" wire:model="feedback" class="{{ $champ }} font-normal">
                    @error('feedback') <span class="font-normal text-red-700">{{ $message }}</span> @enderror
                </label>
            </section>

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.questions') }}" wire:navigate class="flex h-12 items-center rounded-2xl border-[1.5px] border-ligne bg-white px-5 font-bold text-foret">Annuler</a>
                <button type="submit" class="h-12 rounded-2xl bg-foret px-6 font-bold text-white hover:bg-vert">Enregistrer</button>
            </div>
        </form>
    </main>
</div>
