<?php

use App\Models\Epreuve;
use App\Support\Nclc;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function epreuves()
    {
        return Epreuve::orderBy('ordre')->get();
    }

    /** @return list<array{0: string, 1: string}> */
    public function faq(): array
    {
        return [
            ['Est-ce un test officiel ?', "Non. Seul le TCF passé dans un centre agréé donne un résultat officiel. Edumica sert à t'entraîner."],
            ['Mon niveau estimé est-il fiable ?', "C'est une estimation d'entraînement, calculée sur des épreuves au format de l'examen. Elle te donne une tendance solide pour savoir où tu en es, pas un résultat officiel."],
            ['Quelle différence entre TCF Canada et TCF Tout public ?', "Le TCF Canada est exigé par IRCC pour l'immigration au Canada, ses résultats se lisent en NCLC. Le TCF Tout public sert pour les études, le travail ou un projet personnel, et donne un niveau CECRL."],
            ["Combien de temps dure l'accès à un test blanc ?", '[DURÉE D\'ACCÈS À UN TEST BLANC]'],
            ['Faut-il créer un compte ?', '[RÉPONSE : QUIZ SANS COMPTE ? COMPTE POUR LES TESTS BLANCS ?]'],
        ];
    }

    public function render()
    {
        return $this->view(['faq' => $this->faq(), 'bareme' => Nclc::BAREME, 'cible' => Nclc::CIBLE_PAR_DEFAUT]);
    }
};
?>

