<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Connexion de démonstration : en attendant Google (Socialite), on connecte
 * un compte fictif. Remplacer demo() par la redirection / le callback Google.
 */
class ConnexionController extends Controller
{
    /** Comptes fictifs : rôle => [nom, courriel, admin]. */
    public const COMPTES = [
        'utilisateur' => ['Amina Diallo', 'amina@demo.edumica.test', false],
        'admin' => ['Admin Edumica', 'admin@demo.edumica.test', true],
    ];

    public function demo(Request $request, string $role): RedirectResponse
    {
        abort_unless(isset(self::COMPTES[$role]), 404);

        [$nom, $email, $admin] = self::COMPTES[$role];

        $user = User::firstOrCreate(['email' => $email], [
            'name' => $nom,
            'password' => str()->random(32),
            'is_admin' => $admin,
            'google_id' => 'demo-'.$role,
        ]);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended($user->is_admin ? route('admin.questions') : route('espace'));
    }

    public function deconnexion(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('accueil');
    }
}
