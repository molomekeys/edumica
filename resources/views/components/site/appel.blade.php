{{-- Appel final pêche, en bas de page. --}}
@props(['titre' => 'Ton premier quiz prend cinq minutes.', 'lien' => null, 'libelle' => 'Faire un quiz gratuit'])

<section class="px-3 pb-8 md:px-10 md:pb-16">
    <div class="mx-auto flex max-w-[1360px] flex-col gap-4 rounded-[28px] bg-peche px-5 py-7 md:flex-row md:items-center md:justify-between md:gap-10 md:rounded-[36px] md:px-14 md:py-14 xl:px-20">
        <h2 class="font-titre text-[26px] leading-[1.1] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ $titre }}</h2>
        <a href="{{ $lien ?? route('quiz', 'comprehension-orale') }}" wire:navigate class="flex h-14 shrink-0 items-center justify-center rounded-2xl bg-foret px-8 text-[17px] font-bold text-white hover:bg-vert hover:text-white md:h-[58px] md:text-lg">{{ $libelle }}</a>
    </div>
</section>
