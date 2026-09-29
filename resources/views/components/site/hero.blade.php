{{-- Grand aplat menthe en tête de page. $fil : [url => libellé] entre l'accueil et la page courante. --}}
@props(['titre', 'badge' => null, 'fil' => []])

<section class="px-3 pt-1 md:px-10 md:pt-2">
    <div class="relative isolate mx-auto flex max-w-[1360px] flex-col gap-6 overflow-hidden rounded-[28px] bg-menthe px-5 pt-6 pb-7 md:rounded-[36px] md:p-14 lg:flex-row lg:items-center lg:gap-16 xl:px-20 xl:py-16">
        <x-arcs class="-top-[120px] -right-[120px] size-[300px] lg:hidden" />
        <x-arcs class="-bottom-[260px] -left-[200px] hidden size-[560px] lg:block" />

        <div class="flex flex-col gap-4 md:gap-5 lg:flex-1">
            <nav aria-label="Fil d'Ariane">
                <ol class="flex flex-wrap items-center gap-1.5 text-[13px] font-semibold text-mousse-fonce md:text-sm">
                    <li><a href="{{ route('accueil') }}" wire:navigate class="text-mousse-fonce">Accueil</a></li>
                    @foreach ($fil as $url => $libelle)
                        <li aria-hidden="true">/</li>
                        <li><a href="{{ $url }}" wire:navigate class="text-mousse-fonce">{{ $libelle }}</a></li>
                    @endforeach
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="text-foret">{{ strip_tags($titre) }}</li>
                </ol>
            </nav>

            @if ($badge)
                <div class="self-start rounded-full bg-foret/8 px-3 py-1.5 text-[13px] font-semibold md:px-3.5 md:py-[7px] md:text-sm">{{ $badge }}</div>
            @endif

            <h1 class="font-titre text-[32px] leading-[1.05] tracking-[-1px] md:text-[56px] md:leading-[1.02] md:tracking-[-2px]">
                {{ $heading ?? $titre }}
            </h1>

            @isset($intro)
                <p class="max-w-[600px] text-[17px] leading-normal text-mousse-fonce md:text-xl md:leading-[1.55]">{{ $intro }}</p>
            @endisset

            @isset($actions)
                <div class="flex flex-col gap-2.5 md:flex-row md:items-center md:gap-3">{{ $actions }}</div>
            @endisset
        </div>

        @isset($aside)
            <div {{ $aside->attributes->merge(['class' => 'lg:w-[440px] lg:shrink-0']) }}>{{ $aside }}</div>
        @endisset
    </div>
</section>
