<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Connexion des utilisateurs par Google (Socialite) : le premier passage crée le
 * compte. La connexion de démonstration (compte fictif) reste disponible en local.
 * L'admin, lui, se connecte par courriel + mot de passe (pages::admin-connexion).
 */
class ConnexionController extends Controller
{
    /** Comptes fictifs : rôle => [nom, courriel]. Jamais admin. */
    public const COMPTES = [
        'utilisateur' => ['Amina Diallo', 'amina@demo.edumica.test'],
    ];

    public function google(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return to_route('connexion')->with('erreur', "La connexion Google n'est pas encore configurée.");
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Retour de Google : retrouve le compte par google_id, sinon par courriel
     * vérifié (on y rattache Google), sinon le crée. Jamais le compte admin.
     */
    public function retourGoogle(Request $request): RedirectResponse
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);

            return to_route('connexion')->with('erreur', 'La connexion Google a échoué ou a été annulée. Réessaie.');
        }

        $courrielVerifie = (bool) ($google->user['email_verified'] ?? false);

        $user = User::where('google_id', $google->getId())->first()
            ?? ($courrielVerifie ? User::where('email', $google->getEmail())->first() : null);

        if ($user?->is_admin) {
            return to_route('connexion')->with('erreur', "Ce compte se connecte par l'espace administrateur.");
        }

        if (! $user && ! $courrielVerifie) {
            return to_route('connexion')->with('erreur', "Ton adresse Google n'est pas vérifiée : impossible de créer le compte.");
        }

        $user ??= new User([
            'email' => $google->getEmail(),
            'password' => str()->random(32),
            'is_admin' => false,
        ]);

        $user->fill([
            'name' => $user->name ?: ($google->getName() ?: str($google->getEmail())->before('@')),
            'google_id' => $google->getId(),
            'avatar' => $google->getAvatar(),
        ]);
        $user->email_verified_at ??= now();
        $user->save();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('espace'));
    }

    public function demo(Request $request, string $role): RedirectResponse
    {
        abort_unless(app()->environment('local', 'testing') && isset(self::COMPTES[$role]), 404);

        [$nom, $email] = self::COMPTES[$role];

        $user = User::firstOrCreate(['email' => $email], [
            'name' => $nom,
            'password' => str()->random(32),
            'is_admin' => false,
            'google_id' => 'demo-'.$role,
        ]);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('espace'));
    }

    /** Depuis un dashboard Inertia, l'accueil (Livewire) est chargé entièrement. */
    public function deconnexion(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(route('accueil'));
    }
}
