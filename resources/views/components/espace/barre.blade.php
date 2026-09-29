{{-- Barre du haut des espaces connectés (utilisateur et admin). --}}
@php($user = auth()->user())

<header class="border-b-[1.5px] border-ligne bg-white">
    <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between gap-3 px-4 md:px-8">
        <a href="{{ route('accueil') }}" wire:navigate aria-label="Edumica, accueil" class="flex items-center gap-2.5 text-foret">
            <x-logo class="size-[30px]" />
            <span class="hidden font-titre text-xl sm:inline">edumica</span>
        </a>

        <nav aria-label="Navigation de l'espace" class="flex items-center gap-1 text-[15px] font-semibold">
            <a href="{{ route('espace') }}" wire:navigate @class(['rounded-xl px-3 py-2 text-foret', 'bg-brume' => request()->routeIs('espace')])>Mon espace</a>
            @if ($user->is_admin)
                <a href="{{ route('admin.questions') }}" wire:navigate @class(['rounded-xl px-3 py-2 text-foret', 'bg-brume' => request()->routeIs('admin.*')])>Admin</a>
            @endif
        </nav>

        <div class="flex items-center gap-2">
            <div class="hidden size-9 items-center justify-center rounded-full bg-menthe text-sm font-bold text-foret sm:flex" title="{{ $user->name }}">{{ $user->initiales() }}</div>
            <form method="POST" action="{{ route('deconnexion') }}">
                @csrf
                <button type="submit" class="rounded-xl px-3 py-2 text-sm font-semibold text-mousse hover:bg-brume hover:text-foret">Déconnexion</button>
            </form>
        </div>
    </div>
</header>
