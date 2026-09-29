{{-- Les deux offres : quiz gratuits et test blanc complet. --}}
<div {{ $attributes->merge(['class' => 'grid gap-3.5 md:grid-cols-2 md:gap-6']) }}>
    <div class="flex flex-col gap-3 rounded-[20px] border-[1.5px] border-ligne p-5 md:gap-[18px] md:rounded-[28px] md:p-10">
        <div class="flex items-baseline justify-between md:flex-col md:gap-[18px]">
            <div class="text-[17px] font-bold md:text-lg">Quiz</div>
            <div class="font-titre text-[26px] md:text-[52px] md:leading-none md:tracking-[-1.5px]">Gratuit</div>
        </div>
        <p class="text-[15px] leading-normal text-mousse md:hidden">Les 4 épreuves · correction immédiate · sans carte bancaire</p>
        <ul class="hidden flex-col gap-2.5 text-base text-mousse md:flex">
            @foreach (['Quiz sur les 4 épreuves', 'Correction immédiate', 'Sans carte bancaire'] as $avantage)
                <li class="flex items-center gap-2.5"><x-icone nom="coche" :epaisseur="2.4" class="size-[18px] text-vert" />{{ $avantage }}</li>
            @endforeach
        </ul>
        <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="flex h-[52px] items-center justify-center rounded-[14px] bg-brume text-base font-bold text-foret hover:bg-menthe hover:text-foret md:mt-auto md:h-[54px]">Commencer gratuitement</a>
    </div>

    <div class="flex flex-col gap-3 rounded-[20px] bg-vert p-5 text-white md:gap-[18px] md:rounded-[28px] md:p-10">
        <div class="flex items-baseline justify-between md:flex-col md:gap-[18px]">
            <div class="flex w-full items-center justify-between">
                <div class="text-[17px] font-bold md:text-lg">Test blanc complet</div>
                <div class="hidden rounded-full bg-peche px-3 py-1.5 text-[13px] font-bold text-foret md:block">Conditions réelles</div>
            </div>
            <div class="shrink-0 font-titre text-[26px] md:text-[52px] md:leading-none md:tracking-[-1.5px]">[PRIX]</div>
        </div>
        <p class="text-[15px] leading-normal text-vert-pale md:hidden">4 épreuves chronométrées · niveau CECRL et NCLC · bilan détaillé</p>
        <ul class="hidden flex-col gap-2.5 text-base text-vert-pale md:flex">
            @foreach (['Les 4 épreuves, chronométrées', 'Niveau estimé CECRL et NCLC', 'Bilan détaillé et quiz conseillés'] as $avantage)
                <li class="flex items-center gap-2.5"><x-icone nom="coche" :epaisseur="2.4" class="size-[18px] text-peche" />{{ $avantage }}</li>
            @endforeach
        </ul>
        {{-- TODO : brancher le paiement du test blanc --}}
        <a href="#" class="flex h-[52px] items-center justify-center rounded-[14px] bg-peche text-base font-bold text-foret hover:bg-white hover:text-foret md:mt-auto md:h-[54px]">Passer un test blanc</a>
    </div>
</div>
