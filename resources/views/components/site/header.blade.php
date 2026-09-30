@props(['collant' => false])

@php
    use App\Models\Epreuve;
    use App\Support\Langue;

    $epreuves = once(fn () => Epreuve::orderBy('ordre')->get(['slug', 'code', 'nom', 'format', 'description', 'icone']));

    $liens = [
        'tests-blancs' => __('Tests blancs'),
        'scores' => __('Scores NCLC'),
        'tarifs' => __('Tarifs'),
    ];

    $ressources = [
        ['articles', 'article', __('Articles'), __('Méthodes et conseils pour le jour J')],
        ['faq', 'question', __('FAQ'), __('Les réponses aux questions fréquentes')],
        ['contact', 'enveloppe', __('Contact'), __('Une question ? On te répond')],
    ];

    $actif = fn (string ...$routes) => request()->routeIs(...$routes);
    $epreuvesActif = $actif('epreuves', 'epreuve');
    $ressourcesActif = $actif('articles', 'article', 'faq', 'contact');

    // Slug de l'épreuve affichée (le paramètre est un modèle ou une chaîne selon le moment de la liaison).
    $parametre = request()->route('epreuve');
    $epreuveCourante = request()->routeIs('epreuve') ? ($parametre instanceof Epreuve ? $parametre->slug : $parametre) : null;

    $langue = Langue::actuelle();
    $utilisateur = auth()->user();

    // Pastille du menu principal : menthe quand la page est active.
    $pastille = fn (bool $actif) => $actif ? 'bg-menthe text-foret' : 'text-mousse-fonce hover:bg-brume hover:text-foret';
    $panneau = 'absolute top-full z-40 pt-3 whitespace-normal';
    $transition = 'x-transition:enter="transition duration-200 ease-ressort" x-transition:enter-start="opacity-0 translate-y-1.5" x-transition:leave="transition duration-100 ease-in" x-transition:leave-end="opacity-0 translate-y-1"';
@endphp

