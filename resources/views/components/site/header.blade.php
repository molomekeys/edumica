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

<header x-data="{ ouvert: false, defile: false }" @keydown.escape.window="ouvert = false"
    @if ($collant) x-init="defile = scrollY > 8" @scroll.window.passive="defile = scrollY > 8" :class="defile && 'bg-white/85 shadow-[0_1px_0_var(--color-ligne),0_12px_32px_rgba(11,46,28,0.06)] backdrop-blur-md'" @endif
    @class(['z-30', 'sticky top-0 transition-[background-color,box-shadow] duration-300' => $collant, 'relative' => ! $collant])>
    <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between pr-2 pl-5 md:h-20 md:px-10 xl:px-0">
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

        <div class="flex items-center md:gap-2">
            @auth
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.questions') }}" wire:navigate class="px-2.5 py-3 text-[15px] font-semibold text-foret md:px-4 md:text-base">Admin</a>
                @endif
                <a href="{{ route('espace') }}" wire:navigate class="px-2.5 py-3 text-[15px] font-semibold text-foret md:px-4 md:text-base">Mon espace</a>
            @else
                <a href="{{ route('connexion') }}" wire:navigate class="px-2.5 py-3 text-[15px] font-semibold text-foret md:px-4 md:text-base">Connexion</a>
            @endauth
            <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="hidden rounded-[14px] bg-vert px-[22px] py-3.5 text-base font-bold text-white transition hover:-translate-y-0.5 hover:bg-foret hover:shadow-[0_10px_24px_rgba(14,122,69,0.25)] md:inline-flex">Essai gratuit</a>
            <button type="button" @click="ouvert = !ouvert" :aria-expanded="ouvert" aria-controls="menu-mobile" class="flex size-12 items-center justify-center lg:hidden">
                <span class="sr-only" x-text="ouvert ? 'Fermer le menu' : 'Ouvrir le menu'">Ouvrir le menu</span>
                <x-icone nom="menu" class="size-6" x-show="!ouvert" />
                <x-icone nom="croix" class="size-6" x-show="ouvert" x-cloak />
            </button>
        </div>
    </div>

    <nav id="menu-mobile" x-show="ouvert" x-cloak x-transition.opacity.duration.150ms @click.outside="ouvert = false"
        aria-label="Menu" class="absolute inset-x-3 top-full rounded-3xl border-[1.5px] border-ligne bg-white p-3 shadow-[0_16px_40px_rgba(11,46,28,0.12)] lg:hidden">
        @foreach ($liens as $route => $libelle)
            <a href="{{ route($route) }}" wire:navigate @click="ouvert = false" @if ($actif($route)) aria-current="page" @endif
                @class(['flex min-h-12 items-center justify-between rounded-2xl px-4 text-[17px] font-semibold text-foret hover:bg-brume', 'bg-brume' => $actif($route)])>
                {{ $libelle }}
                <x-icone nom="droite" class="size-5" />
            </a>
        @endforeach
        <a href="{{ route('quiz', 'comprehension-orale') }}" wire:navigate class="mt-2 flex h-14 items-center justify-center rounded-2xl bg-foret text-[17px] font-bold text-white hover:text-white">Faire un quiz gratuit</a>
    </nav>
</header>
