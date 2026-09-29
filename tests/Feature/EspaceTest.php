<?php

namespace Tests\Feature;

use App\Models\Epreuve;
use App\Models\Tentative;
use App\Models\User;
use Database\Seeders\EpreuveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EpreuveSeeder::class);
    }

    public function test_la_connexion_demo_connecte_un_utilisateur_ou_un_admin(): void
    {
        $this->get('/espace')->assertRedirect('/connexion');
        $this->get('/connexion')->assertOk()->assertSee('Continuer avec Google');

        $this->post('/connexion/demo/utilisateur')->assertRedirect('/espace');
        $this->assertAuthenticated();
        $this->assertFalse(auth()->user()->is_admin);
        $this->get('/espace')->assertOk()->assertInertia(fn (Assert $page) => $page->component('espace/index'));
        $this->get('/admin')->assertForbidden();

        $this->post('/deconnexion')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/connexion/demo/admin')->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page->component('admin/questions/index'));
        $this->post('/connexion/demo/pirate')->assertRedirect('/espace'); // déjà connecté
    }

    public function test_un_role_inconnu_est_refuse(): void
    {
        $this->post('/connexion/demo/pirate')->assertNotFound();
    }

    public function test_la_deconnexion_depuis_un_dashboard_recharge_l_accueil(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/deconnexion', [], ['X-Inertia' => 'true'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('accueil'));

        $this->assertGuest();
    }

    public function test_une_session_expiree_recharge_la_page_de_connexion(): void
    {
        $this->get('/espace', ['X-Inertia' => 'true'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('connexion'));
    }

    public function test_le_layout_recoit_l_utilisateur_et_les_messages_flash(): void
    {
        $user = User::factory()->create(['name' => 'Amina Diallo', 'email' => 'amina@exemple.test', 'is_admin' => true]);
        $tentative = Tentative::demarrer($user, Epreuve::where('code', 'co')->first());
        $this->actingAs($user);

        // Abandonner un test redirige vers l'espace avec un message flash.
        $this->delete(route('test-blanc.abandonner', $tentative))->assertRedirect(route('espace'));

        $this->withUnencryptedCookie('sidebar_state', 'false')
            ->get('/espace')
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user', ['name' => 'Amina Diallo', 'email' => 'amina@exemple.test', 'initiales' => 'AD', 'is_admin' => true])
                ->where('flash.succes', 'Test abandonné : tes réponses n\'ont pas été gardées.')
                ->where('flash.erreur', null)
                ->has('flash.id')
                ->where('sidebarOpen', false)
                ->where('navigation.epreuves', []));

        $this->withUnencryptedCookie('sidebar_state', 'true')
            ->get('/espace')
            ->assertInertia(fn (Assert $page) => $page->where('flash', null)->where('sidebarOpen', true));
    }

    public function test_le_tableau_de_bord_resume_les_tests_passes(): void
    {
        $user = User::factory()->create();
        Tentative::factory()->for($user)->terminee(bonnes: 6, total: 10, niveau: '7')->create(['terminee_le' => now()->subDay()]);
        Tentative::factory()->for($user)->terminee(bonnes: 9, total: 10)->create(['terminee_le' => now()]);
        Tentative::factory()->for($user)->create(); // en cours : ne compte pas
        Tentative::factory()->terminee(bonnes: 0, total: 10)->create(); // un autre utilisateur

        $this->actingAs($user)->get('/espace')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('espace/index')
            ->where('statistiques.tests', 2)
            ->where('statistiques.scoreMoyen', 75)
            ->where('statistiques.dernierTest.niveau', null)
            ->has('statistiques.dernierTest.le'));
    }

    public function test_sans_test_passe_les_statistiques_sont_vides(): void
    {
        $this->actingAs(User::factory()->create())->get('/espace')->assertInertia(fn (Assert $page) => $page
            ->where('statistiques', ['tests' => 0, 'scoreMoyen' => null, 'dernierTest' => null]));
    }

    public function test_le_catalogue_propose_le_test_complet_et_les_epreuves(): void
    {
        $user = User::factory()->create();
        $comprehensionOrale = Epreuve::where('code', 'co')->first();
        Tentative::factory()->for($user)->create(['epreuve_id' => $comprehensionOrale->id]);

        $this->actingAs($user)->get('/espace/tests-blancs')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('espace/tests-blancs')
            ->where('complet.epreuves', ['Compréhension orale', 'Compréhension écrite'])
            ->where('complet.questions', $comprehensionOrale->questions()->count() + Epreuve::where('code', 'ce')->first()->questions()->count())
            ->where('complet.duree', 35 + 60)
            ->where('complet.enCours', false)
            ->has('epreuves', 4)
            ->where('epreuves.0.slug', 'comprehension-orale')
            ->where('epreuves.0.enCours', true)
            ->where('epreuves.3.questions', 0));
    }

    public function test_la_page_des_resultats_est_un_placeholder(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/espace/resultats')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('espace/resultats'));
    }
}
