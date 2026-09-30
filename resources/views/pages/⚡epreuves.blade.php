<?php

use App\Models\Epreuve;
use App\Support\GuideEpreuve;
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
        return $this->view()->title(__('Les 4 épreuves du TCF'));
    }
};
?>

<x-site.page>
    <x-site.hero :titre="__('Les 4 épreuves')" :badge="__('TCF Canada · TCF Tout public')">
        <x-slot:heading>{!! __('Les :epreuves du TCF, une par une.', ['epreuves' => '<span class="text-vert">'.__('4 épreuves').'</span>']) !!}</x-slot:heading>
        <x-slot:intro>{{ __("Deux épreuves pour comprendre, deux pour t'exprimer. Découvre comment chacune se déroule, ce qu'elle évalue et comment t'y préparer.") }}</x-slot:intro>
        <x-slot:actions>
            <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="flex h-14 items-center justify-center rounded-2xl bg-foret px-[30px] text-[17px] font-bold text-white hover:bg-vert hover:text-white md:h-[58px] md:text-lg">{{ __('Faire un quiz gratuit') }}</a>
        </x-slot:actions>
        <x-slot:aside class="flex flex-col gap-2 rounded-[20px] bg-white p-5 md:rounded-[28px] md:p-7">
            <div class="text-[13px] font-extrabold tracking-[0.8px] text-mousse uppercase">{{ __('Le jour J') }}</div>
            <div class="font-titre text-[34px] leading-none md:text-[44px]">{{ __('2 h 47') }}</div>
            <p class="text-[15px] text-lichen">{{ __('d\'épreuves au TCF Canada, dans cet ordre :') }}</p>
            <ol class="mt-2 flex flex-col gap-2">
                @foreach ($this->epreuves as $epreuve)
                    <li wire:key="jour-{{ $epreuve->id }}" class="flex items-center gap-3">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brume text-sm font-extrabold">{{ $loop->iteration }}</div>
                        <div class="flex-1 text-[15px] font-semibold">{{ __($epreuve->nom) }}</div>
                        <div class="text-sm font-bold text-vert tabular-nums">{{ GuideEpreuve::duree($epreuve->code) }}</div>
                    </li>
                @endforeach
            </ol>
        </x-slot:aside>
    </x-site.hero>

    {{-- Les épreuves --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-4 px-5 py-10 md:gap-9 md:px-10 md:py-24 xl:px-0">
        <div class="flex flex-col gap-2.5">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Choisis une épreuve') }}</h2>
            <p class="text-base text-mousse md:text-lg">{{ __('Chaque page détaille le déroulé, les critères et nos conseils.') }}</p>
        </div>

        <div class="grid gap-3.5 md:gap-5 lg:grid-cols-2">
            @foreach ($this->epreuves as $epreuve)
                @php($guide = GuideEpreuve::pour($epreuve->code))
                <article wire:key="epreuve-{{ $epreuve->id }}" class="flex flex-col gap-4 rounded-[20px] bg-brume p-5 md:gap-5 md:rounded-[28px] md:p-8">
                    <div class="flex items-center gap-3.5 md:gap-5">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-vert text-white md:size-16">
                            <x-icone :nom="$epreuve->icone" class="size-[22px] md:size-7" />
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <h3 class="text-[19px] font-bold md:text-2xl">{{ __($epreuve->nom) }}</h3>
                            <div class="text-sm font-bold text-mousse md:text-[15px]">{{ __($epreuve->format) }}</div>
                        </div>
                    </div>
                    <p class="text-[15px] leading-normal text-mousse md:text-base md:leading-relaxed">{{ $guide['accroche'] }}</p>
                    <ul class="flex flex-col gap-2 text-[15px]">
                        @foreach (array_slice($guide['evalue'], 0, 3) as $point)
                            <li class="flex gap-2.5"><x-icone nom="coche" :epaisseur="2.4" class="mt-0.5 size-[18px] shrink-0 text-vert" />{{ $point }}</li>
                        @endforeach
                    </ul>
                    <div class="mt-auto flex flex-col gap-2.5 pt-1 sm:flex-row">
                        <a href="{{ route('epreuve', $epreuve) }}" wire:navigate class="flex h-[52px] flex-1 items-center justify-center rounded-[14px] bg-foret px-5 text-base font-bold text-white hover:bg-vert hover:text-white">{{ __('Découvrir l\'épreuve') }}</a>
                        @if ($epreuve->questions_count)
                            <a href="{{ route('quiz', $epreuve) }}" wire:navigate class="flex h-[52px] flex-1 items-center justify-center rounded-[14px] border-[1.5px] border-foret bg-white px-5 text-base font-bold text-foret hover:bg-menthe hover:text-foret">{{ __('Faire un quiz') }}</a>
                        @else
                            <div class="flex h-[52px] flex-1 items-center justify-center rounded-[14px] border-[1.5px] border-dashed border-ligne px-5 text-[15px] font-semibold text-mousse">{{ __('Quiz bientôt disponibles') }}</div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- TCF Canada ou Tout public --}}
    <section class="bg-brume">
        <div class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
            <div class="flex flex-col gap-2.5 md:items-center md:text-center">
                <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('TCF Canada ou TCF Tout public ?') }}</h2>
                <p class="max-w-[640px] text-base leading-normal text-mousse md:text-lg">{{ __('Les épreuves se ressemblent, mais l\'usage et la lecture des résultats changent.') }}</p>
            </div>
            <div class="grid gap-3.5 md:grid-cols-2 md:gap-5">
                @foreach ([
                    ['TCF Canada', __('Immigration au Canada'), __('Exigé par IRCC pour la résidence permanente et la citoyenneté.'), [__('Les 4 épreuves sont obligatoires'), __('Résultats lus en NCLC, épreuve par épreuve'), __('Valable 2 ans pour IRCC')], true],
                    ['TCF Tout public', __('Études, travail, projet personnel'), __('Reconnu par des universités, des employeurs et des administrations.'), [__('Compréhensions obligatoires, expressions selon ton besoin'), __('Résultats lus en niveaux CECRL, de A1 à C2'), __("Vérifie ce que demande l'organisme")], false],
                ] as [$nom, $usage, $texte, $points, $vedette])
                    <div @class(['flex flex-col gap-3.5 rounded-[20px] p-5 md:gap-[18px] md:rounded-[28px] md:p-9', 'bg-vert text-white' => $vedette, 'bg-white' => ! $vedette])>
                        <div @class(['self-start rounded-full px-3 py-1.5 text-[13px] font-bold', 'bg-peche text-foret' => $vedette, 'bg-brume' => ! $vedette])>{{ $usage }}</div>
                        <h3 class="font-titre text-[26px] leading-none md:text-4xl" dir="ltr">{{ $nom }}</h3>
                        <p @class(['text-[15px] leading-normal md:text-base', 'text-vert-pale' => $vedette, 'text-mousse' => ! $vedette])>{{ $texte }}</p>
                        <ul class="flex flex-col gap-2.5 text-[15px] md:text-base">
                            @foreach ($points as $point)
                                <li class="flex gap-2.5"><x-icone nom="coche" :epaisseur="2.4" class="mt-0.5 size-[18px] shrink-0 {{ $vedette ? 'text-peche' : 'text-vert' }}" />{{ $point }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            <p class="text-[13px] leading-normal text-mousse md:text-center md:text-sm">{{ __('Le format peut évoluer : vérifie toujours les modalités auprès de ton centre d\'examen.') }}</p>
        </div>
    </section>

    {{-- Méthode --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 lg:flex-row lg:items-start lg:gap-20 xl:px-0">
        <div class="flex flex-col gap-4 lg:w-[420px] lg:shrink-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Par où commencer ?') }}</h2>
            <p class="text-base leading-normal text-mousse md:text-lg md:leading-relaxed">{{ __('Pas besoin de tout travailler en même temps. Une méthode simple, en trois temps.') }}</p>
        </div>
        <ol class="flex flex-1 flex-col gap-3">
            @foreach ([
                [__('Fais un quiz par épreuve'), __("Tu repères vite où tu es à l'aise et où tu perds des points.")],
                [__("Concentre-toi sur l'épreuve la plus faible"), __("C'est là que chaque heure de travail rapporte le plus pour ton NCLC.")],
                [__('Passe un test blanc'), __("Dans les conditions de l'examen, pour mesurer tes progrès et ajuster ton objectif.")],
            ] as $i => [$titre, $texte])
                <li class="flex gap-3.5 rounded-[20px] border-[1.5px] border-ligne p-4 md:gap-5 md:rounded-3xl md:p-6">
                    <div @class(['flex size-9 shrink-0 items-center justify-center rounded-full font-extrabold md:size-12 md:font-titre md:text-xl', 'bg-vert text-white' => $i < 2, 'bg-peche text-foret' => $i === 2])>{{ $i + 1 }}</div>
                    <div class="flex flex-col gap-1 pt-1 md:pt-2">
                        <div class="text-[17px] font-bold md:text-xl">{{ $titre }}</div>
                        <p class="text-[15px] leading-normal text-mousse md:text-base">{{ $texte }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    <x-site.appel />
</x-site.page>
