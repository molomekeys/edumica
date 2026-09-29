@props(['collant' => false])

@php
    $liens = [
        'epreuves' => 'Épreuves',
        'tests-blancs' => 'Tests blancs',
        'scores' => 'Scores NCLC',
        'tarifs' => 'Tarifs',
        'faq' => 'FAQ',
    ];

    $actif = fn (string $route) => request()->routeIs($route) || ($route === 'epreuves' && request()->routeIs('epreuve'));
@endphp

{{-- Sous lg : logo + bouton menu ; le menu s'ouvre en plein écran (focus piégé, défilement bloqué). --}}
<header x-data="{ ouvert: false, defile: false }" @keydown.escape.window="ouvert = false" @resize.window="innerWidth >= 1024 && (ouvert = false)"
    @if ($collant) x-init="defile = scrollY > 8" @scroll.window.passive="defile = scrollY > 8" :class="{ 'bg-white/85 shadow-[0_1px_0_var(--color-ligne),0_12px_32px_rgba(11,46,28,0.06)] backdrop-blur-md': defile && ! ouvert, 'z-50': ouvert }" @else :class="ouvert && 'z-50'" @endif
    @class(['z-30', 'sticky top-0 transition-[background-color,box-shadow] duration-300' => $collant, 'relative' => ! $collant])>
    <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between pr-2 pl-5 md:h-20 md:pr-6 md:pl-10 lg:px-10 xl:px-0">
        <a href="{{ route('accueil') }}" wire:navigate aria-label="Edumica, accueil" class="flex items-center gap-2.5 text-foret md:gap-3">
            <x-logo class="size-[30px] md:size-9" />
            <span class="font-titre text-xl md:text-2xl">edumica</span>
        </a>

        <nav aria-label="Navigation principale" class="hidden gap-2 text-base font-semibold lg:flex">
            @foreach ($liens as $route => $libelle)
                <a href="{{ route($route) }}" wire:navigate @if ($actif($route)) aria-current="page" @endif
                    @class(['group relative px-3.5 py-3', 'text-foret' => ! $actif($route), 'text-vert' => $actif($route)])>
                    {{ $libelle }}
                    <span aria-hidden="true" @class(['absolute inset-x-3.5 bottom-2 h-0.5 origin-left rounded-full bg-vert transition-transform duration-300 ease-ressort', 'scale-x-0 group-hover:scale-x-100' => ! $actif($route)])></span>
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-2 lg:flex">
            @auth
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.questions') }}" wire:navigate class="px-4 py-3 text-base font-semibold text-foret">Admin</a>
                @endif
                <a href="{{ route('espace') }}" wire:navigate class="px-4 py-3 text-base font-semibold text-foret">Mon espace</a>
            @else
                <a href="{{ route('connexion') }}" wire:navigate class="px-4 py-3 text-base font-semibold text-foret">Connexion</a>
            @endauth
            <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="inline-flex rounded-[14px] bg-vert px-[22px] py-3.5 text-base font-bold text-white transition hover:-translate-y-0.5 hover:bg-foret hover:text-white hover:shadow-[0_10px_24px_rgba(14,122,69,0.25)]">Essai gratuit</a>
        </div>

        <button type="button" @click="ouvert = true" :aria-expanded="ouvert" aria-controls="menu-mobile" class="flex size-12 items-center justify-center rounded-2xl text-foret hover:bg-brume lg:hidden">
            <span class="sr-only">Ouvrir le menu</span>
            <x-icone nom="menu" class="size-6" />
        </button>
    </div>

    <div id="menu-mobile" x-show="ouvert" x-cloak x-trap.inert.noscroll="ouvert" role="dialog" aria-modal="true" aria-label="Menu"
        x-transition:enter="transition duration-200 ease-ressort" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed inset-0 flex flex-col overflow-y-auto overscroll-contain bg-white lg:hidden">
        <div class="mx-auto flex h-16 w-full max-w-[1200px] shrink-0 items-center justify-between border-b-[1.5px] border-ligne pr-2 pl-5 md:h-20 md:pr-6 md:pl-10">
            <a href="{{ route('accueil') }}" wire:navigate @click="ouvert = false" aria-label="Edumica, accueil" class="flex items-center gap-2.5 text-foret md:gap-3">
                <x-logo class="size-[30px] md:size-9" />
                <span class="font-titre text-xl md:text-2xl">edumica</span>
            </a>
            <button type="button" @click="ouvert = false" class="flex size-12 items-center justify-center rounded-2xl text-foret hover:bg-brume">
                <span class="sr-only">Fermer le menu</span>
                <x-icone nom="croix" class="size-6" />
            </button>
        </div>

        <div class="mx-auto flex w-full max-w-xl flex-1 flex-col gap-6 px-3 pt-4 pb-[max(1.5rem,env(safe-area-inset-bottom))] md:px-6">
            <nav aria-label="Navigation principale" class="flex flex-col gap-1">
                @foreach ($liens as $route => $libelle)
                    <a href="{{ route($route) }}" wire:navigate @click="ouvert = false" @if ($actif($route)) aria-current="page" @endif
                        @class(['flex min-h-14 items-center justify-between rounded-2xl px-4 text-lg font-semibold hover:bg-brume', 'text-foret' => ! $actif($route), 'bg-brume text-vert' => $actif($route)])>
                        {{ $libelle }}
                        <x-icone nom="droite" class="size-5" />
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto flex flex-col gap-3 border-t-[1.5px] border-ligne pt-6">
                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.questions') }}" wire:navigate @click="ouvert = false" class="flex h-14 items-center justify-center rounded-2xl border-[1.5px] border-ligne text-[17px] font-semibold text-foret hover:bg-brume">Admin</a>
                    @endif
                    <a href="{{ route('espace') }}" wire:navigate @click="ouvert = false" class="flex h-14 items-center justify-center rounded-2xl border-[1.5px] border-ligne text-[17px] font-semibold text-foret hover:bg-brume">Mon espace</a>
                @else
                    <a href="{{ route('connexion') }}" wire:navigate @click="ouvert = false" class="flex h-14 items-center justify-center rounded-2xl border-[1.5px] border-ligne text-[17px] font-semibold text-foret hover:bg-brume">Connexion</a>
                @endauth
                <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate @click="ouvert = false" class="flex h-14 items-center justify-center rounded-2xl bg-vert text-[17px] font-bold text-white hover:bg-foret hover:text-white">Essai gratuit</a>
            </div>
        </div>
    </div>
</header>
