<?php

use App\Models\Epreuve;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function epreuves()
    {
        return Epreuve::orderBy('ordre')->withCount('questions')->get();
    }

    public function render()
    {
        return $this->view()->title('Mon espace');
    }
};
?>

<div class="flex min-h-dvh flex-col bg-brume">
    <x-espace.barre />

    <main class="mx-auto flex w-full max-w-[1200px] flex-1 flex-col gap-6 px-4 py-8 md:px-8">
        <section class="relative isolate flex flex-col gap-2 overflow-hidden rounded-[28px] bg-menthe px-6 py-7">
            <x-arcs class="-top-[120px] -right-[120px] size-[300px]" />
            <div class="text-sm font-bold text-mousse-fonce">Bonjour {{ explode(' ', auth()->user()->name)[0] }} 👋</div>
            <h1 class="font-titre text-[28px] leading-tight tracking-[-0.5px] md:text-[36px]">Prêt pour un test blanc&nbsp;?</h1>
            <p class="max-w-xl text-base text-mousse-fonce">Conditions réelles : chrono de l'examen, une seule écoute par audio, correction à la fin.</p>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="font-titre text-[22px] tracking-[-0.5px]">Tests blancs</h2>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($this->epreuves as $epreuve)
                    <div wire:key="epreuve-{{ $epreuve->id }}" class="flex items-center gap-4 rounded-[20px] border-[1.5px] border-ligne bg-white p-5">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-[14px] bg-brume text-vert"><x-icone :nom="$epreuve->icone" class="size-6" /></div>
                        <div class="flex flex-1 flex-col gap-0.5">
                            <div class="text-[17px] font-bold">{{ $epreuve->nom }}</div>
                            <div class="text-sm text-mousse">
                                @if ($epreuve->questions_count)
                                    {{ $epreuve->questions_count }} questions · {{ $epreuve->duree_test ?? 30 }} min
                                @else
                                    Bientôt disponible
                                @endif
                            </div>
                        </div>
                        @if ($epreuve->questions_count)
                            <a href="{{ route('test-blanc', $epreuve) }}" wire:navigate class="rounded-xl bg-foret px-4 py-2.5 text-sm font-bold text-white hover:bg-vert hover:text-white">Commencer</a>
                        @else
                            <span class="rounded-xl bg-brume px-4 py-2.5 text-sm font-bold text-mousse">Bientôt</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </main>
</div>
