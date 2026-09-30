<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Connexion de l'unique compte admin, par courriel + mot de passe.
 * Un compte non admin est refusé comme un mauvais mot de passe.
 */
new class extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public function connecter()
    {
        $this->validate();

        $cle = 'admin-connexion|'.Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($cle, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Trop de tentatives. Réessaie dans '.RateLimiter::availableIn($cle).' secondes.',
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password, 'is_admin' => true])) {
            RateLimiter::hit($cle);
            $this->reset('password');

            throw ValidationException::withMessages(['email' => 'Courriel ou mot de passe incorrect.']);
        }

        RateLimiter::clear($cle);
        session()->regenerate();

        return redirect()->intended(route('admin.questions'));
    }

    public function render()
    {
        return $this->view()->title('Connexion admin');
    }
};
?>

<div class="flex min-h-dvh flex-col bg-brume">
    <x-site.header />

    <main class="flex flex-1 items-center justify-center px-4 py-10">
        <form wire:submit="connecter" class="flex w-full max-w-md flex-col gap-5 rounded-[28px] border-[1.5px] border-ligne bg-white p-6 md:p-8">
            <div class="flex flex-col gap-2 text-center">
                <x-logo class="mx-auto size-12" />
                <h1 class="font-titre text-[26px] leading-tight tracking-[-0.5px]">Espace administrateur</h1>
                <p class="text-[15px] text-mousse">Réservé au compte admin d'Edumica.</p>
            </div>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-bold text-foret">Courriel</span>
                <input type="email" wire:model="email" autocomplete="username" required autofocus class="h-12 rounded-xl border-[1.5px] border-ligne px-4 text-base text-foret focus:border-foret focus:outline-none" />
                @error('email') <span class="text-sm font-semibold text-red-700">{{ $message }}</span> @enderror
            </label>

            <label class="flex flex-col gap-2">
                <span class="text-sm font-bold text-foret">Mot de passe</span>
                <input type="password" wire:model="password" autocomplete="current-password" required class="h-12 rounded-xl border-[1.5px] border-ligne px-4 text-base text-foret focus:border-foret focus:outline-none" />
                @error('password') <span class="text-sm font-semibold text-red-700">{{ $message }}</span> @enderror
            </label>

            <button type="submit" wire:loading.attr="disabled" class="h-12 w-full rounded-2xl bg-foret text-base font-bold text-white hover:bg-vert disabled:opacity-60">Se connecter</button>

            <a href="{{ route('connexion') }}" wire:navigate class="text-center text-sm font-semibold text-mousse hover:text-foret">Retour à la connexion</a>
        </form>
    </main>
</div>
