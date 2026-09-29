<?php

namespace Database\Seeders;

use App\Http\Controllers\ConnexionController;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Comptes fictifs utilisés par la connexion de démonstration.
 */
class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ConnexionController::COMPTES as $role => [$nom, $email, $admin]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $nom,
                'password' => str()->random(32),
                'is_admin' => $admin,
                'google_id' => 'demo-'.$role,
            ]);
        }
    }
}
