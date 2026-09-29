<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\QuestionController;
use App\Models\Epreuve;
use App\Models\Question;
use App\Models\User;
use Database\Seeders\EpreuveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminQuestionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EpreuveSeeder::class);
    }

    public function test_le_panel_admin_est_reserve_aux_admins(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertForbidden();
        $this->get(route('admin.questions.creer'))->assertForbidden();
        $this->post(route('admin.questions.enregistrer'), $this->donnees())->assertForbidden();
        $this->delete(route('admin.questions.supprimer', Question::first()))->assertForbidden();
    }

    public function test_la_liste_est_paginee_et_filtree(): void
    {
        $this->actingAs($this->admin());
        $comprehensionEcrite = Epreuve::where('code', 'ce')->first();

        $this->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('admin/questions/index')
            ->has('questions.data', min(QuestionController::PAR_PAGE, Question::count()))
            ->where('questions.total', Question::count())
            ->has('questions.data.0', fn (Assert $question) => $question
                ->hasAll(['id', 'enonce', 'categorie', 'ordre', 'epreuve', 'choix', 'bonne_reponse', 'support']))
            ->has('epreuves', 4)
            ->where('filtres', ['epreuve' => '', 'q' => ''])
            // Sous-menu des épreuves dans la navigation admin.
            ->has('navigation.epreuves', 4)
            ->where('navigation.epreuves.0.slug', 'comprehension-orale'));

        $this->get('/admin?epreuve=comprehension-ecrite')->assertInertia(fn (Assert $page) => $page
            ->where('questions.total', $comprehensionEcrite->questions()->count())
            ->where('questions.data.0.epreuve.slug', 'comprehension-ecrite')
            ->where('filtres.epreuve', 'comprehension-ecrite'));

        $this->get('/admin?q=ordonnance')->assertInertia(fn (Assert $page) => $page
            ->where('questions.total', Question::where('enonce', 'like', '%ordonnance%')->orWhere('categorie', 'like', '%ordonnance%')->count())
            ->where('filtres.q', 'ordonnance'));

        $this->get('/admin?q=Vie+quotidienne')->assertInertia(fn (Assert $page) => $page
            ->where('questions.total', Question::where('categorie', 'Vie quotidienne')->count())
            ->where('questions.data.0.categorie', 'Vie quotidienne'));
    }

    public function test_une_page_devenue_vide_recule_a_la_derniere_page(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin?epreuve=comprehension-orale&page=9')
            ->assertRedirect(route('admin.questions', ['epreuve' => 'comprehension-orale', 'page' => 1]));
    }

    public function test_l_admin_cree_une_question(): void
    {
        $this->actingAs($this->admin());
        $epreuve = Epreuve::where('code', 'ce')->first();

        $this->get(route('admin.questions.creer', ['epreuve' => 'comprehension-ecrite']))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('admin/questions/formulaire')
            ->where('question.id', null)
            ->where('question.epreuve_id', $epreuve->id)
            ->where('question.ordre', $epreuve->questions()->max('ordre') + 1)
            ->where('question.choix', ['', '', '', ''])
            ->where("prochainsOrdres.{$epreuve->id}", $epreuve->questions()->max('ordre') + 1)
            ->has("categories.{$epreuve->id}"));

        $this->post(route('admin.questions.enregistrer'), $this->donnees(['epreuve_id' => $epreuve->id, 'support' => '', 'duree_audio' => '']))
            ->assertRedirect(route('admin.questions'))
            ->assertSessionHas('succes', 'Question créée.');

        $question = Question::latest('id')->first();
        $this->assertSame('Nouvelle question ?', $question->enonce);
        $this->assertSame(['Oui', 'Non', 'Peut-être'], $question->choix);
        $this->assertSame(2, $question->bonne_reponse);
        $this->assertNull($question->support);
        $this->assertNull($question->duree_audio);
    }

    public function test_la_question_est_validee(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.questions.enregistrer'), [])
            ->assertSessionHasErrors(['epreuve_id', 'categorie', 'enonce', 'choix', 'bonne_reponse', 'explication', 'ordre']);

        $this->post(route('admin.questions.enregistrer'), $this->donnees(['choix' => ['Oui', ''], 'bonne_reponse' => 1]))
            ->assertSessionHasErrors(['choix.1' => 'The choix field is required.']);

        $this->post(route('admin.questions.enregistrer'), $this->donnees(['choix' => ['Oui', 'Non'], 'bonne_reponse' => 2]))
            ->assertSessionHasErrors(['bonne_reponse' => 'The bonne réponse field must not be greater than 1.']);

        $this->post(route('admin.questions.enregistrer'), $this->donnees(['choix' => ['Seul choix']]))
            ->assertSessionHasErrors(['choix' => 'The choix field must have at least 2 items.']);

        $this->post(route('admin.questions.enregistrer'), $this->donnees(['epreuve_id' => 999]))
            ->assertSessionHasErrors(['epreuve_id' => 'The selected épreuve is invalid.']);
    }

    public function test_l_admin_modifie_une_question(): void
    {
        $this->actingAs($this->admin());
        $question = Question::first();

        $this->get(route('admin.questions.modifier', $question))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('admin/questions/formulaire')
            ->where('question.id', $question->id)
            ->where('question.enonce', $question->enonce)
            ->where('question.choix', $question->choix)
            ->where('question.support', ''));

        $this->put(route('admin.questions.mettre-a-jour', $question), $this->donnees([
            'epreuve_id' => $question->epreuve_id,
            'enonce' => 'Énoncé modifié',
            'categorie' => 'Nouvelle catégorie',
            'choix' => [...$question->choix, 'Cinquième choix'],
            'bonne_reponse' => 4,
        ]))->assertRedirect(route('admin.questions'))->assertSessionHas('succes', 'Question mise à jour.');

        $question->refresh();
        $this->assertSame('Énoncé modifié', $question->enonce);
        $this->assertSame('Nouvelle catégorie', $question->categorie);
        $this->assertSame('Cinquième choix', $question->choix[4]);
        $this->assertSame(4, $question->bonne_reponse);
    }

    public function test_l_admin_supprime_une_question(): void
    {
        $this->actingAs($this->admin());
        $question = Question::first();

        $this->from('/admin?epreuve=comprehension-orale')
            ->delete(route('admin.questions.supprimer', $question))
            ->assertRedirect('/admin?epreuve=comprehension-orale')
            ->assertSessionHas('succes', 'Question supprimée.');

        $this->assertModelMissing($question);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @param  array<string, mixed>  $changements
     * @return array<string, mixed>
     */
    private function donnees(array $changements = []): array
    {
        return [
            'epreuve_id' => Epreuve::where('code', 'co')->value('id'),
            'categorie' => 'Test',
            'enonce' => 'Nouvelle question ?',
            'support' => '',
            'audio' => '',
            'duree_audio' => 20,
            'transcription' => 'Texte lu.',
            'choix' => ['Oui', 'Non', 'Peut-être'],
            'bonne_reponse' => 2,
            'feedback' => '',
            'explication' => 'Parce que.',
            'ordre' => 99,
            ...$changements,
        ];
    }
}
