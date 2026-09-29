<footer class="border-t-[1.5px] border-ligne text-sm text-mousse md:text-[15px]">
    <div class="mx-auto flex max-w-[1200px] flex-col gap-3.5 px-5 pt-6 pb-10 md:gap-10 md:px-10 md:pt-14 xl:px-0">
        <div class="grid gap-10 md:grid-cols-4">
            <div class="flex flex-col gap-3.5">
                <div class="font-titre text-lg text-foret md:text-[22px]">edumica</div>
                <p class="leading-relaxed md:hidden">Préparation indépendante au TCF, non affiliée à France Éducation international. Tests d'entraînement non officiels.</p>
                <p class="hidden leading-relaxed md:block">Préparation indépendante au TCF Canada et au TCF Tout public.</p>
            </div>

            @foreach ([
                'Épreuves' => [
                    route('epreuve', 'comprehension-orale') => 'Compréhension orale',
                    route('epreuve', 'comprehension-ecrite') => 'Compréhension écrite',
                    route('epreuve', 'expression-ecrite') => 'Expression écrite',
                    route('epreuve', 'expression-orale') => 'Expression orale',
                ],
                'Edumica' => [
                    route('tests-blancs') => 'Tests blancs',
                    route('scores') => 'Scores NCLC',
                    route('tarifs') => 'Tarifs',
                    route('faq') => 'Questions fréquentes',
                    route('contact') => 'Contact',
                ],
                'Informations' => [
                    route('mentions-legales') => 'Mentions légales',
                    route('cgv') => 'CGV',
                    route('confidentialite') => 'Confidentialité',
                ],
            ] as $titre => $liens)
                <div class="hidden flex-col gap-1 md:flex">
                    <div class="mb-1.5 font-bold text-foret">{{ $titre }}</div>
                    @foreach ($liens as $url => $lien)
                        <a href="{{ $url }}" wire:navigate class="py-1.5 text-mousse">{{ $lien }}</a>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-x-5 font-semibold md:hidden">
            <a href="{{ route('contact') }}" wire:navigate class="py-3 text-foret">Contact</a>
            <a href="{{ route('mentions-legales') }}" wire:navigate class="py-3 text-foret">Mentions légales</a>
            <a href="{{ route('cgv') }}" wire:navigate class="py-3 text-foret">CGV</a>
            <a href="{{ route('confidentialite') }}" wire:navigate class="py-3 text-foret">Confidentialité</a>
        </div>

        <p class="hidden border-t-[1.5px] border-ligne pt-6 text-sm md:block">© Edumica · Non affiliée à France Éducation international. TCF est une marque de France Éducation international. Tests d'entraînement non officiels.</p>
    </div>
</footer>
