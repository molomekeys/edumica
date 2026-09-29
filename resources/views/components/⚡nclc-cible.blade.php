<?php

use App\Support\Nclc;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $cible = Nclc::CIBLE_PAR_DEFAUT;

    public function choisir(string $niveau): void
    {
        if (Nclc::existe($niveau)) {
            $this->cible = $niveau;
        }
    }

    /** @return array<string, int> */
    #[Computed]
    public function minimums(): array
    {
        return Nclc::minimums($this->cible);
    }
};
?>

<div class="flex flex-col gap-3.5">
    <div role="group" aria-label="NCLC cible" class="grid grid-cols-6 gap-1.5">
        @foreach (Nclc::niveaux() as $niveau)
            <button type="button" wire:click="choisir('{{ $niveau }}')" aria-pressed="{{ $niveau === $cible ? 'true' : 'false' }}" aria-label="NCLC {{ $niveau }}" @class([
                'h-12 rounded-[14px] text-base transition-colors',
                'bg-foret font-extrabold text-white' => $niveau === $cible,
                'border-[1.5px] border-ligne bg-white font-bold text-foret hover:border-foret' => $niveau !== $cible,
            ])>{{ $niveau }}</button>
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-2.5" aria-live="polite">
        @foreach (['co', 'ce', 'eo', 'ee'] as $code)
            <div class="flex flex-col gap-0.5 rounded-2xl bg-brume px-4 py-3.5">
                <div class="text-sm text-mousse">{{ Nclc::libelle($code) }}</div>
                <div class="font-titre text-2xl">
                    {{ $this->minimums[$code] }}<span class="font-sans text-sm font-semibold text-mousse"> / {{ Nclc::maximum($code) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <p class="text-[13px] leading-normal text-mousse">Minimum par épreuve selon le barème IRCC. Vérifie le niveau exigé par ton programme.</p>
</div>
