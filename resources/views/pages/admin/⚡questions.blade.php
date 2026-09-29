<?php

use App\Models\Epreuve;
use App\Models\Question;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'epreuve')]
    public string $filtre = '';

    #[Url(as: 'q')]
    public string $recherche = '';

    public ?string $message = null;

    public function mount(): void
    {
        $this->message = session('message');
    }

    public function render()
    {
        return $this->view()->title('Admin · Questions');
    }

    #[Computed]
    public function epreuves()
    {
        return Epreuve::orderBy('ordre')->withCount('questions')->get();
    }

    #[Computed]
    public function questions()
    {
        return Question::with('epreuve')
            ->when($this->filtre, fn ($q) => $q->whereRelation('epreuve', 'slug', $this->filtre))
            ->when(trim($this->recherche), fn ($q, $terme) => $q->where(fn ($q) => $q
                ->where('enonce', 'like', "%{$terme}%")
                ->orWhere('categorie', 'like', "%{$terme}%")))
            ->orderBy('epreuve_id')
            ->orderBy('categorie')
            ->orderBy('ordre')
            ->get()
            ->groupBy(fn (Question $q) => $q->epreuve->nom.' · '.$q->categorie);
    }

    public function supprimer(int $id): void
    {
        Question::findOrFail($id)->delete();
        $this->message = 'Question supprimée.';
        unset($this->questions, $this->epreuves);
    }
};
?>

<div class="flex min-h-dvh flex-col bg-brume">
    <x-espace.barre />

    <main class="mx-auto flex w-full max-w-[1200px] flex-1 flex-col gap-5 px-4 py-8 md:px-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="text-sm font-bold text-mousse">Panel admin</div>
                <h1 class="font-titre text-[28px] leading-tight tracking-[-0.5px]">Questions</h1>
            </div>
            <a href="{{ route('admin.questions.creer', $filtre ? ['epreuve' => $filtre] : []) }}" wire:navigate class="flex h-11 items-center gap-2 rounded-xl bg-foret px-4 text-sm font-bold text-white hover:bg-vert hover:text-white">
                <span class="text-lg leading-none">+</span> Nouvelle question
            </a>
        </div>

        @if ($message)
            <div role="status" class="rounded-2xl bg-menthe px-4 py-3 text-[15px] font-semibold">{{ $message }}</div>
        @endif

        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <div class="flex flex-wrap gap-1.5">
                <button type="button" wire:click="$set('filtre', '')" @class(['rounded-full px-3.5 py-2 text-sm font-bold', 'bg-foret text-white' => ! $filtre, 'bg-white text-foret' => $filtre])>Toutes</button>
                @foreach ($this->epreuves as $epreuve)
                    <button type="button" wire:key="filtre-{{ $epreuve->id }}" wire:click="$set('filtre', '{{ $epreuve->slug }}')" @class(['rounded-full px-3.5 py-2 text-sm font-bold', 'bg-foret text-white' => $filtre === $epreuve->slug, 'bg-white text-foret' => $filtre !== $epreuve->slug])>
                        {{ $epreuve->nom }} <span class="opacity-60">{{ $epreuve->questions_count }}</span>
                    </button>
                @endforeach
            </div>
            <input type="search" wire:model.live.debounce.300ms="recherche" placeholder="Rechercher une question ou une catégorie…" aria-label="Rechercher"
                class="h-11 flex-1 rounded-xl border-[1.5px] border-ligne bg-white px-4 text-[15px] outline-none focus:border-vert md:ml-auto md:max-w-sm">
        </div>

        @forelse ($this->questions as $groupe => $questions)
            <section wire:key="groupe-{{ md5($groupe) }}" class="flex flex-col gap-2">
                <h2 class="text-[13px] font-bold tracking-wide text-mousse-fonce uppercase">{{ $groupe }} <span class="text-mousse">({{ $questions->count() }})</span></h2>
                <ul class="flex flex-col divide-y-[1.5px] divide-ligne overflow-hidden rounded-2xl border-[1.5px] border-ligne bg-white">
                    @foreach ($questions as $question)
                        <li wire:key="question-{{ $question->id }}" class="flex items-center gap-3 px-4 py-3">
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <span class="truncate text-[15px] font-semibold">{{ $question->enonce }}</span>
                                <span class="truncate text-[13px] text-mousse">
                                    {{ count($question->choix) }} choix · réponse : {{ $question->choix[$question->bonne_reponse] ?? '?' }}
                                    @if ($question->audio) · 🎧 fichier audio @elseif ($question->transcription) · 🗣️ voix de synthèse @endif
                                </span>
                            </div>
                            <a href="{{ route('admin.questions.modifier', $question) }}" wire:navigate class="rounded-lg px-3 py-2 text-sm font-bold text-vert hover:bg-brume">Modifier</a>
                            <button type="button" wire:click="supprimer({{ $question->id }})" wire:confirm="Supprimer cette question ?" class="rounded-lg px-3 py-2 text-sm font-bold text-mousse hover:bg-peche-clair hover:text-foret">Supprimer</button>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="rounded-2xl bg-white p-6 text-center text-mousse">Aucune question trouvée.</p>
        @endforelse
    </main>
</div>
