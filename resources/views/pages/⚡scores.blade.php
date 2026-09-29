<?php

use App\Support\GuideEpreuve;
use App\Support\Nclc;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'objectif', except: Nclc::CIBLE_PAR_DEFAUT)]
    public string $cible = Nclc::CIBLE_PAR_DEFAUT;

    public function mount(): void
    {
        if (! Nclc::existe($this->cible)) {
            $this->cible = Nclc::CIBLE_PAR_DEFAUT;
        }
    }

    #[On('cible-choisie')]
    public function surligner(string $niveau): void
    {
        if (Nclc::existe($niveau)) {
            $this->cible = $niveau;
        }
    }

    public function render()
    {
        return $this->view()->title('Scores NCLC du TCF Canada');
    }
};
?>

<x-site.page>
    <x-site.hero titre="Scores NCLC" badge="TCF Canada · barème IRCC">
        <x-slot:heading>Quel score pour quel <span class="text-vert">NCLC</span>&nbsp;?</x-slot:heading>
        <x-slot:intro>Choisis ton objectif : on te donne le score minimum à obtenir dans chaque épreuve du TCF Canada.</x-slot:intro>
        <x-slot:aside class="flex flex-col gap-3.5 rounded-[20px] bg-white p-4 md:rounded-[28px] md:p-7">
            <div class="text-[15px] font-bold md:text-[17px]">Ton NCLC cible</div>
            <livewire:nclc-cible :cible="$cible" />
        </x-slot:aside>
    </x-site.hero>

    {{-- Barème --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 lg:flex-row lg:items-start lg:gap-16 xl:px-0">
        <div class="flex flex-col gap-4 lg:w-[380px] lg:shrink-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Le barème complet</h2>
            <p class="text-base leading-normal text-mousse md:text-[17px] md:leading-relaxed">Correspondance entre les résultats du TCF Canada et les niveaux NCLC utilisés par IRCC. Ton objectif, le <span class="font-bold text-foret">NCLC {{ $cible }}</span>, est surligné.</p>
            <div class="flex items-center gap-2.5 text-[13px] text-mousse md:text-sm"><span class="size-4 rounded bg-peche-clair ring-[1.5px] ring-peche"></span>Ton objectif</div>
        </div>
        <x-bareme :cible="$cible" class="w-full flex-1" />
    </section>

    {{-- Lire son résultat --}}
    <section class="bg-brume">
        <div class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-center md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Comment lire ton résultat</h2>
            <div class="grid gap-3.5 md:grid-cols-3 md:gap-5">
                @foreach ([
                    ['jauge', 'Deux échelles de notes', "Les compréhensions sont notées de 100 à 699, les expressions de 0 à 20. Chaque score se convertit ensuite en NCLC."],
                    ['valide', 'Un NCLC par compétence', "IRCC regarde chaque compétence séparément. Si ton programme exige le NCLC 7, il le faut dans les 4 épreuves : ta note la plus faible compte."],
                    ['horloge', 'Valable deux ans', "Tes résultats doivent dater de moins de deux ans quand tu déposes ta demande. Planifie ton examen en conséquence."],
                ] as [$icone, $titre, $texte])
                    <div class="flex flex-col gap-3 rounded-[20px] bg-white p-5 md:gap-4 md:rounded-3xl md:p-8">
                        <div class="flex size-12 items-center justify-center rounded-[14px] bg-brume text-vert"><x-icone :nom="$icone" class="size-6" /></div>
                        <h3 class="text-[17px] font-bold md:text-[22px]">{{ $titre }}</h3>
                        <p class="text-[15px] leading-normal text-mousse md:text-base md:leading-relaxed">{{ $texte }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CECRL --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 lg:flex-row lg:items-start lg:gap-16 xl:px-0">
        <div class="flex flex-col gap-4 lg:w-[380px] lg:shrink-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Et en niveaux CECRL&nbsp;?</h2>
            <p class="text-base leading-normal text-mousse md:text-[17px] md:leading-relaxed">Le cadre européen, de A1 à C2, est utilisé par le TCF Tout public et par beaucoup d'écoles et d'employeurs.</p>
        </div>
        <div class="w-full flex-1 overflow-hidden rounded-[20px] border-[1.5px] border-ligne md:rounded-3xl">
            <table class="w-full border-collapse text-left text-[15px] md:text-base">
                <caption class="sr-only">Scores du TCF par niveau CECRL</caption>
                <thead>
                    <tr class="bg-brume">
                        <th scope="col" class="px-4 py-3.5 font-bold md:px-6 md:py-4">CECRL</th>
                        <th scope="col" class="px-4 py-3.5 font-bold md:px-6 md:py-4">Compréhensions<span class="block text-[13px] font-semibold text-mousse md:text-sm">sur 699</span></th>
                        <th scope="col" class="px-4 py-3.5 font-bold md:px-6 md:py-4">Expressions<span class="block text-[13px] font-semibold text-mousse md:text-sm">sur 20</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (GuideEpreuve::CECRL as $niveau => $plages)
                        <tr class="border-t-[1.5px] border-ligne">
                            <th scope="row" class="px-4 py-3 md:px-6 md:py-3.5">
                                <span @class(['inline-flex h-8 min-w-11 items-center justify-center rounded-lg px-2 font-extrabold', 'bg-vert text-white' => str_starts_with($niveau, 'B') || str_starts_with($niveau, 'C'), 'bg-brume' => str_starts_with($niveau, 'A')])>{{ $niveau }}</span>
                            </th>
                            <td class="px-4 py-3 tabular-nums md:px-6 md:py-3.5">{{ Nclc::plage($plages['co']) }}</td>
                            <td class="px-4 py-3 tabular-nums md:px-6 md:py-3.5">{{ Nclc::plage($plages['ee']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Quel NCLC viser --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 pb-10 md:gap-10 md:px-10 md:pb-24 xl:px-0">
        <div class="flex flex-col gap-2.5">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Quel NCLC viser&nbsp;?</h2>
            <p class="max-w-[720px] text-base leading-normal text-mousse md:text-lg">Quelques repères courants. Les exigences évoluent : vérifie toujours celles de ton programme sur le site d'IRCC.</p>
        </div>
        <div class="grid gap-3 md:grid-cols-2 md:gap-4">
            @foreach ([
                ['4', 'Citoyenneté canadienne', "Le niveau demandé en compréhension et en expression orales pour la demande de citoyenneté."],
                ['5', 'Expérience canadienne, emplois TEER 2 et 3', "Le minimum dans les 4 compétences pour la catégorie de l'expérience canadienne, selon ton emploi."],
                ['7', 'Travailleurs qualifiés (fédéral)', "Le minimum dans les 4 compétences pour ce programme d'Entrée express, et pour l'expérience canadienne en TEER 0 et 1."],
                ['9+', 'Plus de points au classement', "Au-delà du minimum, un NCLC élevé rapporte des points supplémentaires dans le système de classement global d'Entrée express."],
            ] as [$niveau, $titre, $texte])
                <div class="flex gap-4 rounded-[20px] border-[1.5px] border-ligne p-5 md:gap-5 md:rounded-3xl md:p-7">
                    <div @class(['flex size-14 shrink-0 flex-col items-center justify-center rounded-2xl leading-none md:size-16', 'bg-peche text-foret' => $niveau === $cible, 'bg-brume' => $niveau !== $cible])>
                        <span class="text-[11px] font-extrabold tracking-[0.5px] uppercase">NCLC</span>
                        <span class="font-titre text-xl md:text-2xl">{{ $niveau }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <h3 class="text-[17px] font-bold md:text-xl">{{ $titre }}</h3>
                        <p class="text-[15px] leading-normal text-mousse md:text-base">{{ $texte }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <x-site.appel titre="Découvre où tu en es par rapport à ton objectif." :lien="route('bilan', $cible === \App\Support\Nclc::CIBLE_PAR_DEFAUT ? [] : ['objectif' => $cible])" libelle="Voir un exemple de bilan" />
</x-site.page>
