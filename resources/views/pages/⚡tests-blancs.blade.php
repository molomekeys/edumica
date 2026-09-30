<?php

use App\Models\Epreuve;
use App\Support\Faq;
use App\Support\GuideEpreuve;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function epreuves()
    {
        return Epreuve::orderBy('ordre')->get();
    }

    public function render()
    {
        return $this->view(['faq' => Faq::theme('Les tests blancs')])->title(__('Tests blancs'));
    }
};
?>

<x-site.page>
    <x-site.hero :titre="__('Tests blancs')" :badge="__('Conditions réelles')">
        <x-slot:heading>{{ __('Un test blanc,') }} <span class="text-vert">{{ __('comme le jour J') }}</span>.</x-slot:heading>
        <x-slot:intro>{{ __("Les 4 épreuves dans l'ordre de l'examen, avec leur chrono. À la fin, ton niveau estimé en CECRL et en NCLC, épreuve par épreuve.") }}</x-slot:intro>
        <x-slot:actions>
            {{-- TODO : brancher le paiement du test blanc --}}
            <a href="#" class="flex h-14 items-center justify-center rounded-2xl bg-foret px-[30px] text-[17px] font-bold text-white hover:bg-vert hover:text-white md:h-[58px] md:text-lg">{{ __('Passer un test blanc · [PRIX]') }}</a>
            <a href="{{ route('bilan') }}" wire:navigate class="flex h-[52px] items-center justify-center rounded-2xl border-[1.5px] border-foret px-[26px] text-base font-bold text-foret hover:bg-foret/5 md:h-[58px] md:text-lg">{{ __('Voir un exemple de bilan') }}</a>
        </x-slot:actions>
        <x-slot:aside class="flex flex-col items-center gap-1 rounded-[20px] bg-white px-3 pt-[18px] pb-4 md:gap-2 md:rounded-[28px] md:px-6 md:pt-9 md:pb-7">
            <x-jauge class="h-auto w-[260px] md:w-[360px] lg:w-full lg:max-w-[380px]" />
            <div class="font-titre text-[32px] leading-none md:text-[40px]" dir="ltr">B2 · NCLC 7</div>
            <p class="text-sm text-lichen md:text-[15px]">{{ __('Exemple de niveau estimé après un test blanc') }}</p>
        </x-slot:aside>
    </x-site.hero>

    {{-- Déroulé --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
        <div class="flex flex-col gap-2.5">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Ce qui t\'attend') }}</h2>
            <p class="max-w-[720px] text-base leading-normal text-mousse md:text-lg">{{ __('Les épreuves s\'enchaînent dans l\'ordre de l\'examen. Chacune a son chrono, qui ne s\'arrête pas.') }}</p>
        </div>
        <ol class="grid gap-2.5 md:grid-cols-2 md:gap-4 xl:grid-cols-4">
            @foreach ($this->epreuves as $epreuve)
                <li wire:key="etape-{{ $epreuve->id }}" class="relative flex items-center gap-3.5 rounded-[18px] bg-brume p-4 md:flex-col md:items-start md:gap-4 md:rounded-[22px] md:p-6">
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-vert text-white md:size-14">
                        <x-icone :nom="$epreuve->icone" class="size-[22px] md:size-6" />
                    </div>
                    <div class="flex flex-1 flex-col gap-0.5 md:gap-1">
                        <div class="text-[13px] font-extrabold text-mousse">{{ __('Épreuve :numero', ['numero' => $loop->iteration]) }}</div>
                        <div class="text-[17px] font-bold md:text-xl">{{ __($epreuve->nom) }}</div>
                        <div class="text-sm text-mousse">{{ __($epreuve->format) }}</div>
                    </div>
                    <div class="font-titre text-xl text-vert md:mt-auto md:text-[32px] md:leading-none">{{ GuideEpreuve::duree($epreuve->code) }}</div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Conditions réelles --}}
    <section class="bg-brume">
        <div class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-center md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Les règles de l\'examen, sans surprise') }}</h2>
            <div class="grid gap-3.5 md:grid-cols-2 md:gap-5 xl:grid-cols-4">
                @foreach ([
                    ['chrono', __('Le vrai chrono'), __("35, 60, 60 et 12 minutes : les durées de l'examen, épreuve par épreuve.")],
                    ['casque', __('Une seule écoute'), __("En compréhension orale, chaque enregistrement ne s'écoute qu'une fois.")],
                    ['boussole', __("L'ordre officiel"), __("Compréhensions d'abord, expressions ensuite, comme le jour J.")],
                    ['telephone', __('Où tu veux'), __('Sur ordinateur ou sur téléphone. Prévois un endroit calme et un casque.')],
                ] as [$icone, $titre, $texte])
                    <div class="flex flex-col gap-3 rounded-[20px] bg-white p-5 md:gap-4 md:rounded-3xl md:p-7">
                        <div class="flex size-12 items-center justify-center rounded-[14px] bg-brume text-vert"><x-icone :nom="$icone" class="size-6" /></div>
                        <h3 class="text-[17px] font-bold md:text-xl">{{ $titre }}</h3>
                        <p class="text-[15px] leading-normal text-mousse md:text-base">{{ $texte }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Le bilan --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:px-10 md:py-24 lg:flex-row lg:items-center lg:gap-20 xl:px-0">
        <div class="flex flex-col gap-5 lg:w-[440px] lg:shrink-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Un bilan qui te dit quoi faire') }}</h2>
            <p class="text-base leading-relaxed text-mousse md:text-lg">{{ __('Pas seulement une note : un plan d\'action pour atteindre ton objectif.') }}</p>
            <ul class="flex flex-col gap-3">
                @foreach ([
                    ['jauge', __('Ton niveau par épreuve'), __('En CECRL et en NCLC, avec ton score détaillé.')],
                    ['valide', __("L'écart avec ton objectif"), __('Choisis ton NCLC cible : le bilan te dit combien de points il te manque.')],
                    ['boussole', __('Ta priorité'), __("L'épreuve à travailler en premier et les quiz conseillés pour progresser.")],
                ] as [$icone, $titre, $texte])
                    <li class="flex gap-3.5">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-[14px] bg-brume text-vert"><x-icone :nom="$icone" class="size-[22px]" /></div>
                        <div class="flex flex-col gap-0.5">
                            <div class="text-base font-bold md:text-[17px]">{{ $titre }}</div>
                            <p class="text-[15px] leading-normal text-mousse">{{ $texte }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <a href="{{ route('bilan') }}" wire:navigate class="flex flex-1 flex-col gap-3.5 rounded-[20px] border-[1.5px] border-ligne p-5 text-foret transition-colors hover:border-vert hover:text-foret md:gap-[18px] md:rounded-[28px] md:p-9">
            <div class="flex items-baseline justify-between">
                <div class="flex items-baseline gap-2.5 md:gap-3">
                    <div class="font-titre text-4xl leading-none md:text-[44px]">B2</div>
                    <div class="font-bold text-vert md:text-lg">NCLC 7</div>
                </div>
                <div class="text-[13px] text-mousse md:text-sm"><span class="md:hidden">{{ __('Exemple') }}</span><span class="hidden md:inline">{{ __('Exemple de résultat') }}</span></div>
            </div>
            @foreach ([
                [__('Compréhension orale'), 'B2', 66, false],
                [__('Compréhension écrite'), 'C1', 82, false],
                [__('Expression écrite'), 'B1', 48, true],
                [__('Expression orale'), 'B2', 64, false],
            ] as [$nom, $niveau, $pourcentage, $aTravailler])
                <div class="flex flex-col gap-1.5 md:flex-row md:items-center md:gap-4">
                    <div class="flex justify-between text-[15px] md:w-[200px] md:text-base md:font-semibold">
                        <span>{{ $nom }}</span>
                        <span class="font-bold md:hidden">{{ $niveau }}{{ $aTravailler ? ' · '.__('à travailler') : '' }}</span>
                    </div>
                    <div class="h-3 flex-1 rounded-md bg-brume md:h-6 md:rounded-lg">
                        <div @class(['h-full rounded-md md:rounded-lg', 'bg-peche' => $aTravailler, 'bg-vert' => ! $aTravailler]) style="width: {{ $pourcentage }}%"></div>
                    </div>
                    <div class="hidden w-9 text-end font-bold md:block">{{ $niveau }}</div>
                </div>
            @endforeach
            <div class="flex items-center justify-between border-t-[1.5px] border-ligne pt-3.5 text-[15px]">
                <span class="text-mousse">{{ __('À travailler :') }} <span class="font-bold text-foret">{{ __('expression écrite') }}</span></span>
                <span class="font-bold text-vert">{{ __('Voir le bilan') }} <span aria-hidden="true" class="inline-block rtl:-scale-x-100">→</span></span>
            </div>
        </a>
    </section>

    {{-- Quiz ou test blanc --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 pb-10 md:gap-10 md:px-10 md:pb-24 xl:px-0">
        <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-center md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Quiz ou test blanc ?') }}</h2>
        <div class="overflow-hidden rounded-[20px] border-[1.5px] border-ligne md:rounded-3xl">
            <table class="w-full border-collapse text-start text-[15px] md:text-base">
                <thead>
                    <tr class="bg-brume">
                        <th scope="col" class="px-3.5 py-3.5 font-bold md:px-6 md:py-4"><span class="sr-only">{{ __('Critère') }}</span></th>
                        <th scope="col" class="px-3.5 py-3.5 font-bold md:px-6 md:py-4">{{ __('Quiz') }}</th>
                        <th scope="col" class="px-3.5 py-3.5 font-bold text-vert md:px-6 md:py-4">{{ __('Test blanc') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ([
                        [__('Durée'), __('10 questions · quelques minutes'), __('Les 4 épreuves · environ 2 h 45')],
                        [__('Épreuves'), __('Une au choix'), __("Les 4, dans l'ordre")],
                        [__('Correction'), __('Après chaque question'), __('Dans ton bilan final')],
                        [__('Niveau CECRL et NCLC'), false, true],
                        [__('Objectif et priorités'), false, true],
                        [__('Prix'), __('Gratuit'), __('[PRIX]')],
                    ] as [$critere, $quiz, $test])
                        <tr class="border-t-[1.5px] border-ligne">
                            <th scope="row" class="px-3.5 py-3.5 font-bold md:px-6 md:py-4">{{ $critere }}</th>
                            @foreach ([$quiz, $test] as $valeur)
                                <td class="px-3.5 py-3.5 text-mousse md:px-6 md:py-4">
                                    @if ($valeur === true)
                                        <x-icone nom="coche" :epaisseur="2.6" class="size-5 text-vert" /><span class="sr-only">{{ __('Oui') }}</span>
                                    @elseif ($valeur === false)
                                        <span aria-hidden="true">—</span><span class="sr-only">{{ __('Non') }}</span>
                                    @else
                                        {{ $valeur }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-[15px] leading-normal text-mousse md:text-center">{{ __('Notre conseil : des quiz pour travailler chaque épreuve, un test blanc pour mesurer où tu en es.') }}</p>
    </section>

    {{-- Questions --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-2.5 px-5 pb-10 md:px-10 md:pb-24 lg:flex-row lg:gap-20 xl:px-0">
        <div class="mb-1 flex flex-col gap-4 lg:w-[360px] lg:shrink-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Tes questions') }}</h2>
            <a href="{{ route('faq') }}" wire:navigate class="self-start py-2 text-base font-bold text-foret underline decoration-2 underline-offset-4">{{ __('Toutes les questions') }}</a>
        </div>
        <x-site.faq :questions="$faq" prefixe="faq-test" class="flex-1" />
    </section>

    <x-site.appel :titre="__('Commence par un quiz gratuit, passe le test blanc ensuite.')" />
</x-site.page>
