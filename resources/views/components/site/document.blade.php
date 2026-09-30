{{--
    Page de texte avec sommaire (mentions légales, CGV, confidentialité).
    $sections : liste de [titre, blocs], un bloc étant un paragraphe (chaîne) ou une liste (tableau de chaînes).
--}}
@props(['titre', 'intro', 'sections', 'miseAJour' => null])

<x-site.page>
    <x-site.hero :titre="$titre">
        <x-slot:intro>{{ $intro }}</x-slot:intro>
        <x-slot:actions>
            <div class="self-start rounded-full bg-foret/8 px-3 py-1.5 text-[13px] font-semibold md:px-3.5 md:py-[7px] md:text-sm">{{ __('Mise à jour : :date', ['date' => $miseAJour ?? __('[DATE DE MISE À JOUR]')]) }}</div>
        </x-slot:actions>
    </x-site.hero>

    <div class="mx-auto flex max-w-[1200px] flex-col gap-8 px-5 py-10 md:px-10 md:py-20 lg:flex-row lg:items-start lg:gap-20 xl:px-0">
        <nav aria-label="{{ __('Sommaire') }}" class="rounded-[20px] bg-brume p-5 lg:sticky lg:top-6 lg:w-[320px] lg:shrink-0 lg:rounded-3xl lg:p-7">
            <div class="mb-2 text-[13px] font-extrabold tracking-[0.8px] text-mousse uppercase">{{ __('Sommaire') }}</div>
            <ol class="flex flex-col">
                @foreach ($sections as $i => [$titreSection])
                    <li><a href="#section-{{ $i + 1 }}" class="flex min-h-11 items-center gap-3 text-[15px] font-semibold text-foret"><span class="w-5 text-mousse tabular-nums">{{ $i + 1 }}.</span>{{ __($titreSection) }}</a></li>
                @endforeach
            </ol>
        </nav>

        <article class="flex flex-1 flex-col gap-9 md:gap-12">
            @foreach ($sections as $i => [$titreSection, $blocs])
                <section id="section-{{ $i + 1 }}" class="flex scroll-mt-6 flex-col gap-3.5">
                    <h2 class="font-titre text-xl leading-[1.15] tracking-[-0.5px] md:text-[28px]"><span class="text-vert">{{ $i + 1 }}.</span> {{ __($titreSection) }}</h2>
                    @foreach ($blocs as $bloc)
                        @if (is_array($bloc))
                            <ul class="flex flex-col gap-2 text-[15px] leading-relaxed text-mousse md:text-base">
                                @foreach ($bloc as $element)
                                    <li class="flex gap-2.5"><span class="mt-[9px] size-1.5 shrink-0 rounded-full bg-vert md:mt-[10px]"></span>{{ __($element) }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-[15px] leading-relaxed text-mousse md:text-base">{{ __($bloc) }}</p>
                        @endif
                    @endforeach
                </section>
            @endforeach

            <div class="flex flex-col gap-3 rounded-[20px] bg-peche-clair p-5 md:flex-row md:items-center md:justify-between md:rounded-3xl md:p-7">
                <p class="text-[15px] leading-normal md:text-base">{{ __('Une question sur ce document ?') }}</p>
                <a href="{{ route('contact') }}" wire:navigate class="flex h-[52px] shrink-0 items-center justify-center rounded-2xl bg-foret px-6 text-base font-bold text-white hover:bg-vert hover:text-white">{{ __('Nous contacter') }}</a>
            </div>
        </article>
    </div>
</x-site.page>