<div class="flex min-h-dvh flex-col">
    <x-site.header />

    <main class="flex-1">
        {{-- Accueil --}}
        <section class="px-3 pt-1 md:px-10 md:pt-2">
            <div class="relative isolate mx-auto flex max-w-[1360px] flex-col gap-4 overflow-hidden rounded-[28px] bg-menthe px-5 pt-7 pb-6 md:rounded-[36px] md:p-14 lg:flex-row lg:items-center lg:gap-16 xl:px-20 xl:py-[72px]">
                <x-arcs class="-top-[120px] -right-[120px] size-[300px] lg:hidden" />
                <x-arcs class="-bottom-[260px] -left-[200px] hidden size-[560px] lg:block" />

                <div class="flex flex-col gap-4 md:gap-6 lg:w-[620px] lg:shrink-0">
                    <div class="self-start rounded-full bg-foret/8 px-3 py-1.5 text-[13px] font-semibold md:px-3.5 md:py-[7px] md:text-sm">TCF Canada · TCF Tout public</div>
                    <h1 class="font-titre text-[34px] leading-[1.05] tracking-[-1px] md:text-[64px] md:leading-[1.02] md:tracking-[-2px]">
                        Sache ton <span class="text-vert">niveau</span> avant le jour&nbsp;J.
                    </h1>
                    <p class="max-w-[540px] text-[17px] leading-normal text-mousse-fonce md:text-xl md:leading-[1.55]">Quiz gratuits pour t'entraîner, tests blancs chronométrés pour un niveau estimé CECRL et NCLC.</p>
                    <div class="flex flex-col gap-2.5 md:flex-row md:items-center md:gap-3">
                        <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="flex h-14 items-center justify-center rounded-2xl bg-foret px-[30px] text-[17px] font-bold text-white hover:bg-vert hover:text-white md:h-[58px] md:text-lg">Faire un quiz gratuit</a>
                        <a href="#epreuves" class="flex h-[52px] items-center justify-center gap-2 rounded-2xl border-[1.5px] border-foret px-[26px] text-base font-bold text-foret hover:bg-foret/5 md:h-[58px] md:gap-2.5 md:text-lg">
                            <x-icone nom="boussole" class="size-[18px] md:size-5" />Explorer les épreuves
                        </a>
                    </div>
                    <p class="text-center text-[13px] text-mousse-fonce md:text-left md:text-sm">Gratuit · corrigé et expliqué · sans carte<span class="hidden md:inline"> bancaire</span></p>
                </div>

                <div class="flex flex-col items-center gap-1 rounded-[20px] bg-white px-3 pt-[18px] pb-4 md:gap-2 md:rounded-[28px] md:px-6 md:pt-9 md:pb-7 lg:flex-1">
                    <x-jauge class="h-auto w-[280px] md:w-[420px] lg:w-full lg:max-w-[420px]" />
                    <p class="text-sm font-semibold text-lichen md:hidden">Exemple : <span class="font-bold text-foret">B2 · NCLC 7</span> après un test blanc</p>
                    <div class="hidden font-titre text-[44px] leading-none md:block">B2 · NCLC 7</div>
                    <p class="hidden text-[15px] text-lichen md:block">Exemple de niveau estimé après un test blanc</p>
                </div>
            </div>
        </section>

        {{-- Points forts --}}
        <section class="mx-auto hidden max-w-[1200px] px-10 pt-10 md:block xl:px-0">
            <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                @foreach ([
                    ['chrono', "Au format de l'examen", 'Les 4 épreuves, avec leurs durées'],
                    ['valide', 'Correction immédiate', 'Chaque réponse expliquée'],
                    ['jauge', 'Niveau CECRL et NCLC', 'Estimé après chaque test blanc'],
                    ['telephone', 'Mobile et ordinateur', "Entraîne-toi où que tu sois"],
                ] as [$icone, $titre, $texte])
                    <div class="flex items-center gap-3.5 rounded-[20px] border-[1.5px] border-ligne p-5">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-[14px] bg-brume text-vert"><x-icone :nom="$icone" class="size-6" /></div>
                        <div class="flex flex-col gap-0.5">
                            <div class="text-base font-bold">{{ $titre }}</div>
                            <div class="text-sm text-mousse">{{ $texte }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Épreuves --}}
        <section id="epreuves" class="mx-auto flex max-w-[1200px] scroll-mt-4 flex-col gap-4 px-5 py-10 md:gap-9 md:px-10 md:py-24 xl:px-0">
            <div class="flex items-end justify-between gap-6">
                <div class="flex flex-col gap-2.5">
                    <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Choisis une épreuve</h2>
                    <p class="hidden text-lg text-mousse md:block">Des quiz gratuits au format de l'examen, corrigés et expliqués.</p>
                </div>
                <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="hidden shrink-0 border-b-2 border-foret py-3 text-base font-bold text-foret md:block">Explorer tous les quiz</a>
            </div>

            <div class="grid gap-2.5 md:gap-4 lg:grid-cols-2">
                @foreach ($this->epreuves as $epreuve)
                    <a href="{{ route('quiz', $epreuve) }}" wire:navigate wire:key="epreuve-{{ $epreuve->id }}"
                        class="group flex min-h-[76px] items-center gap-3.5 rounded-[18px] bg-brume py-3 pr-4 pl-3 text-foret transition-colors hover:bg-menthe hover:text-foret md:min-h-[120px] md:gap-5 md:rounded-[22px] md:py-6 md:pr-7 md:pl-6">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-vert text-white md:size-16">
                            <x-icone :nom="$epreuve->icone" class="size-[22px] md:size-7" />
                        </div>
                        <div class="flex flex-1 flex-col gap-0.5 md:gap-1.5">
                            <div class="text-[17px] font-bold md:text-[21px]">{{ $epreuve->nom }}</div>
                            <div class="hidden text-[15px] leading-normal text-mousse md:block">{{ $epreuve->description }}</div>
                            <div class="text-sm text-mousse md:font-bold md:text-foret">{{ $epreuve->format }}</div>
                        </div>
                        <div class="hidden shrink-0 font-bold text-vert md:block">Commencer <span aria-hidden="true" class="inline-block transition-transform group-hover:translate-x-0.5">→</span></div>
                        <x-icone nom="droite" class="size-5 shrink-0 md:hidden" />
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Score visé (téléphone) --}}
        <section id="scores-cible" class="flex scroll-mt-4 flex-col gap-3.5 px-5 pb-10 md:hidden">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px]">Quel score vises-tu ?</h2>
            <p class="text-base leading-normal text-mousse">Choisis ton NCLC cible pour voir le score minimum à obtenir au TCF Canada.</p>
            <livewire:nclc-cible />
        </section>

        {{-- Comment ça marche --}}
        <section class="bg-brume">
            <div class="mx-auto flex max-w-[1200px] flex-col gap-[18px] px-5 pt-8 pb-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
                <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-center md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Comment ça marche</h2>
                <ol class="flex flex-col gap-[18px] md:grid md:grid-cols-3 md:gap-5">
                    @foreach ([
                        ["T'entraîner gratuitement", 'Des quiz courts, corrigés et expliqués à la fin.', "Choisis une épreuve et réponds aux questions comme le jour de l'examen. À la fin, chaque réponse est corrigée et expliquée."],
                        ['Passer un test blanc', "Les 4 épreuves avec le chrono de l'examen.", "Les 4 épreuves dans l'ordre et avec le chrono de l'examen, pour te mettre en conditions réelles."],
                        ['Cibler ce qui manque', 'Ton bilan te dit combien de points il te manque, épreuve par épreuve.', 'Ton bilan donne ton niveau par épreuve et te propose les quiz à refaire en priorité.'],
                    ] as $i => [$titre, $court, $long])
                        <li class="flex gap-3.5 md:flex-col md:rounded-3xl md:bg-white md:p-8">
                            <div @class([
                                'flex size-9 shrink-0 items-center justify-center rounded-full font-extrabold md:size-12 md:font-titre md:text-xl',
                                'bg-vert text-white' => $i < 2,
                                'bg-peche text-foret' => $i === 2,
                            ])>{{ $i + 1 }}</div>
                            <div class="flex flex-col gap-0.5 pt-1.5 md:gap-3.5 md:pt-0">
                                <div class="text-[17px] font-bold md:text-[22px]">{{ $titre }}</div>
                                <p class="text-[15px] leading-normal text-mousse md:hidden">{{ $court }}</p>
                                <p class="hidden text-base leading-relaxed text-mousse md:block">{{ $long }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Bilan --}}
        <section id="tests-blancs" class="mx-auto flex max-w-[1200px] scroll-mt-4 flex-col gap-4 px-5 py-10 md:px-10 md:py-24 lg:flex-row lg:items-center lg:gap-20 xl:px-0">
            <div class="flex flex-col gap-5 lg:w-[440px] lg:shrink-0">
                <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Ton bilan après un test blanc</h2>
                <p class="hidden text-lg leading-relaxed text-mousse md:block">Ton niveau par épreuve, en CECRL et en NCLC, et l'épreuve à travailler en priorité.</p>
                <div class="hidden gap-3 md:flex">
                    <a href="#tarifs" class="flex h-[54px] items-center rounded-[14px] bg-foret px-[26px] text-base font-bold text-white hover:bg-vert hover:text-white">Passer un test blanc</a>
                    <a href="{{ route('bilan') }}" wire:navigate class="flex h-[54px] items-center px-2 text-base font-bold text-foret underline decoration-2 underline-offset-4">Voir un exemple</a>
                </div>
            </div>

            <a href="{{ route('bilan') }}" wire:navigate class="flex flex-1 flex-col gap-3.5 rounded-[20px] border-[1.5px] border-ligne p-5 text-foret transition-colors hover:border-vert hover:text-foret md:gap-[18px] md:rounded-[28px] md:p-9">
                <div class="flex items-baseline justify-between">
                    <div class="flex items-baseline gap-2.5 md:gap-3">
                        <div class="font-titre text-4xl leading-none md:text-[44px]">B2</div>
                        <div class="font-bold text-vert md:text-lg">NCLC 7</div>
                    </div>
                    <div class="text-[13px] text-mousse md:text-sm">Exemple<span class="hidden md:inline"> de résultat</span></div>
                </div>
                @foreach ([
                    ['Compréhension orale', 'B2', 66, false],
                    ['Compréhension écrite', 'C1', 82, false],
                    ['Expression écrite', 'B1', 48, true],
                    ['Expression orale', 'B2', 64, false],
                ] as [$epreuve, $niveau, $pourcentage, $aTravailler])
                    <div class="flex flex-col gap-1.5 md:flex-row md:items-center md:gap-4">
                        <div class="flex justify-between text-[15px] md:w-[200px] md:text-base md:font-semibold">
                            <span>{{ $epreuve }}</span>
                            <span class="font-bold md:hidden">{{ $niveau }}{{ $aTravailler ? ' · à travailler' : '' }}</span>
                        </div>
                        <div class="h-3 flex-1 rounded-md bg-brume md:h-6 md:rounded-lg">
                            <div @class(['h-full rounded-md md:rounded-lg', 'bg-peche' => $aTravailler, 'bg-vert' => ! $aTravailler]) style="width: {{ $pourcentage }}%"></div>
                        </div>
                        <div class="hidden w-9 text-right font-bold md:block">{{ $niveau }}</div>
                    </div>
                @endforeach
                <p class="hidden text-[15px] text-mousse md:block">À travailler en priorité : <span class="font-bold text-foret">expression écrite</span></p>
            </a>
        </section>

        {{-- Barème NCLC (ordinateur) --}}
        <section id="scores" class="mx-auto hidden max-w-[1200px] scroll-mt-4 items-start gap-16 px-10 pb-24 md:flex md:flex-col lg:flex-row xl:px-0">
            <div class="flex flex-col gap-[18px] lg:w-[420px] lg:shrink-0">
                <h2 class="font-titre text-[44px] leading-[1.05] tracking-[-1.5px]">Quel score pour quel NCLC&nbsp;?</h2>
                <p class="text-[17px] leading-relaxed text-mousse">Correspondance entre les résultats du TCF Canada et les niveaux NCLC utilisés par IRCC. Vérifie le niveau exigé par ton programme.</p>
            </div>
            <div class="w-full flex-1 overflow-hidden rounded-3xl border-[1.5px] border-ligne">
                <table class="w-full border-collapse text-left text-base">
                    <thead>
                        <tr class="bg-brume">
                            <th scope="col" class="px-5 py-4 font-bold">NCLC</th>
                            @foreach (['co', 'ce', 'eo', 'ee'] as $code)
                                <th scope="col" class="px-5 py-4 font-bold">{{ \App\Support\Nclc::EPREUVES[$code][1] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bareme as $niveau => $plages)
                            <tr @class(['border-t-[1.5px] border-ligne', 'bg-peche-clair font-bold' => (string) $niveau === $cible])>
                                <th scope="row" @class(['px-5 py-3.5', 'font-extrabold' => (string) $niveau === $cible, 'font-bold' => (string) $niveau !== $cible])>{{ $niveau }}</th>
                                @foreach (['co', 'ce', 'eo', 'ee'] as $code)
                                    <td class="px-5 py-3.5">{{ \App\Support\Nclc::plage($plages[$code]) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Avis --}}
        <section class="bg-brume">
            <div class="mx-auto flex max-w-[1200px] flex-col gap-3 px-5 py-8 md:grid md:grid-cols-3 md:gap-5 md:px-10 md:py-20 xl:px-0">
                @foreach ([1, 2] as $n)
                    <figure @class(['flex-col gap-2.5 rounded-[20px] bg-white p-5 md:gap-3.5 md:rounded-3xl md:p-8', 'flex' => $n === 1, 'hidden md:flex' => $n === 2])>
                        <blockquote class="text-[17px] leading-normal font-medium md:text-[19px]">« [TÉMOIGNAGE D'UN CANDIDAT] »</blockquote>
                        <figcaption class="text-sm text-mousse md:mt-auto md:text-[15px]">[PRÉNOM] · [NIVEAU OBTENU]</figcaption>
                    </figure>
                @endforeach
                <div class="grid grid-cols-2 gap-3 md:flex md:flex-col md:gap-5">
                    <div class="flex flex-col justify-center gap-1 rounded-2xl bg-white p-3.5 text-center md:flex-1 md:rounded-3xl md:px-7 md:py-6 md:text-left">
                        <div class="font-titre text-lg md:text-3xl">[NOMBRE]</div>
                        <div class="text-[13px] text-mousse md:text-[15px]">quiz corrigés</div>
                    </div>
                    <div class="flex flex-col justify-center gap-1 rounded-2xl bg-white p-3.5 text-center md:flex-1 md:rounded-3xl md:px-7 md:py-6 md:text-left">
                        <div class="font-titre text-lg md:text-3xl">[NOTE] / 5</div>
                        <div class="text-[13px] text-mousse md:text-[15px]">avis<span class="hidden md:inline"> · note moyenne</span></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Tarifs --}}
        <section id="tarifs" class="mx-auto flex max-w-[1200px] scroll-mt-4 flex-col gap-3.5 px-5 py-10 md:gap-9 md:px-10 md:py-24 xl:px-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-center md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">
                <span class="md:hidden">Tarifs</span><span class="hidden md:inline">Deux façons de te préparer</span>
            </h2>
            <div class="grid gap-3.5 md:grid-cols-2 md:gap-6 xl:px-[100px]">
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
                    <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="flex h-[52px] items-center justify-center rounded-[14px] bg-brume text-base font-bold text-foret hover:bg-menthe hover:text-foret md:mt-2 md:h-[54px]">Commencer gratuitement</a>
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
                    <a href="#" class="flex h-[52px] items-center justify-center rounded-[14px] bg-peche text-base font-bold text-foret hover:bg-white hover:text-foret md:mt-2 md:h-[54px]">Passer un test blanc</a>
                </div>
            </div>
        </section>

        {{-- Questions fréquentes --}}
        <section id="faq" class="mx-auto flex max-w-[1200px] scroll-mt-4 flex-col gap-2.5 px-5 pb-10 md:px-10 md:pb-24 lg:flex-row lg:gap-20 xl:px-0" x-data="{ ouverte: 0 }">
            <h2 class="mb-1 font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px] lg:w-[360px] lg:shrink-0">Questions fréquentes</h2>
            <div class="flex flex-1 flex-col gap-2.5 md:gap-3">
                @foreach ($faq as $i => [$question, $reponse])
                    <div class="rounded-2xl bg-brume px-4 md:rounded-[20px] md:px-7">
                        <h3>
                            <button type="button" @click="ouverte = ouverte === {{ $i }} ? null : {{ $i }}" :aria-expanded="ouverte === {{ $i }}" aria-controls="faq-{{ $i }}"
                                class="flex min-h-14 w-full items-center justify-between gap-3 text-left text-base font-bold text-foret md:min-h-[68px] md:text-lg">
                                {{ $question }}
                                <x-icone nom="bas" class="size-5 shrink-0 transition-transform duration-200" x-bind:class="ouverte === {{ $i }} && 'rotate-180'" />
                            </button>
                        </h3>
                        <div id="faq-{{ $i }}" x-show="ouverte === {{ $i }}" x-collapse @if ($i !== 0) x-cloak @endif>
                            <p class="pb-4 text-[15px] leading-[1.55] text-mousse md:pb-6 md:text-base md:leading-relaxed">{{ $reponse }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Appel final --}}
        <section class="px-3 pb-8 md:px-10 md:pb-16">
            <div class="mx-auto flex max-w-[1360px] flex-col gap-4 rounded-[28px] bg-peche px-5 py-7 md:flex-row md:items-center md:justify-between md:gap-10 md:rounded-[36px] md:px-14 md:py-14 xl:px-20">
                <h2 class="font-titre text-[26px] leading-[1.1] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Ton premier quiz prend cinq minutes.</h2>
                <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="flex h-14 shrink-0 items-center justify-center rounded-2xl bg-foret px-8 text-[17px] font-bold text-white hover:bg-vert hover:text-white md:h-[58px] md:text-lg">Faire un quiz gratuit</a>
            </div>
        </section>
    </main>

    <x-site.footer />
</div>