{{-- Sous xl (la barre complète ne tient pas en arabe à 1024 px) : logo + bouton menu ; le menu s'ouvre en plein écran (focus piégé, défilement bloqué). --}}
<header x-data="{ ouvert: false, defile: false }" @keydown.escape.window="ouvert = false" @resize.window="innerWidth >= 1280 && (ouvert = false)"
    @if ($collant) x-init="defile = scrollY > 8" @scroll.window.passive="defile = scrollY > 8" :class="{ 'bg-white/85 shadow-[0_1px_0_var(--color-ligne),0_12px_32px_rgba(11,46,28,0.06)] backdrop-blur-md': defile && ! ouvert, 'z-50': ouvert }" @else :class="ouvert && 'z-50'" @endif
    @class(['z-30', 'sticky top-0 transition-[background-color,box-shadow] duration-300' => $collant, 'relative' => ! $collant])>
    <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between gap-4 pe-2 ps-5 md:h-20 md:pe-6 md:ps-10 lg:px-10 xl:px-0">
        <a href="{{ route('accueil') }}" wire:navigate aria-label="{{ __('Edumica, accueil') }}" class="group flex shrink-0 items-center gap-2.5 text-foret hover:text-foret md:gap-3">
            <x-logo class="size-[30px] transition-transform duration-500 ease-ressort group-hover:rotate-[30deg] md:size-9" />
            <span class="font-titre text-xl md:text-2xl">edumica</span>
        </a>

        {{-- Menu principal (xl+) : une barre en pastilles, deux entrées ouvrent un panneau. --}}
        <nav aria-label="{{ __('Navigation principale') }}" class="hidden items-center gap-0.5 rounded-full bg-white/90 p-1.5 text-[15px] font-semibold whitespace-nowrap shadow-[0_1px_2px_rgba(11,46,28,0.06),0_12px_32px_-10px_rgba(11,46,28,0.18)] ring-1 ring-ligne backdrop-blur-md xl:flex">
            {{-- Épreuves --}}
            <div x-data="deroulant" @mouseenter="entrer" @mouseleave="sortir" @focusout="quitter" @keydown.escape.stop="fermer(true)" @click.outside="fermer()" class="relative">
                <button type="button" x-ref="bouton" @click="basculer" :aria-expanded="ouvert" aria-controls="panneau-epreuves"
                    @class(['flex h-10 items-center gap-1.5 rounded-full px-4 transition-colors', $pastille($epreuvesActif)]) :class="ouvert && 'bg-menthe! text-foret!'">
                    {{ __('Épreuves') }}
                    <x-icone nom="bas" :epaisseur="2.4" class="size-4 transition-transform duration-200" ::class="ouvert && 'rotate-180'" />
                </button>

                <div id="panneau-epreuves" x-show="ouvert" x-cloak {!! $transition !!} class="{{ $panneau }} -start-4 w-[680px]">
                    <div class="grid grid-cols-[1fr_216px] gap-2 rounded-[26px] bg-white p-2 shadow-[0_24px_60px_-12px_rgba(11,46,28,0.22)] ring-1 ring-ligne">
                        <div class="flex flex-col">
                            <div class="grid grid-cols-2 gap-1">
                                @foreach ($epreuves as $epreuve)
                                    <a href="{{ route('epreuve', $epreuve) }}" wire:navigate @if ($epreuveCourante === $epreuve->slug) aria-current="page" @endif
                                        class="group flex flex-col gap-2.5 rounded-[18px] p-3.5 text-foret transition-colors hover:bg-brume hover:text-foret aria-[current=page]:bg-brume">
                                        <span class="flex items-center justify-between">
                                            <span class="flex size-10 items-center justify-center rounded-full bg-menthe text-vert transition-colors duration-200 group-hover:bg-vert group-hover:text-white">
                                                <x-icone :nom="$epreuve->icone" class="size-5" />
                                            </span>
                                            <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-vert tabular-nums ring-1 ring-ligne">{{ \App\Support\GuideEpreuve::duree($epreuve->code) }}</span>
                                        </span>
                                        <span class="flex flex-col gap-0.5">
                                            <span class="text-[15px] font-bold">{{ __($epreuve->nom) }}</span>
                                            <span class="text-[13px] leading-snug font-medium text-lichen">{{ __($epreuve->description) }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                            <a href="{{ route('epreuves') }}" wire:navigate class="group mt-1 flex items-center justify-between rounded-[14px] px-3.5 py-3 text-sm font-bold text-vert hover:bg-brume">
                                {{ __('Voir le guide des 4 épreuves') }}
                                <x-icone nom="fleche" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                            </a>
                        </div>

                        <a href="{{ route('tests-blancs') }}" wire:navigate class="group relative isolate flex flex-col justify-between gap-6 overflow-hidden rounded-[20px] bg-foret p-5 text-white hover:text-white">
                            <x-arcs couleur="#D3F4DF" class="-end-16 -bottom-16 size-48 opacity-80 transition-transform duration-700 ease-ressort group-hover:rotate-45" />
                            <span class="flex flex-col gap-2">
                                <span class="self-start rounded-full bg-white/12 px-2.5 py-1 text-xs font-bold text-menthe">{{ __('2 h 47') }}</span>
                                <span class="font-titre text-xl leading-tight">{{ __('Test blanc complet') }}</span>
                                <span class="text-[13px] leading-snug text-white/75">{{ __('Les 4 épreuves enchaînées, dans les conditions du jour J.') }}</span>
                            </span>
                            <span class="flex items-center gap-1.5 text-sm font-bold text-peche">
                                {{ __('Découvrir') }}
                                <x-icone nom="fleche" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            @foreach ($liens as $route => $libelle)
                <a href="{{ route($route) }}" wire:navigate @if ($actif($route)) aria-current="page" @endif
                    @class(['flex h-10 items-center rounded-full px-4 transition-colors', $pastille($actif($route))])>{{ $libelle }}</a>
            @endforeach

            {{-- Ressources --}}
            <div x-data="deroulant" @mouseenter="entrer" @mouseleave="sortir" @focusout="quitter" @keydown.escape.stop="fermer(true)" @click.outside="fermer()" class="relative">
                <button type="button" x-ref="bouton" @click="basculer" :aria-expanded="ouvert" aria-controls="panneau-ressources"
                    @class(['flex h-10 items-center gap-1.5 rounded-full px-4 transition-colors', $pastille($ressourcesActif)]) :class="ouvert && 'bg-menthe! text-foret!'">
                    {{ __('Ressources') }}
                    <x-icone nom="bas" :epaisseur="2.4" class="size-4 transition-transform duration-200" ::class="ouvert && 'rotate-180'" />
                </button>

                <div id="panneau-ressources" x-show="ouvert" x-cloak {!! $transition !!} class="{{ $panneau }} -end-4 w-[320px]">
                    <div class="flex flex-col gap-0.5 rounded-[22px] bg-white p-2 shadow-[0_24px_60px_-12px_rgba(11,46,28,0.22)] ring-1 ring-ligne">
                        @foreach ($ressources as [$route, $icone, $libelle, $detail])
                            <a href="{{ route($route) }}" wire:navigate @if ($actif($route, $route === 'articles' ? 'article' : $route)) aria-current="page" @endif
                                class="group flex items-center gap-3.5 rounded-[16px] p-3 text-foret hover:bg-brume hover:text-foret aria-[current=page]:bg-brume">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-menthe text-vert transition-colors duration-200 group-hover:bg-vert group-hover:text-white">
                                    <x-icone :nom="$icone" class="size-5" />
                                </span>
                                <span class="flex flex-col">
                                    <span class="text-[15px] font-bold">{{ $libelle }}</span>
                                    <span class="text-[13px] font-medium text-lichen">{{ $detail }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </nav>

        <div class="hidden shrink-0 items-center gap-1.5 whitespace-nowrap xl:flex">
            {{-- Langue : le code de la langue active, les langues dans un petit panneau. --}}
            <div x-data="deroulant" @focusout="quitter" @keydown.escape.stop="fermer(true)" @click.outside="fermer()" class="relative">
                <button type="button" x-ref="bouton" @click="basculer" :aria-expanded="ouvert" aria-controls="panneau-langue" aria-label="{{ __('Langue : :langue', ['langue' => Langue::nom($langue)]) }}"
                    class="flex h-11 items-center gap-1.5 rounded-full px-3 text-sm font-bold text-foret transition-colors hover:bg-brume" :class="ouvert && 'bg-brume'">
                    <x-icone nom="globe" class="size-[18px] text-vert" />
                    <span class="uppercase">{{ $langue }}</span>
                    <x-icone nom="bas" :epaisseur="2.4" class="size-3.5 text-mousse transition-transform duration-200" ::class="ouvert && 'rotate-180'" />
                </button>

                <div id="panneau-langue" x-show="ouvert" x-cloak {!! $transition !!} class="absolute top-full -end-2 z-40 pt-2">
                    <div class="flex w-56 flex-col gap-0.5 rounded-[20px] bg-white p-1.5 shadow-[0_24px_60px_-12px_rgba(11,46,28,0.22)] ring-1 ring-ligne">
                        <div class="px-3 pt-2 pb-1.5 text-xs font-extrabold tracking-[0.6px] text-mousse uppercase">{{ __('Langue du site') }}</div>
                        @foreach (Langue::CODES as $code)
                            @if ($code === $langue)
                                <span aria-current="true" class="flex items-center gap-3 rounded-[14px] bg-brume px-3 py-2.5 text-foret">
                                    <span class="flex h-7 w-9 items-center justify-center rounded-lg bg-vert text-xs font-extrabold text-white uppercase">{{ $code }}</span>
                                    <span lang="{{ $code }}" class="flex-1 text-[15px] font-bold">{{ Langue::nom($code) }}</span>
                                    <x-icone nom="coche" :epaisseur="2.6" class="size-4 text-vert" />
                                </span>
                            @else
                                <a href="{{ route('langue', $code) }}" hreflang="{{ $code }}" class="group flex items-center gap-3 rounded-[14px] px-3 py-2.5 text-foret hover:bg-brume hover:text-foret">
                                    <span class="flex h-7 w-9 items-center justify-center rounded-lg bg-menthe text-xs font-extrabold text-vert uppercase transition-colors group-hover:bg-vert group-hover:text-white">{{ $code }}</span>
                                    <span lang="{{ $code }}" class="flex-1 text-[15px] font-semibold">{{ Langue::nom($code) }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <span aria-hidden="true" class="mx-1 h-6 w-px bg-ligne"></span>

            @auth
                {{-- Profil : l'initiale et le prénom, le panneau mène à l'espace et permet de se déconnecter. --}}
                <div x-data="deroulant" @focusout="quitter" @keydown.escape.stop="fermer(true)" @click.outside="fermer()" class="relative">
                    <button type="button" x-ref="bouton" @click="basculer" :aria-expanded="ouvert" aria-controls="panneau-profil"
                        class="flex h-11 items-center gap-2 rounded-full ps-1.5 pe-3 text-[15px] font-semibold text-foret transition-colors hover:bg-brume" :class="ouvert && 'bg-brume'">
                        <span class="flex size-8 items-center justify-center rounded-full bg-menthe text-sm font-extrabold text-vert uppercase">{{ mb_substr($utilisateur->name, 0, 1) }}</span>
                        <span class="max-w-32 truncate">{{ Str::before($utilisateur->name, ' ') }}</span>
                        <x-icone nom="bas" :epaisseur="2.4" class="size-3.5 text-mousse transition-transform duration-200" ::class="ouvert && 'rotate-180'" />
                    </button>

                    <div id="panneau-profil" x-show="ouvert" x-cloak {!! $transition !!} class="absolute top-full -end-2 z-40 pt-2">
                        <div class="flex w-64 flex-col gap-0.5 rounded-[20px] bg-white p-1.5 shadow-[0_24px_60px_-12px_rgba(11,46,28,0.22)] ring-1 ring-ligne">
                            <div class="flex items-center gap-3 px-3 pt-2.5 pb-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-vert text-base font-extrabold text-white uppercase">{{ mb_substr($utilisateur->name, 0, 1) }}</span>
                                <span class="flex min-w-0 flex-col">
                                    <span class="truncate text-[15px] font-bold text-foret">{{ $utilisateur->name }}</span>
                                    <span class="truncate text-[13px] font-medium text-lichen">{{ $utilisateur->email }}</span>
                                </span>
                            </div>
                            <span aria-hidden="true" class="mx-2 mb-1 h-px bg-ligne"></span>
                            <a href="{{ route('espace') }}" class="group flex items-center gap-3 rounded-[14px] px-3 py-2.5 text-[15px] font-semibold text-foret hover:bg-brume hover:text-foret">
                                <x-icone nom="maison" class="size-[18px] text-vert" />
                                {{ __('Mon espace') }}
                            </a>
                            @if ($utilisateur->is_admin)
                                <a href="{{ route('admin.questions') }}" class="group flex items-center gap-3 rounded-[14px] px-3 py-2.5 text-[15px] font-semibold text-foret hover:bg-brume hover:text-foret">
                                    <x-icone nom="bouclier" class="size-[18px] text-vert" />
                                    {{ __('Administration') }}
                                </a>
                            @endif
                            <span aria-hidden="true" class="mx-2 my-1 h-px bg-ligne"></span>
                            <form method="POST" action="{{ route('deconnexion') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 rounded-[14px] px-3 py-2.5 text-[15px] font-semibold text-foret hover:bg-brume">
                                    <x-icone nom="sortie" class="size-[18px] text-mousse" />
                                    {{ __('Se déconnecter') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('connexion') }}" wire:navigate class="flex h-11 items-center rounded-full px-4 text-[15px] font-semibold text-foret hover:bg-brume hover:text-foret">{{ __('Connexion') }}</a>
            @endauth
            <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="group ms-1 inline-flex h-12 items-center gap-2 rounded-full bg-vert ps-5 pe-4 text-[15px] font-bold text-white transition hover:-translate-y-0.5 hover:bg-foret hover:text-white hover:shadow-[0_10px_24px_rgba(14,122,69,0.25)]">
                {{ __('Essai gratuit') }}
                <span class="flex size-6 items-center justify-center rounded-full bg-white/15 transition-transform duration-200 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5">
                    <x-icone nom="fleche" :epaisseur="2.4" class="size-3.5" />
                </span>
            </a>
        </div>

        <button type="button" @click="ouvert = true" :aria-expanded="ouvert" aria-controls="menu-mobile" class="flex size-12 items-center justify-center rounded-2xl text-foret hover:bg-brume xl:hidden">
            <span class="sr-only">{{ __('Ouvrir le menu') }}</span>
            <x-icone nom="menu" class="size-6" />
        </button>
    </div>

    <div id="menu-mobile" x-show="ouvert" x-cloak x-trap.inert.noscroll="ouvert" role="dialog" aria-modal="true" aria-label="{{ __('Menu') }}"
        x-transition:enter="transition duration-200 ease-ressort" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed inset-0 flex flex-col overflow-y-auto overscroll-contain bg-white xl:hidden">
        <div class="mx-auto flex h-16 w-full max-w-[1200px] shrink-0 items-center justify-between border-b-[1.5px] border-ligne pe-2 ps-5 md:h-20 md:pe-6 md:ps-10">
            <a href="{{ route('accueil') }}" wire:navigate @click="ouvert = false" aria-label="{{ __('Edumica, accueil') }}" class="flex items-center gap-2.5 text-foret md:gap-3">
                <x-logo class="size-[30px] md:size-9" />
                <span class="font-titre text-xl md:text-2xl">edumica</span>
            </a>
            <button type="button" @click="ouvert = false" class="flex size-12 items-center justify-center rounded-2xl text-foret hover:bg-brume">
                <span class="sr-only">{{ __('Fermer le menu') }}</span>
                <x-icone nom="croix" class="size-6" />
            </button>
        </div>

        <div class="mx-auto flex w-full max-w-xl flex-1 flex-col gap-7 px-4 pt-5 pb-[max(1.5rem,env(safe-area-inset-bottom))] md:px-6">
            <nav aria-label="{{ __('Navigation principale') }}" class="flex flex-col gap-7">
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center justify-between px-1">
                        <span class="text-xs font-extrabold tracking-[0.6px] text-mousse uppercase">{{ __('Épreuves') }}</span>
                        <a href="{{ route('epreuves') }}" wire:navigate @click="ouvert = false" class="flex items-center gap-1 text-sm font-bold text-vert">
                            {{ __('Le guide') }}
                            <x-icone nom="fleche" class="size-4" />
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($epreuves as $epreuve)
                            <a href="{{ route('epreuve', $epreuve) }}" wire:navigate @click="ouvert = false" @if ($epreuveCourante === $epreuve->slug) aria-current="page" @endif
                                class="flex flex-col gap-3 rounded-[18px] bg-brume p-3.5 text-foret hover:text-foret aria-[current=page]:ring-2 aria-[current=page]:ring-vert">
                                <span class="flex size-9 items-center justify-center rounded-full bg-white text-vert">
                                    <x-icone :nom="$epreuve->icone" class="size-[18px]" />
                                </span>
                                <span class="flex flex-col">
                                    <span class="text-[15px] leading-tight font-bold">{{ __($epreuve->nom) }}</span>
                                    <span class="mt-0.5 text-[13px] font-semibold text-lichen tabular-nums">{{ \App\Support\GuideEpreuve::duree($epreuve->code) }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col">
                    @foreach ([...$liens, 'articles' => __('Articles'), 'faq' => __('FAQ'), 'contact' => __('Contact')] as $route => $libelle)
                        <a href="{{ route($route) }}" wire:navigate @click="ouvert = false" @if ($actif($route, $route === 'articles' ? 'article' : $route)) aria-current="page" @endif
                            class="flex min-h-14 items-center justify-between border-b border-ligne/70 px-1 text-lg font-semibold text-foret last:border-0 aria-[current=page]:text-vert">
                            {{ $libelle }}
                            <x-icone nom="droite" class="size-5 text-mousse" />
                        </a>
                    @endforeach
                </div>
            </nav>

            <div class="mt-auto flex flex-col gap-3">
                {{-- Langue : les deux choix visibles côte à côte. --}}
                <div role="group" aria-label="{{ __('Langue du site') }}" class="grid grid-cols-2 gap-1 rounded-2xl bg-brume p-1">
                    @foreach (Langue::CODES as $code)
                        @if ($code === $langue)
                            <span aria-current="true" lang="{{ $code }}" class="flex h-12 items-center justify-center gap-2 rounded-xl bg-white text-[16px] font-bold text-foret shadow-[0_1px_2px_rgba(11,46,28,0.08)]">
                                <x-icone nom="coche" :epaisseur="2.6" class="size-4 text-vert" />
                                {{ Langue::nom($code) }}
                            </span>
                        @else
                            <a href="{{ route('langue', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}" class="flex h-12 items-center justify-center rounded-xl text-[16px] font-semibold text-mousse-fonce hover:bg-white/60 hover:text-foret">{{ Langue::nom($code) }}</a>
                        @endif
                    @endforeach
                </div>

                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.questions') }}" @click="ouvert = false" class="flex h-14 items-center justify-center rounded-2xl border-[1.5px] border-ligne text-[17px] font-semibold text-foret hover:bg-brume">Admin</a>
                    @endif
                    <div class="grid grid-cols-[1fr_auto] gap-2">
                        <a href="{{ route('espace') }}" @click="ouvert = false" class="flex h-14 items-center justify-center gap-2.5 rounded-2xl border-[1.5px] border-ligne text-[17px] font-semibold text-foret hover:bg-brume">
                            <span class="flex size-8 items-center justify-center rounded-full bg-menthe text-sm font-extrabold text-vert uppercase">{{ mb_substr($utilisateur->name, 0, 1) }}</span>
                            {{ __('Mon espace') }}
                        </a>
                        <form method="POST" action="{{ route('deconnexion') }}">
                            @csrf
                            <button type="submit" class="flex size-14 items-center justify-center rounded-2xl border-[1.5px] border-ligne text-foret hover:bg-brume">
                                <span class="sr-only">{{ __('Se déconnecter') }}</span>
                                <x-icone nom="sortie" class="size-5" />
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('connexion') }}" wire:navigate @click="ouvert = false" class="flex h-14 items-center justify-center rounded-2xl border-[1.5px] border-ligne text-[17px] font-semibold text-foret hover:bg-brume">{{ __('Connexion') }}</a>
                @endauth
                <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate @click="ouvert = false" class="flex h-14 items-center justify-center gap-2 rounded-2xl bg-vert text-[17px] font-bold text-white hover:bg-foret hover:text-white">
                    {{ __('Essai gratuit') }}
                    <x-icone nom="fleche" :epaisseur="2.4" class="size-4" />
                </a>
            </div>
        </div>
    </div>
</header>
