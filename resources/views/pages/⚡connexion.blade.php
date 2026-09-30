<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()->title(__('Connexion'));
    }
};
?>

<div class="flex min-h-dvh flex-col bg-brume">
    <x-site.header />

    <main class="flex flex-1 items-center justify-center px-4 py-10">
        <div class="flex w-full max-w-md flex-col gap-6 rounded-[28px] border-[1.5px] border-ligne bg-white p-6 md:p-8">
            <div class="flex flex-col gap-2 text-center">
                <x-logo class="mx-auto size-12" />
                <h1 class="font-titre text-[26px] leading-tight tracking-[-0.5px]">{{ __('Connexion') }}</h1>
                <p class="text-[15px] text-mousse">{{ __('Connecte-toi pour passer tes tests blancs et suivre ton niveau.') }}</p>
            </div>

            @if (session('erreur'))
                <p role="alert" class="rounded-2xl bg-red-50 p-4 text-sm font-semibold text-red-700">{{ __(session('erreur')) }}</p>
            @endif

            {{-- Premier passage : Google crée le compte --}}
            <a href="{{ route('connexion.google') }}" class="flex h-14 w-full items-center justify-center gap-3 rounded-2xl border-[1.5px] border-foret bg-white text-base font-bold text-foret hover:bg-brume">
                <svg class="size-5" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/>
                    <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
                    <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
                    <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/>
                </svg>
                {{ __('Continuer avec Google') }}
            </a>

            <p class="text-center text-[13px] text-mousse">{{ __('Pas encore de compte ? Il est créé automatiquement à ta première connexion.') }}</p>

            @if (app()->isLocal())
                <div class="flex flex-col gap-3 rounded-2xl bg-peche-clair p-4">
                    <div class="text-[13px] font-bold tracking-wide text-mousse-fonce uppercase">{{ __('Mode démo') }}</div>
                    <p class="text-sm leading-normal text-foret">{{ __('Visible seulement en local : connecte un compte fictif sans passer par Google.') }}</p>
                    <form method="POST" action="{{ route('connexion.demo', 'utilisateur') }}">
                        @csrf
                        <button type="submit" class="h-11 w-full rounded-xl bg-foret px-4 text-sm font-bold text-white hover:bg-vert">{{ __('Compte utilisateur') }}</button>
                    </form>
                </div>
            @endif

            <a href="{{ route('admin.connexion') }}" wire:navigate class="text-center text-sm font-semibold text-mousse hover:text-foret">{{ __('Espace administrateur') }}</a>
        </div>
    </main>
</div>
