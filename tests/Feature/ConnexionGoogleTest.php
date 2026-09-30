<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\EpreuveSeeder;
use Database\Seeders\UtilisateurSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as UtilisateurGoogle;
use Tests\TestCase;

class ConnexionGoogleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EpreuveSeeder::class);
        config(['services.google.client_id' => 'id-client-test', 'services.google.client_secret' => 'secret-test']);
    }

    private function googleRenvoie(string $email, bool $verifie = true, string $id = 'google-123'): void
    {
        $google = (new UtilisateurGoogle)
            ->setRaw(['email_verified' => $verifie])
            ->map(['id' => $id, 'name' => 'Fatou Ndiaye', 'email' => $email, 'avatar' => 'https://lh3.googleusercontent.com/a/photo']);

        Socialite::shouldReceive('driver->user')->andReturn($google);
    }

    public function test_le_bouton_redirige_vers_google(): void
    {
        $this->get('/connexion/google')->assertRedirectContains('accounts.google.com');
    }

    public function test_sans_configuration_google_on_revient_a_la_connexion(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/connexion/google')->assertRedirect('/connexion')->assertSessionHas('erreur');
    }

    public function test_le_premier_passage_cree_le_compte(): void
    {
        $this->googleRenvoie('fatou@gmail.com');

        $this->get('/connexion/google/retour')->assertRedirect('/espace');

        $user = User::where('email', 'fatou@gmail.com')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Fatou Ndiaye', $user->name);
        $this->assertSame('google-123', $user->google_id);
        $this->assertFalse($user->is_admin);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_le_passage_suivant_reconnecte_le_meme_compte(): void
    {
        $user = User::factory()->create(['google_id' => 'google-123', 'name' => 'Fatou N.']);
        $this->googleRenvoie('nouvelle-adresse@gmail.com');

        $this->get('/connexion/google/retour')->assertRedirect('/espace');

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
        $this->assertSame('Fatou N.', $user->fresh()->name);
    }

    public function test_un_compte_existant_au_meme_courriel_est_rattache(): void
    {
        $user = User::factory()->create(['email' => 'fatou@gmail.com', 'google_id' => null]);
        $this->googleRenvoie('fatou@gmail.com');

        $this->get('/connexion/google/retour')->assertRedirect('/espace');

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-123', $user->fresh()->google_id);
    }

    public function test_un_courriel_non_verifie_ne_cree_ni_ne_rattache_de_compte(): void
    {
        User::factory()->create(['email' => 'fatou@gmail.com', 'google_id' => null]);
        $this->googleRenvoie('fatou@gmail.com', verifie: false);

        $this->get('/connexion/google/retour')->assertRedirect('/connexion')->assertSessionHas('erreur');

        $this->assertGuest();
        $this->assertNull(User::where('email', 'fatou@gmail.com')->value('google_id'));
    }

    public function test_google_ne_connecte_jamais_le_compte_admin(): void
    {
        $this->seed(UtilisateurSeeder::class);
        $this->googleRenvoie(config('auth.admin.email'));

        $this->get('/connexion/google/retour')->assertRedirect('/connexion')->assertSessionHas('erreur');

        $this->assertGuest();
    }

    public function test_une_connexion_annulee_revient_a_la_connexion(): void
    {
        Socialite::shouldReceive('driver->user')->andThrow(new \RuntimeException('access_denied'));

        $this->get('/connexion/google/retour')->assertRedirect('/connexion')->assertSessionHas('erreur');

        $this->assertGuest();
    }
}
