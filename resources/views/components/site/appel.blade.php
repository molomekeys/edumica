{{-- Appel final pêche, en bas de page. --}}
@props(['titre' => null, 'lien' => null, 'libelle' => null, 'lienSecondaire' => null, 'libelleSecondaire' => null, 'arcs' => false])

<section class="px-3 pb-8 md:px-10 md:pb-16">
    <div class="relative isolate mx-auto flex max-w-[1360px] flex-col gap-4 overflow-hidden rounded-[28px] bg-peche px-5 py-7 md:flex-row md:items-center md:justify-between md:gap-10 md:rounded-[36px] md:px-14 md:py-14 xl:px-20">
        @if ($arcs)
            <x-arcs couleur="#0B2E1C" class="-top-[160px] left-1/2 hidden size-[420px] md:block" />
        @endif
        <h2 class="font-titre text-[26px] leading-[1.1] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ $titre ?? __('Ton premier quiz prend cinq minutes.') }}</h2>
        <div class="flex shrink-0 flex-col gap-2.5 sm:flex-row">
            @if ($lienSecondaire)
                <a href="{{ $lienSecondaire }}" wire:navigate class="flex h-14 items-center justify-center rounded-2xl border-[1.5px] border-foret px-7 text-[17px] font-bold text-foret transition-colors duration-300 hover:bg-white/40 hover:text-foret md:h-[58px] md:text-lg">{{ $libelleSecondaire }}</a>
            @endif
            <a href="{{ $lien ?? route('quiz', 'comprehension-orale') }}" wire:navigate class="reflet group flex h-14 shrink-0 items-center justify-center gap-2 rounded-2xl bg-foret px-8 text-[17px] font-bold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-vert hover:text-white hover:shadow-[0_14px_30px_rgba(11,46,28,0.3)] md:h-[58px] md:text-lg">
                {{ $libelle ?? __('Faire un quiz gratuit') }}
                <x-icone nom="droite" :epaisseur="2.4" class="size-5 transition-transform duration-300 group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
            </a>
        </div>
    </div>
</section>
