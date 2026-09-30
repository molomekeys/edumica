<?php

use App\Support\Faq;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view(['faq' => Faq::theme('Compte et paiement')])->title(__('Tarifs'));
    }
};
?>

<x-site.page>
    <x-site.hero :titre="__('Tarifs')" :badge="__('Sans abonnement')">
        <x-slot:heading>{!! __('Commence :gratuitement, paie seulement le test blanc.', ['gratuitement' => '<span class="text-vert">'.__('gratuitement').'</span>']) !!}</x-slot:heading>
        <x-slot:intro>{{ __('Les quiz restent gratuits, sans carte bancaire. Quand tu veux connaître ton niveau, tu passes un test blanc complet.') }}</x-slot:intro>
        <x-slot:aside class="flex flex-col gap-3 rounded-[20px] bg-white p-5 md:rounded-[28px] md:p-7">
            @foreach ([
                ['valide', __("Pas d'abonnement"), __('Tu paies un test blanc quand tu en as besoin.')],
                ['coche', __('Quiz illimités'), __('Sur les 4 épreuves, gratuitement.')],
                ['telephone', __('Partout'), __('Sur ordinateur et sur téléphone.')],
            ] as [$icone, $titre, $texte])
                <div class="flex items-center gap-3.5">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-[14px] bg-brume text-vert"><x-icone :nom="$icone" class="size-[22px]" /></div>
                    <div class="flex flex-col">
                        <div class="text-base font-bold">{{ $titre }}</div>
                        <div class="text-sm text-mousse">{{ $texte }}</div>
                    </div>
                </div>
            @endforeach
        </x-slot:aside>
    </x-site.hero>

    {{-- Offres --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-3.5 px-5 py-10 md:gap-9 md:px-10 md:py-24 xl:px-0">
        <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-center md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Deux façons de te préparer') }}</h2>
        <x-site.tarifs class="xl:px-[100px]" />
    </section>

    {{-- Comparatif --}}
    <section class="bg-brume">
        <div class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-center md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Ce qui est inclus') }}</h2>
            <div class="overflow-hidden rounded-[20px] bg-white md:rounded-3xl xl:mx-[100px]">
                <table class="w-full border-collapse text-start text-[15px] md:text-base">
                    <thead>
                        <tr>
                            <th scope="col" class="px-3.5 py-4 md:px-7 md:py-5"><span class="sr-only">{{ __('Fonctionnalité') }}</span></th>
                            <th scope="col" class="px-3.5 py-4 text-center font-bold md:px-7 md:py-5">{{ __('Quiz') }}<span class="block text-[13px] font-semibold text-mousse">{{ __('Gratuit') }}</span></th>
                            <th scope="col" class="px-3.5 py-4 text-center font-bold text-vert md:px-7 md:py-5">{{ __('Test blanc') }}<span class="block text-[13px] font-semibold text-mousse">[PRIX]</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            ["Questions au format de l'examen", true, true],
                            ['Les 4 épreuves', true, true],
                            ['Correction et explication de chaque réponse', true, true],
                            ["Chrono de l'examen, épreuve par épreuve", false, true],
                            ["Enchaînement des 4 épreuves dans l'ordre", false, true],
                            ['Niveau estimé CECRL et NCLC', false, true],
                            ['Écart avec ton NCLC cible', false, true],
                            ['Épreuve prioritaire et quiz conseillés', false, true],
                        ] as [$fonction, $quiz, $test])
                            <tr class="border-t-[1.5px] border-ligne">
                                <th scope="row" class="px-3.5 py-3.5 font-semibold md:px-7 md:py-4">{{ __($fonction) }}</th>
                                @foreach ([$quiz, $test] as $inclus)
                                    <td class="px-3.5 py-3.5 text-center md:px-7 md:py-4">
                                        @if ($inclus)
                                            <x-icone nom="coche" :epaisseur="2.6" class="inline size-5 text-vert" /><span class="sr-only">{{ __('Inclus') }}</span>
                                        @else
                                            <span aria-hidden="true" class="text-mousse">—</span><span class="sr-only">{{ __('Non inclus') }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Pour qui --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
        <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Laquelle choisir ?') }}</h2>
        <div class="grid gap-3.5 md:grid-cols-3 md:gap-5">
            @foreach ([
                [__('Tu débutes ta préparation'), __('Commence par les quiz pour découvrir le format et repérer tes points faibles.'), __('Quiz gratuits'), route('epreuves'), false],
                [__('Ton examen approche'), __('Passe un test blanc pour savoir où tu en es et sur quelle épreuve concentrer tes efforts.'), __('Test blanc'), route('tests-blancs'), true],
                [__('Tu vises un NCLC précis'), __('Vérifie les scores minimums, puis mesure ton écart avec un test blanc.'), __('Scores NCLC'), route('scores'), false],
            ] as [$titre, $texte, $lien, $url, $vedette])
                <a href="{{ $url }}" wire:navigate @class([
                    'group flex flex-col gap-3 rounded-[20px] p-5 transition-colors md:gap-4 md:rounded-3xl md:p-8',
                    'bg-peche-clair text-foret hover:bg-peche hover:text-foret' => $vedette,
                    'border-[1.5px] border-ligne text-foret hover:border-vert hover:text-foret' => ! $vedette,
                ])>
                    <h3 class="text-[17px] font-bold md:text-[22px]">{{ $titre }}</h3>
                    <p class="text-[15px] leading-normal text-mousse-fonce md:text-base md:leading-relaxed">{{ $texte }}</p>
                    <div class="mt-auto font-bold text-vert">{{ $lien }} <span aria-hidden="true" class="inline-block transition-transform group-hover:translate-x-0.5 rtl:-scale-x-100 rtl:group-hover:-translate-x-0.5">→</span></div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Questions --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-2.5 px-5 pb-10 md:px-10 md:pb-24 lg:flex-row lg:gap-20 xl:px-0">
        <div class="mb-1 flex flex-col gap-4 lg:w-[360px] lg:shrink-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Paiement et compte') }}</h2>
            <p class="text-base leading-normal text-mousse">{{ __('Une autre question ?') }} <a href="{{ route('contact') }}" wire:navigate class="font-bold text-foret underline decoration-2 underline-offset-4">{{ __('Écris-nous') }}</a>.</p>
        </div>
        <x-site.faq :questions="$faq" prefixe="faq-tarifs" class="flex-1" />
    </section>

    <x-site.appel />
</x-site.page>
