<?php

namespace Tests\Feature;

use App\Models\Epreuve;
use App\Models\Question;
use App\Models\User;
use Database\Seeders\EpreuveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $this->get('/espace')->assertOk()->assertSee('Tests blancs')->assertSee('Commencer');
        $this->get('/admin')->assertForbidden();

        $this->post('/deconnexion')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/connexion/demo/admin')->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Nouvelle question');
        $this->post('/connexion/demo/pirate')->assertRedirect('/espace'); // déjà connecté
    }

    public function test_un_role_inconnu_est_refuse(): void
    {
        $this->post('/connexion/demo/pirate')->assertNotFound();
    }

    public function test_le_test_blanc_se_deroule_jusqu_aux_resultats(): void
    {
        $this->actingAs(User::factory()->create());
        $epreuve = Epreuve::where('code', 'co')->first();

        $this->get(route('test-blanc', $epreuve))->assertOk()->assertSee('Vie quotidienne')->assertSee('Une seule écoute');

        $test = Livewire::test('pages::test-blanc', ['epreuve' => $epreuve]);
        $ids = $test->get('ids');

        // Les questions sont regroupées par catégorie.
        $categories = Question::findMany($ids)->sortBy(fn ($q) => array_search($q->id, $ids))->pluck('categorie')->values();
        $this->assertSame($categories->all(), $categories->groupBy(fn ($c) => $c)->flatten()->all());

        $premiere = Question::find($ids[0]);
        $test->call('choisir', $premiere->bonne_reponse)
            ->assertSet('reponses', [0 => $premiere->bonne_reponse])
            ->call('basculerMarque')->assertSet('marquees', [0])
            ->call('marquerEcoutee', 0)->assertSet('ecoutees', [0])
            ->call('aller', 3)->assertSet('index', 3)
            ->call('precedente')->assertSet('index', 2)
            ->call('aller', 99)->assertSet('index', 2)
            ->call('terminer')
            ->assertSet('termine', true)
            ->assertSee('Test blanc terminé')
            ->assertSee('Par catégorie')
            ->call('choisir', 0)
            ->assertSet('reponses', [0 => $premiere->bonne_reponse]);
    }

    public function test_le_test_complet_enchaine_toutes_les_epreuves(): void
    {
        $this->actingAs(User::factory()->create());
        $avecQuestions = Epreuve::has('questions')->orderBy('ordre')->get();

        $this->get('/espace')->assertOk()->assertSee('Commencer le test complet');
        $this->get(route('test-blanc.complet'))->assertOk()->assertSee('Toutes les épreuves');

        $test = Livewire::test('pages::test-blanc');
        $ids = $test->get('ids');

        // Toutes les questions, épreuve par épreuve, avec un chrono qui cumule leurs durées.
        $this->assertCount(Question::count(), $ids);
        $ordre = Question::findMany($ids)->sortBy(fn ($q) => array_search($q->id, $ids))->pluck('epreuve_id')->unique()->values();
        $this->assertSame($avecQuestions->pluck('id')->all(), $ordre->all());
        $this->assertEqualsWithDelta(now()->addMinutes($avecQuestions->sum('duree_test'))->getTimestamp(), $test->get('fin'), 2);

        $premiere = Question::find($ids[0]);
        $test->call('choisir', $premiere->bonne_reponse)
            ->call('terminer')
            ->assertSee('Par épreuve')
            ->assertSee($avecQuestions->last()->nom);

        $this->assertSame(1, $test->instance()->resultat['bonnes']);
        $this->assertSame($avecQuestions->pluck('id')->all(), array_keys($test->instance()->resultat['epreuves']));
    }

    public function test_abandonner_le_test_ramene_a_l_espace(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::test-blanc', ['epreuve' => Epreuve::where('code', 'ce')->first()])
            ->assertSee('Abandonner')
            ->assertDontSee('Quitter le test blanc')
            ->call('abandonner')
            ->assertRedirect(route('espace'));
    }

    public function test_l_admin_modifie_et_cree_des_questions(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $question = Question::first();

        $this->get(route('admin.questions.modifier', $question))->assertOk()->assertSee('Modifier la question');

        Livewire::test('pages::admin.question', ['question' => $question])
            ->assertSet('enonce', $question->enonce)
            ->assertSet('choix', $question->choix)
            ->set('enonce', 'Énoncé modifié')
            ->set('categorie', 'Nouvelle catégorie')
            ->call('ajouterChoix')
            ->set('choix.4', 'Cinquième choix')
            ->set('bonne_reponse', 4)
            ->call('enregistrer')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.questions'));

        $question->refresh();
        $this->assertSame('Énoncé modifié', $question->enonce);
        $this->assertSame('Nouvelle catégorie', $question->categorie);
        $this->assertSame('Cinquième choix', $question->choix[4]);
        $this->assertSame(4, $question->bonne_reponse);

        Livewire::test('pages::admin.question')
            ->set('categorie', 'Test')
            ->set('enonce', 'Nouvelle question ?')
            ->set('choix', ['Oui', ''])
            ->set('explication', 'Parce que.')
            ->call('enregistrer')
            ->assertHasErrors(['choix.1']);

        $avant = Question::count();
        Livewire::test('pages::admin.questions')->call('supprimer', $question->id)->assertSee('Question supprimée.');
        $this->assertSame($avant - 1, Question::count());
    }

    public function test_le_panel_admin_est_reserve_aux_admins(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertForbidden();
        $this->get(route('admin.questions.creer'))->assertForbidden();
    }
}
