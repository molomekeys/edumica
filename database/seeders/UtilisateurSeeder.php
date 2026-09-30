<?php

namespace Database\Seeders;

use App\Http\Controllers\ConnexionController;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Comptes fictifs de la connexion de démonstration, plus l'unique compte admin
 * (config('auth.admin')). Tout autre compte perd le rôle admin.
 */
class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ConnexionController::COMPTES as $role => [$nom, $email]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $nom,
                'password' => str()->random(32),
                'is_admin' => false,
                'google_id' => 'demo-'.$role,
            ]);
        }

        $admin = config('auth.admin');

        User::where('is_admin', true)->where('email', '!=', $admin['email'])->update(['is_admin' => false]);

        User::updateOrCreate(['email' => $admin['email']], [
            'name' => $admin['name'],
            'password' => $admin['password'],
            'is_admin' => true,
            'google_id' => null,
        ]);
    }
}
