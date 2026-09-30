<?php

use App\Models\Epreuve;
use App\Support\Nclc;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    /** Scores d'exemple d'un test blanc (TCF Canada). */
    public const SCORES = ['co' => 480, 'ce' => 505, 'eo' => 11, 'ee' => 9];

    /** Conseils d'exemple, par épreuve. */
    public const CONSEILS = [
        'ee' => "La tâche 3, le texte argumenté, t'a coûté le plus de points. On te propose 5 quiz ciblés.",
    ];

    #[Url(as: 'objectif', except: Nclc::CIBLE_PAR_DEFAUT)]
    public string $cible = Nclc::CIBLE_PAR_DEFAUT;

    public bool $modifier = false;

    public function mount(): void
    {
        if (! Nclc::existe($this->cible)) {
            $this->cible = Nclc::CIBLE_PAR_DEFAUT;
        }
    }

    public function choisir(string $niveau): void
    {
        if (Nclc::existe($niveau)) {
            $this->cible = $niveau;
        }

        $this->modifier = false;
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function resultats(): array
    {
        return collect(self::SCORES)->map(function (int $score, string $code) {
            $minimum = Nclc::minimum($this->cible, $code);
            $maximum = Nclc::maximum($code);

            return [
                'code' => $code,
                'nom' => Nclc::libelle($code),
                'score' => $score,
                'maximum' => $maximum,
                'minimum' => $minimum,
                'niveau' => Nclc::niveauPour($code, $score),
                'atteint' => $score >= $minimum,
                'ecart' => $minimum - $score,
                'barre' => round($score / $maximum * 100, 1),
                'trait' => round($minimum / $maximum * 100, 1),
            ];
        })->values()->all();
    }

    /** L'épreuve la plus éloignée de l'objectif, en proportion du barème. */
    #[Computed]
    public function priorite(): array
    {
        return collect($this->resultats)->sortByDesc(fn ($r) => $r['ecart'] / $r['maximum'])->first();
    }

    #[Computed]
    public function epreuves()
    {
        return Epreuve::pluck('slug', 'code');
    }

    public function render()
    {
        return $this->view()->title(__('Ton bilan'));
    }
};
?>

@php
    $resultats = collect($this->resultats);
    $atteints = $resultats->where('atteint', true)->count();
    $manquants = $resultats->where('atteint', false);
    $priorite = $this->priorite;
@endphp

