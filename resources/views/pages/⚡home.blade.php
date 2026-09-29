<?php

use App\Models\Epreuve;
use App\Support\Faq;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function epreuves()
    {
        return Epreuve::orderBy('ordre')->get();
    }

    /** @return list<string> */
    public function bandeau(): array
    {
        return [...$this->epreuves->pluck('nom'), 'Niveau CECRL', 'Niveau NCLC', 'Correction immédiate', 'Tests blancs chronométrés'];
    }

    public function render()
    {
        return $this->view(['faq' => Faq::accueil(), 'bandeau' => $this->bandeau()]);
    }
};
?>

<div x-data class="flex min-h-dvh flex-col">
    <div aria-hidden="true" class="progression-lecture fixed inset-x-0 top-0 z-40 h-1 bg-vert"></div>

    <x-site.header collant />

    <main class="flex-1">
        {{-- Accueil --}}
        <section class="px-3 pt-1 md:px-10 md:pt-2">
            <div class="relative isolate mx-auto flex max-w-[1360px] flex-col gap-4 overflow-hidden rounded-[28px] bg-menthe px-5 pt-7 pb-6 md:rounded-[36px] md:p-14 lg:flex-row lg:items-center lg:gap-16 xl:px-20 xl:py-[72px]">
                <x-arcs class="-top-[120px] -right-[120px] size-[300px] animate-tourne lg:hidden" />
                <x-arcs class="-bottom-[260px] -left-[200px] hidden size-[560px] animate-tourne lg:block" />
                <div aria-hidden="true" class="pointer-events-none absolute -top-40 right-1/4 -z-10 hidden size-[420px] rounded-full bg-white/50 blur-3xl lg:block"></div>

                <div class="flex flex-col gap-4 md:gap-6 lg:w-[620px] lg:shrink-0">
                    <div class="entree flex items-center gap-2 self-start rounded-full bg-foret/8 px-3 py-1.5 text-[13px] font-semibold md:px-3.5 md:py-[7px] md:text-sm">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-vert opacity-60"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-vert"></span>
                        </span>
                        TCF Canada · TCF Tout public
                    </div>
                    <h1 class="font-titre text-[34px] leading-[1.05] tracking-[-1px] md:text-[64px] md:leading-[1.02] md:tracking-[-2px]">
                        <span class="entree inline-block" style="--delai: 80ms">Sache ton</span>
                        <span class="entree relative inline-block text-vert" style="--delai: 180ms">niveau<svg class="absolute -bottom-[0.12em] left-0 h-[0.22em] w-full overflow-visible" viewBox="0 0 200 16" preserveAspectRatio="none" fill="none" aria-hidden="true"><path class="trace" style="--delai: 900ms" pathLength="1" d="M3 11 C 40 3, 80 3, 110 8 S 170 14, 197 5" stroke="#FFB59E" stroke-width="6" stroke-linecap="round" vector-effect="non-scaling-stroke"/></svg></span>
                        <span class="entree inline-block" style="--delai: 280ms">avant le</span>
                        <span class="entree inline-block" style="--delai: 380ms">jour&nbsp;J.</span>
                    </h1>
                    <p class="entree max-w-[540px] text-[17px] leading-normal text-mousse-fonce md:text-xl md:leading-[1.55]" style="--delai: 480ms">Quiz gratuits pour t'entraîner, tests blancs chronométrés pour un niveau estimé CECRL et NCLC.</p>
                    <div class="entree flex flex-col gap-2.5 md:flex-row md:items-center md:gap-3" style="--delai: 580ms">
                        <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="reflet group flex h-14 items-center justify-center gap-2 rounded-2xl bg-foret px-[30px] text-[17px] font-bold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-vert hover:text-white hover:shadow-[0_14px_30px_rgba(14,122,69,0.3)] active:translate-y-0 md:h-[58px] md:text-lg">
                            Faire un quiz gratuit
                            <x-icone nom="droite" :epaisseur="2.4" class="size-5 transition-transform duration-300 group-hover:translate-x-1" />
                        </a>
                        <a href="{{ route('epreuves') }}" wire:navigate class="group flex h-[52px] items-center justify-center gap-2 rounded-2xl border-[1.5px] border-foret px-[26px] text-base font-bold text-foret transition duration-300 hover:bg-foret/5 md:h-[58px] md:gap-2.5 md:text-lg">
                            <x-icone nom="boussole" class="size-[18px] transition-transform duration-500 ease-ressort group-hover:rotate-[135deg] md:size-5" />Explorer les épreuves
                        </a>
                    </div>
                    <p class="entree text-center text-[13px] text-mousse-fonce md:text-left md:text-sm" style="--delai: 680ms">Gratuit · corrigé et expliqué · sans carte<span class="hidden md:inline"> bancaire</span></p>
                </div>

                <div class="entree relative lg:flex-1" style="--delai: 250ms">
                    <div class="flex flex-col items-center gap-1 rounded-[20px] bg-white px-3 pt-[18px] pb-4 shadow-[0_24px_60px_rgba(11,46,28,0.08)] md:gap-2 md:rounded-[28px] md:px-6 md:pt-9 md:pb-7">
                        <x-jauge anime class="h-auto w-[280px] md:w-[420px] lg:w-full lg:max-w-[420px]" />
                        <p class="entree text-sm font-semibold text-lichen md:hidden" style="--delai: 1500ms">Exemple : <span class="font-bold text-foret">B2 · NCLC 7</span> après un test blanc</p>
                        <div class="entree hidden font-titre text-[44px] leading-none md:block" style="--delai: 1500ms">B2 · NCLC 7</div>
                        <p class="entree hidden text-[15px] text-lichen md:block" style="--delai: 1600ms">Exemple de niveau estimé après un test blanc</p>
                    </div>

                    {{-- Pastilles flottantes (ordinateur) --}}
                    <div aria-hidden="true" class="entree absolute -top-5 -left-6 hidden xl:block" style="--delai: 1100ms">
                        <div class="flex animate-flotte items-center gap-2.5 rounded-2xl bg-white px-4 py-3 text-sm font-bold shadow-[0_16px_40px_rgba(11,46,28,0.14)]">
                            <span class="flex size-8 items-center justify-center rounded-full bg-menthe text-vert"><x-icone nom="valide" class="size-[18px]" /></span>
                            Réponse corrigée
                        </div>
                    </div>
                    <div aria-hidden="true" class="entree absolute -right-6 -bottom-8 hidden xl:block" style="--delai: 1300ms">
                        <div class="flex animate-flotte items-center gap-2.5 rounded-2xl bg-foret px-4 py-3 text-sm font-bold text-white shadow-[0_16px_40px_rgba(11,46,28,0.22)] [animation-delay:-3s]">
                            <span class="flex size-8 items-center justify-center rounded-full bg-peche text-foret"><x-icone nom="chrono" class="size-[18px]" /></span>
                            Au chrono de l'examen
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Bandeau défilant --}}
        <div class="fondu-bords overflow-hidden py-5 md:py-8">
            <div class="flex w-max animate-defile hover:[animation-play-state:paused]">
                @foreach ([false, true] as $copie)
                    <ul class="flex shrink-0 items-center" @if ($copie) aria-hidden="true" @endif>
                        @foreach ($bandeau as $element)
                            <li class="flex items-center gap-6 pr-6 font-titre text-base whitespace-nowrap text-foret/80 md:gap-10 md:pr-10 md:text-2xl">
                                {{ $element }}
                                <x-logo class="size-4 md:size-6" />
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>

        {{-- Points forts --}}
        <section class="mx-auto hidden max-w-[1200px] px-10 md:block xl:px-0">
            <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                @foreach ([
                    ['chrono', "Au format de l'examen", 'Les 4 épreuves, avec leurs durées'],
                    ['valide', 'Correction immédiate', 'Chaque réponse expliquée'],
                    ['jauge', 'Niveau CECRL et NCLC', 'Estimé après chaque test blanc'],
                    ['telephone', 'Mobile et ordinateur', "Entraîne-toi où que tu sois"],
                ] as $i => [$icone, $titre, $texte])
                    <div x-apparition style="--delai: {{ $i * 90 }}ms" class="group flex items-center gap-3.5 rounded-[20px] border-[1.5px] border-ligne p-5 transition duration-300 hover:-translate-y-1 hover:border-vert/40 hover:shadow-[0_16px_40px_rgba(11,46,28,0.08)]">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-[14px] bg-brume text-vert transition duration-300 ease-ressort group-hover:scale-110 group-hover:-rotate-6 group-hover:bg-menthe"><x-icone :nom="$icone" class="size-6" /></div>
                        <div class="flex flex-col gap-0.5">
                            <div class="text-base font-bold">{{ $titre }}</div>
                            <div class="text-sm text-mousse">{{ $texte }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Épreuves --}}
        <section id="epreuves" class="mx-auto flex max-w-[1200px] scroll-mt-20 flex-col gap-4 px-5 py-10 md:scroll-mt-24 md:gap-9 md:px-10 md:py-24 xl:px-0">
            <div x-apparition class="flex items-end justify-between gap-6">
                <div class="flex flex-col gap-2.5">
                    <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Choisis une épreuve</h2>
                    <p class="hidden text-lg text-mousse md:block">Des quiz gratuits au format de l'examen, corrigés et expliqués.</p>
                </div>
                <a href="{{ route('epreuves') }}" wire:navigate class="group relative hidden shrink-0 py-3 text-base font-bold text-foret md:block">
                    Tout savoir sur les épreuves
                    <span aria-hidden="true" class="absolute inset-x-0 bottom-0 h-0.5 bg-foret"></span>
                    <span aria-hidden="true" class="absolute inset-x-0 bottom-0 h-0.5 origin-left scale-x-0 bg-vert transition-transform duration-300 ease-ressort group-hover:scale-x-100"></span>
                </a>
            </div>

            <div class="grid gap-2.5 md:gap-4 lg:grid-cols-2">
                @foreach ($this->epreuves as $epreuve)
                    <a href="{{ route('quiz', $epreuve) }}" wire:navigate wire:key="epreuve-{{ $epreuve->id }}" x-apparition style="--delai: {{ $loop->index * 90 }}ms"
                        class="group flex min-h-[76px] items-center gap-3.5 rounded-[18px] bg-brume py-3 pr-4 pl-3 text-foret transition duration-300 ease-ressort hover:-translate-y-1 hover:bg-menthe hover:text-foret hover:shadow-[0_18px_40px_rgba(11,46,28,0.08)] md:min-h-[120px] md:gap-5 md:rounded-[22px] md:py-6 md:pr-7 md:pl-6">
                        <div class="relative flex size-12 shrink-0 items-center justify-center rounded-full bg-vert text-white transition duration-500 ease-ressort group-hover:scale-110 group-hover:-rotate-12 md:size-16">
                            <span aria-hidden="true" class="absolute inset-0 rounded-full bg-vert opacity-0 transition duration-500 group-hover:scale-125 group-hover:opacity-20"></span>
                            <x-icone :nom="$epreuve->icone" class="relative size-[22px] md:size-7" />
                        </div>
                        <div class="flex flex-1 flex-col gap-0.5 md:gap-1.5">
                            <div class="text-[17px] font-bold md:text-[21px]">{{ $epreuve->nom }}</div>
                            <div class="hidden text-[15px] leading-normal text-mousse md:block">{{ $epreuve->description }}</div>
                            <div class="text-sm text-mousse md:font-bold md:text-foret">{{ $epreuve->format }}</div>
                        </div>
                        <div class="hidden shrink-0 font-bold text-vert md:block">Commencer <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-1.5">→</span></div>
                        <x-icone nom="droite" class="size-5 shrink-0 transition-transform duration-300 group-hover:translate-x-1 md:hidden" />
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Score visé (téléphone) --}}
        <section id="scores-cible" x-apparition class="flex scroll-mt-20 flex-col gap-3.5 px-5 pb-10 md:hidden">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px]">Quel score vises-tu ?</h2>
            <p class="text-base leading-normal text-mousse">Choisis ton NCLC cible pour voir le score minimum à obtenir au TCF Canada.</p>
            <livewire:nclc-cible />
            <a href="{{ route('scores') }}" wire:navigate class="flex h-[52px] items-center justify-center rounded-2xl border-[1.5px] border-foret text-base font-bold text-foret">Voir le barème complet</a>
        </section>

        {{-- Comment ça marche --}}
        <section class="relative isolate overflow-hidden bg-brume">
            <x-arcs class="-top-[180px] -right-[180px] hidden size-[480px] animate-tourne md:block" />
            <div class="mx-auto flex max-w-[1200px] flex-col gap-[18px] px-5 pt-8 pb-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
                <h2 x-apparition class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-center md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Comment ça marche</h2>
                <ol class="relative flex flex-col gap-[18px] md:grid md:grid-cols-3 md:gap-5">
                    {{-- Fil qui relie les étapes (téléphone) --}}
                    <span aria-hidden="true" class="absolute top-9 bottom-9 left-[17px] w-0.5 rounded-full bg-vert/20 md:hidden"></span>
                    @foreach ([
                        ["T'entraîner gratuitement", 'Des quiz courts, corrigés et expliqués à la fin.', "Choisis une épreuve et réponds aux questions comme le jour de l'examen. À la fin, chaque réponse est corrigée et expliquée."],
                        ['Passer un test blanc', "Les 4 épreuves avec le chrono de l'examen.", "Les 4 épreuves dans l'ordre et avec le chrono de l'examen, pour te mettre en conditions réelles."],
                        ['Cibler ce qui manque', 'Ton bilan te dit combien de points il te manque, épreuve par épreuve.', 'Ton bilan donne ton niveau par épreuve et te propose les quiz à refaire en priorité.'],
                    ] as $i => [$titre, $court, $long])
                        <li x-apparition style="--delai: {{ $i * 140 }}ms" class="group relative flex gap-3.5 md:flex-col md:rounded-3xl md:bg-white md:p-8 md:transition md:duration-300 md:hover:-translate-y-1.5 md:hover:shadow-[0_20px_44px_rgba(11,46,28,0.08)]">
                            <div @class([
                                'relative flex size-9 shrink-0 items-center justify-center rounded-full font-extrabold ring-4 ring-brume transition-transform duration-500 ease-ressort group-hover:scale-110 md:size-12 md:font-titre md:text-xl md:ring-0',
                                'bg-vert text-white' => $i < 2,
                                'bg-peche text-foret' => $i === 2,
                            ])>{{ $i + 1 }}</div>
                            <div class="flex flex-col gap-0.5 pt-1.5 md:gap-3.5 md:pt-0">
                                <div class="text-[17px] font-bold md:text-[22px]">{{ $titre }}</div>
                                <p class="text-[15px] leading-normal text-mousse md:hidden">{{ $court }}</p>
                                <p class="hidden text-base leading-relaxed text-mousse md:block">{{ $long }}</p>
                            </div>
                            @if ($i < 2)
                                <x-icone nom="droite" class="absolute top-1/2 -right-[18px] z-10 hidden size-4 -translate-y-1/2 text-vert/60 md:block" />
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Bilan --}}
        <section id="tests-blancs" class="mx-auto flex max-w-[1200px] scroll-mt-20 flex-col gap-4 px-5 py-10 md:scroll-mt-24 md:px-10 md:py-24 lg:flex-row lg:items-center lg:gap-20 xl:px-0">
            <div x-apparition class="flex flex-col gap-5 lg:w-[440px] lg:shrink-0">
                <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Ton bilan après un test blanc</h2>
                <p class="hidden text-lg leading-relaxed text-mousse md:block">Ton niveau par épreuve, en CECRL et en NCLC, et l'épreuve à travailler en priorité.</p>
                <div class="hidden gap-3 md:flex">
                    <a href="{{ route('tests-blancs') }}" wire:navigate class="reflet flex h-[54px] items-center rounded-[14px] bg-foret px-[26px] text-base font-bold text-white transition duration-300 hover:-translate-y-0.5 hover:bg-vert hover:text-white hover:shadow-[0_14px_30px_rgba(14,122,69,0.3)]">Passer un test blanc</a>
                    <a href="{{ route('bilan') }}" wire:navigate class="flex h-[54px] items-center px-2 text-base font-bold text-foret underline decoration-2 underline-offset-4 transition-[text-underline-offset] hover:underline-offset-8">Voir un exemple</a>
                </div>
            </div>

            <a href="{{ route('bilan') }}" wire:navigate x-apparition style="--delai: 120ms" class="group flex flex-1 flex-col gap-3.5 rounded-[20px] border-[1.5px] border-ligne p-5 text-foret transition duration-300 hover:-translate-y-1 hover:border-vert hover:text-foret hover:shadow-[0_24px_60px_rgba(11,46,28,0.08)] md:gap-[18px] md:rounded-[28px] md:p-9">
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
                ] as $i => [$epreuve, $niveau, $pourcentage, $aTravailler])
                    <div class="flex flex-col gap-1.5 md:flex-row md:items-center md:gap-4">
                        <div class="flex justify-between text-[15px] md:w-[200px] md:text-base md:font-semibold">
                            <span>{{ $epreuve }}</span>
                            <span class="font-bold md:hidden">{{ $niveau }}{{ $aTravailler ? ' · à travailler' : '' }}</span>
                        </div>
                        <div class="h-3 flex-1 rounded-md bg-brume md:h-6 md:rounded-lg">
                            <div @class(['barre h-full rounded-md md:rounded-lg', 'bg-peche' => $aTravailler, 'bg-vert' => ! $aTravailler]) style="width: {{ $pourcentage }}%; --delai-barre: {{ $i * 150 }}ms"></div>
                        </div>
                        <div class="hidden w-9 text-right font-bold md:block">{{ $niveau }}</div>
                    </div>
                @endforeach
                <p class="hidden text-[15px] text-mousse md:block">À travailler en priorité : <span class="font-bold text-foret">expression écrite</span></p>
            </a>
        </section>

        {{-- Barème NCLC (ordinateur) --}}
        <section id="scores" class="mx-auto hidden max-w-[1200px] scroll-mt-24 items-start gap-16 px-10 pb-24 md:flex md:flex-col lg:flex-row xl:px-0">
            <div x-apparition class="flex flex-col gap-[18px] lg:sticky lg:top-28 lg:w-[420px] lg:shrink-0">
                <h2 class="font-titre text-[44px] leading-[1.05] tracking-[-1.5px]">Quel score pour quel NCLC&nbsp;?</h2>
                <p class="text-[17px] leading-relaxed text-mousse">Correspondance entre les résultats du TCF Canada et les niveaux NCLC utilisés par IRCC. Vérifie le niveau exigé par ton programme.</p>
                <a href="{{ route('scores') }}" wire:navigate class="self-start py-2 text-base font-bold text-foret underline decoration-2 underline-offset-4">Comprendre le barème</a>
            </div>
            <div x-apparition style="--delai: 120ms" class="w-full flex-1">
                <x-bareme :mobile="false" />
            </div>
        </section>

        {{-- Avis --}}
        <section class="bg-brume">
            <div class="mx-auto flex max-w-[1200px] flex-col gap-3 px-5 py-8 md:grid md:grid-cols-3 md:gap-5 md:px-10 md:py-20 xl:px-0">
                @foreach ([1, 2] as $n)
                    <figure x-apparition style="--delai: {{ ($n - 1) * 120 }}ms" @class(['relative flex-col gap-2.5 overflow-hidden rounded-[20px] bg-white p-5 transition duration-300 hover:-translate-y-1 hover:shadow-[0_20px_44px_rgba(11,46,28,0.08)] md:gap-3.5 md:rounded-3xl md:p-8', 'flex' => $n === 1, 'hidden md:flex' => $n === 2])>
                        <span aria-hidden="true" class="pointer-events-none absolute top-1 right-5 font-titre text-[96px] leading-none text-menthe">“</span>
                        <blockquote class="relative text-[17px] leading-normal font-medium md:text-[19px]">« [TÉMOIGNAGE D'UN CANDIDAT] »</blockquote>
                        <figcaption class="relative text-sm text-mousse md:mt-auto md:text-[15px]">[PRÉNOM] · [NIVEAU OBTENU]</figcaption>
                    </figure>
                @endforeach
                <div x-apparition style="--delai: 240ms" class="grid grid-cols-2 gap-3 md:flex md:flex-col md:gap-5">
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
        <section id="tarifs" class="mx-auto flex max-w-[1200px] scroll-mt-20 flex-col gap-3.5 px-5 py-10 md:scroll-mt-24 md:gap-9 md:px-10 md:py-24 xl:px-0">
            <h2 x-apparition class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-center md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">
                <span class="md:hidden">Tarifs</span><span class="hidden md:inline">Deux façons de te préparer</span>
            </h2>
            <x-site.tarifs anime class="xl:px-[100px]" />
            <a href="{{ route('tarifs') }}" wire:navigate class="self-center py-2 text-base font-bold text-foret underline decoration-2 underline-offset-4">Comparer les offres en détail</a>
        </section>

        {{-- Questions fréquentes --}}
        <section id="faq" class="mx-auto flex max-w-[1200px] scroll-mt-20 flex-col gap-2.5 px-5 pb-10 md:scroll-mt-24 md:px-10 md:pb-24 lg:flex-row lg:gap-20 xl:px-0">
            <div x-apparition class="mb-1 flex flex-col gap-4 lg:w-[360px] lg:shrink-0">
                <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Questions fréquentes</h2>
                <a href="{{ route('faq') }}" wire:navigate class="hidden self-start py-2 text-base font-bold text-foret underline decoration-2 underline-offset-4 lg:block">Toutes les questions</a>
            </div>
            <x-site.faq :questions="$faq" class="flex-1" />
            <a href="{{ route('faq') }}" wire:navigate class="self-start py-3 text-base font-bold text-foret underline decoration-2 underline-offset-4 lg:hidden">Toutes les questions</a>
        </section>

        {{-- Appel final --}}
        <x-site.appel anime />
    </main>

    <x-site.footer />
</div>
