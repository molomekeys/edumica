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

<div class="flex min-h-dvh flex-col bg-papier">
    <x-espace.barre />

    <main class="mx-auto flex w-full max-w-[1200px] flex-1 flex-col gap-6 px-4 py-8 md:px-8">
        <section class="relative isolate flex flex-col gap-2 overflow-hidden rounded-[28px] border-[1.5px] border-trait bg-white px-6 py-7">
            <x-arcs couleur="#FFB59E" class="-top-[120px] -right-[120px] size-[300px]" />
            <div class="text-sm font-bold text-cendre">Bonjour {{ explode(' ', auth()->user()->name)[0] }} 👋</div>
            <h1 class="font-titre text-[28px] leading-tight tracking-[-0.5px] md:text-[36px]">Prêt pour un test blanc&nbsp;?</h1>
            <p class="max-w-xl text-base text-cendre">Conditions réelles : chrono de l'examen, une seule écoute par audio, correction à la fin.</p>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="font-titre text-[22px] tracking-[-0.5px]">Tests blancs</h2>

            {{-- Test complet : toutes les épreuves disponibles d'affilée, dans la même interface --}}
            @php($disponibles = $this->epreuves->where('questions_count', '>', 0))
            @if ($disponibles->isNotEmpty())
                <div class="relative isolate flex flex-col gap-4 overflow-hidden rounded-[20px] bg-foret p-5 text-white sm:flex-row sm:items-center md:p-6">
                    <x-arcs couleur="#FFB59E" class="-top-[140px] -right-[100px] size-[300px]" />
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-[14px] bg-white/10 text-peche"><x-icone nom="chrono" class="size-6" /></div>
                    <div class="flex flex-1 flex-col gap-0.5">
                        <div class="text-[12px] font-bold tracking-[0.14em] text-peche uppercase">Test blanc complet</div>
                        <div class="text-[17px] font-bold">{{ $disponibles->pluck('nom')->join(', ', ' et ') }}</div>
                        <div class="text-sm text-white/70">
                            {{ $disponibles->sum('questions_count') }} questions · {{ $disponibles->sum(fn ($epreuve) => $epreuve->duree_test ?? 30) }} min · un seul chrono
                        </div>
                    </div>
                    <a href="{{ route('test-blanc.complet') }}" wire:navigate class="self-start rounded-xl bg-peche px-5 py-3 text-sm font-bold text-foret hover:bg-white hover:text-foret sm:self-center">Commencer le test complet</a>
                </div>
            @endif

            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($this->epreuves as $epreuve)
                    <div wire:key="epreuve-{{ $epreuve->id }}" class="flex items-center gap-4 rounded-[20px] border-[1.5px] border-trait bg-white p-5">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-[14px] bg-peche-clair text-foret"><x-icone :nom="$epreuve->icone" class="size-6" /></div>
                        <div class="flex flex-1 flex-col gap-0.5">
                            <div class="text-[17px] font-bold">{{ $epreuve->nom }}</div>
                            <div class="text-sm text-cendre">
                                @if ($epreuve->questions_count)
                                    {{ $epreuve->questions_count }} questions · {{ $epreuve->duree_test ?? 30 }} min
                                @else
                                    Bientôt disponible
                                @endif
                            </div>
                        </div>
                        @if ($epreuve->questions_count)
                            <a href="{{ route('test-blanc', $epreuve) }}" wire:navigate class="rounded-xl bg-foret px-4 py-2.5 text-sm font-bold text-white hover:bg-foret/90 hover:text-white">Commencer</a>
                        @else
                            <span class="rounded-xl bg-papier px-4 py-2.5 text-sm font-bold text-cendre">Bientôt</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </main>
</div>