<div class="flex min-h-dvh flex-col">
    <header class="border-b-[1.5px] border-ligne">
        <div class="mx-auto flex h-[60px] max-w-2xl items-center justify-between px-2">
            <a href="{{ route('accueil') }}" wire:navigate aria-label="{{ __('Retour à l\'accueil') }}" class="flex size-12 items-center justify-center text-foret">
                <x-icone nom="gauche" class="size-6" />
            </a>
            <h1 class="text-base font-bold">{{ __('Ton bilan') }}</h1>
            <div x-data="{ copie: false }" class="relative">
                <button type="button" aria-label="{{ __('Partager le bilan') }}" class="flex size-12 items-center justify-center"
                    @click="navigator.share ? navigator.share({ title: document.title, url: location.href }).catch(() => {}) : navigator.clipboard.writeText(location.href).then(() => { copie = true; setTimeout(() => copie = false, 2000) })">
                    <x-icone nom="partager" class="size-[22px]" />
                </button>
                <div x-show="copie" x-cloak x-transition.opacity role="status" class="absolute top-full end-2 rounded-lg bg-foret px-3 py-1.5 text-[13px] font-bold whitespace-nowrap text-white">{{ __('Lien copié') }}</div>
            </div>
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-2xl flex-1 flex-col">
        {{-- Synthèse --}}
        <section class="px-3 pt-4">
            <div class="relative isolate flex flex-col gap-3.5 overflow-hidden rounded-[28px] bg-menthe px-5 py-6">
                <x-arcs class="-top-[120px] -right-[120px] size-[300px]" />
                <div class="text-[13px] font-bold text-mousse-fonce">{{ __('Test blanc complet · [DATE] · Exemple') }}</div>
                <h2 class="font-titre text-[30px] leading-[1.08] tracking-[-1px]">
                    @if ($atteints === 4) {{ __('NCLC :niveau partout.', ['niveau' => $cible]) }}
                    @elseif ($atteints === 3) {{ __('Presque NCLC :niveau partout.', ['niveau' => $cible]) }}
                    @else {{ __('En route vers le NCLC :niveau.', ['niveau' => $cible]) }}
                    @endif
                </h2>
                <p class="text-base leading-normal text-mousse-fonce">
                    @if ($atteints === 4)
                        {{ __('Tu atteins ton objectif dans les 4 épreuves.') }}
                    @else
                        {{ trans_choice('Tu atteins ton objectif dans :count épreuve sur 4.|Tu atteins ton objectif dans :count épreuves sur 4.', $atteints) }}
                        @if ($manquants->count() === 1)
                            @php($m = $manquants->first())
                            {{ trans_choice('Il te manque :count point en :epreuve.|Il te manque :count points en :epreuve.', $m['ecart'], ['epreuve' => mb_strtolower($m['nom'])]) }}
                        @endif
                    @endif
                </p>

                <div class="flex flex-col gap-3 rounded-2xl bg-white px-3.5 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <div class="text-[13px] text-mousse">{{ __('Ton objectif') }}</div>
                            <div class="text-[17px] font-extrabold" dir="ltr">NCLC {{ $cible }}</div>
                        </div>
                        <button type="button" wire:click="$toggle('modifier')" aria-expanded="{{ $modifier ? 'true' : 'false' }}" aria-controls="choix-objectif"
                            class="min-h-11 rounded-xl border-[1.5px] border-ligne bg-white px-3.5 text-sm font-bold text-foret hover:border-foret">
                            {{ $modifier ? __('Fermer') : __('Modifier') }}
                        </button>
                    </div>
                    @if ($modifier)
                        <div id="choix-objectif" role="group" aria-label="{{ __('Choisir ton NCLC cible') }}" class="grid grid-cols-6 gap-1.5">
                            @foreach (\App\Support\Nclc::niveaux() as $niveau)
                                <button type="button" wire:click="choisir('{{ $niveau }}')" aria-pressed="{{ $niveau === $cible ? 'true' : 'false' }}" aria-label="NCLC {{ $niveau }}" @class([
                                    'h-11 rounded-xl text-[15px]',
                                    'bg-foret font-extrabold text-white' => $niveau === $cible,
                                    'border-[1.5px] border-ligne bg-white font-bold text-foret hover:border-foret' => $niveau !== $cible,
                                ])>{{ $niveau }}</button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Par épreuve --}}
        <section class="flex flex-col gap-3 px-5 pt-7 pb-2">
            <h2 class="font-titre text-[22px] tracking-[-0.5px]">{{ __('Par épreuve') }}</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($resultats as $r)
                    <div wire:key="resultat-{{ $r['code'] }}" class="flex flex-col gap-2.5 rounded-[18px] border-[1.5px] border-ligne px-4 py-3.5">
                        <div class="flex items-center justify-between gap-2.5">
                            <div class="text-base font-bold">{{ $r['nom'] }}</div>
                            @if ($r['atteint'])
                                <div class="flex shrink-0 items-center gap-1 rounded-full bg-brume px-2.5 py-1 text-[13px] font-extrabold">
                                    <x-icone nom="coche" :epaisseur="3" class="size-3.5 text-vert" /><span dir="ltr">NCLC {{ $r['niveau'] }}</span>
                                </div>
                            @else
                                <div class="shrink-0 rounded-full bg-peche px-2.5 py-1 text-[13px] font-extrabold text-foret">{{ $r['niveau'] ? 'NCLC '.$r['niveau'] : __('Sous le NCLC 5') }}</div>
                            @endif
                        </div>
                        <div class="flex items-baseline gap-1 self-start" dir="ltr">
                            <div class="font-titre text-2xl">{{ $r['score'] }}</div>
                            <div class="text-sm text-mousse">/ {{ $r['maximum'] }}</div>
                        </div>
                        <div class="relative h-2.5 rounded-full bg-brume">
                            <div @class(['h-2.5 rounded-full transition-[width] duration-300', 'bg-vert' => $r['atteint'], 'bg-peche' => ! $r['atteint']]) style="width: {{ $r['barre'] }}%"></div>
                            <div class="absolute -top-1 h-[18px] w-0.5 bg-foret transition-[inset-inline-start] duration-300" style="inset-inline-start: {{ $r['trait'] }}%" aria-hidden="true"></div>
                        </div>
                        @if ($r['atteint'])
                            <div class="text-[13px] font-medium text-mousse">{{ __('Au-dessus du minimum de :minimum', ['minimum' => $r['minimum']]) }}</div>
                        @else
                            <div class="text-[13px] font-bold">{{ trans_choice('Encore :count point pour le NCLC :niveau|Encore :count points pour le NCLC :niveau', $r['ecart'], ['niveau' => $cible]) }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="flex items-center gap-2 text-[13px] text-mousse"><div class="h-3.5 w-0.5 bg-foret"></div>{{ __('Le trait marque le minimum pour le NCLC :niveau.', ['niveau' => $cible]) }}</div>
        </section>

        {{-- Priorité --}}
        <section class="px-5 pt-5 pb-2">
            <div class="flex flex-col gap-3 rounded-[20px] bg-peche-clair p-[18px]">
                <div class="text-[13px] font-extrabold tracking-[0.8px] uppercase">{{ $priorite['atteint'] ? __('Pour aller plus loin') : __('À travailler en priorité') }}</div>
                <div class="font-titre text-xl tracking-[-0.5px]">{{ $priorite['nom'] }}</div>
                <p class="text-[15px] leading-normal">
                    {{ __($this::CONSEILS[$priorite['code']] ?? ($priorite['atteint']
                        ? "C'est l'épreuve où ta marge est la plus faible. Quelques quiz de plus pour la consolider."
                        : "C'est l'épreuve où tu es le plus loin de ton objectif. Des quiz ciblés t'aideront à gagner des points.")) }}
                </p>
                <a href="{{ isset($this->epreuves[$priorite['code']]) ? route('quiz', $this->epreuves[$priorite['code']]) : route('epreuves') }}" wire:navigate
                    class="flex h-[52px] items-center justify-center rounded-2xl bg-foret text-base font-bold text-white hover:bg-vert hover:text-white">
                    {{ __("Travailler l':epreuve", ['epreuve' => mb_strtolower($priorite['nom'])]) }}
                </a>
            </div>
        </section>

        <section class="flex flex-col gap-2.5 px-5 pt-5 pb-4">
            <a href="#" class="flex h-[52px] items-center justify-center rounded-2xl border-[1.5px] border-foret text-base font-bold text-foret">{{ __('Revoir mes réponses') }}</a>
            <a href="{{ route('tests-blancs') }}" wire:navigate class="flex h-[52px] items-center justify-center rounded-2xl bg-brume text-base font-bold text-foret hover:bg-menthe hover:text-foret">{{ __('Refaire un test blanc') }}</a>
        </section>

        <p class="mt-auto px-5 pt-3 pb-8 text-[13px] leading-normal text-mousse">{{ __('Estimation d\'entraînement, non officielle. Seul le TCF passé dans un centre agréé donne un résultat officiel.') }}</p>
    </main>
</div>
