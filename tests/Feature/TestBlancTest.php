<?php

namespace Tests\Feature;

use App\Models\Epreuve;
use App\Models\Question;
use App\Models\Tentative;
use App\Models\User;
use Database\Seeders\EpreuveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TestBlancTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Epreuve $comprehensionOrale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeSecond();
        $this->seed(EpreuveSeeder::class);
        $this->user = User::factory()->create();
        $this->comprehensionOrale = Epreuve::where('code', 'co')->first();
        $this->actingAs($this->user);
    }

    public function test_le_test_blanc_demarre_sans_devoiler_les_reponses(): void
    {
        $this->get(route('test-blanc', $this->comprehensionOrale))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('test-blanc/passer')
            ->where('epreuve.nom', 'Compréhension orale')
            ->where('tentative.fin', now()->addMinutes(35)->getTimestamp())
            ->has('questions', $this->comprehensionOrale->questions()->count(), fn (Assert $question) => $question
                ->hasAll(['index', 'id', 'epreuve_id', 'categorie', 'enonce', 'support', 'audio', 'transcription', 'duree_audio', 'choix'])
                ->missing('bonne_reponse')
                ->missing('explication')));

        $tentative = $this->user->tentatives()->sole();

        // Les questions sont regroupées par catégorie.
        $categories = collect($tentative->questions)->map(fn (int $id) => Question::find($id)->categorie);
        $this->assertSame($categories->all(), $categories->groupBy(fn (string $categorie) => $categorie)->flatten()->all());
        $this->assertSame(array_fill(0, $tentative->total, null), $tentative->reponses);
    }

    public function test_un_rechargement_reprend_le_test_en_cours(): void
    {
        $this->get(route('test-blanc', $this->comprehensionOrale));
        $tentative = $this->user->tentatives()->sole();

        $this->travel(5)->minutes();

        $this->get(route('test-blanc', $this->comprehensionOrale))->assertInertia(fn (Assert $page) => $page
            ->where('tentative.id', $tentative->id)
            ->where('tentative.fin', $tentative->fin_chrono->getTimestamp()));

        // Chrono écoulé : un nouveau test commence.
        $this->travel(31)->minutes();
        $this->get(route('test-blanc', $this->comprehensionOrale))->assertInertia(fn (Assert $page) => $page->whereNot('tentative.id', $tentative->id));
    }

    public function test_le_test_complet_enchaine_toutes_les_epreuves(): void
    {
        $avecQuestions = Epreuve::has('questions')->orderBy('ordre')->get();

        $this->get(route('test-blanc.complet'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('epreuve', null)
            ->has('epreuves', 2)
            ->has('questions', Question::count()));

        $tentative = $this->user->tentatives()->sole();
        $this->assertNull($tentative->epreuve_id);
        $ordre = collect($tentative->questions)->map(fn (int $id) => Question::find($id)->epreuve_id)->unique()->values();
        $this->assertSame($avecQuestions->pluck('id')->all(), $ordre->all());
        $this->assertEqualsWithDelta(now()->addMinutes($avecQuestions->sum('duree_test'))->getTimestamp(), $tentative->fin_chrono->getTimestamp(), 2);
    }

    public function test_une_epreuve_sans_question_n_a_pas_de_test(): void
    {
        $this->get(route('test-blanc', Epreuve::where('code', 'eo')->first()))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('tentative', null)
            ->where('questions', []));

        $this->assertSame(0, Tentative::count());
    }

    public function test_la_sauvegarde_garde_les_reponses_et_les_ecoutes(): void
    {
        $tentative = $this->demarrer();
        $etat = $this->etat($tentative, [0 => 1], marquees: [2], ecoutees: [0]);

        $this->putJson(route('test-blanc.sauvegarder', $tentative), $etat)->assertNoContent();

        $tentative->refresh();
        $this->assertSame($etat['reponses'], $tentative->reponses);
        $this->assertSame([2], $tentative->marquees);
        $this->assertSame([0], $tentative->ecoutees);

        // Une écoute lancée ne peut pas être « oubliée » par le navigateur.
        $this->putJson(route('test-blanc.sauvegarder', $tentative), $this->etat($tentative, ecoutees: [1]))->assertNoContent();
        $this->assertSame([0, 1], $tentative->refresh()->ecoutees);
    }

    public function test_apres_le_chrono_les_reponses_sont_figees(): void
    {
        $tentative = $this->demarrer();
        $this->putJson(route('test-blanc.sauvegarder', $tentative), $this->etat($tentative, [0 => 1]));

        $this->travel(35)->minutes();
        $this->travel(Tentative::MARGE + 1)->seconds();

        $this->putJson(route('test-blanc.sauvegarder', $tentative), $this->etat($tentative, [0 => 2, 1 => 0]))->assertNoContent();
        $this->assertSame(1, $tentative->refresh()->reponses[0]);
        $this->assertNull($tentative->reponses[1]);
    }

    public function test_la_sauvegarde_valide_l_etat_envoye(): void
    {
        $tentative = $this->demarrer();
        $premiere = Question::find($tentative->questions[0]);

        $this->putJson(route('test-blanc.sauvegarder', $tentative), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reponses', 'marquees', 'ecoutees']);

        $this->putJson(route('test-blanc.sauvegarder', $tentative), [...$this->etat($tentative), 'reponses' => [0]])
            ->assertJsonValidationErrors(['reponses' => 'must contain '.$tentative->total.' items']);

        $this->putJson(route('test-blanc.sauvegarder', $tentative), $this->etat($tentative, [0 => count($premiere->choix)]))
            ->assertJsonValidationErrors(['reponses.0' => 'Ce choix n\'existe pas pour cette question.']);

        $this->putJson(route('test-blanc.sauvegarder', $tentative), $this->etat($tentative, ecoutees: [$tentative->total]))
            ->assertJsonValidationErrors(['ecoutees.0']);
    }

    public function test_terminer_calcule_le_resultat(): void
    {
        $tentative = $this->demarrer();
        $questions = $tentative->questionsDuTest();
        $bonnes = $questions->map(fn (Question $question) => $question->bonne_reponse)->all();

        $this->post(route('test-blanc.terminer', $tentative), $this->etat($tentative, [...$bonnes, 0 => null]))
            ->assertRedirect(route('test-blanc.resultat', $tentative));

        $tentative->refresh();
        $this->assertNotNull($tentative->terminee_le);
        $this->assertSame($tentative->total - 1, $tentative->bonnes);
        $this->assertSame('10+', $tentative->niveau);
        $this->assertSame($tentative->total - 1, $tentative->resultat[0]['bonnes']);

        $this->get(route('test-blanc.resultat', $tentative))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('test-blanc/resultat')
            ->where('tentative.bonnes', $tentative->total - 1)
            ->where('tentative.niveau', '10+')
            ->where('epreuves.0.nom', 'Compréhension orale')
            ->where('epreuves.0.maximum', 699)
            ->has('corrections', $tentative->total)
            ->where('corrections.0.reponse', null)
            ->where('corrections.0.bonne_reponse', $questions[0]->bonne_reponse)
            ->where('corrections.0.explication', $questions[0]->explication));

        // Le test terminé ne change plus.
        $this->post(route('test-blanc.terminer', $tentative), $this->etat($tentative))->assertRedirect(route('test-blanc.resultat', $tentative));
        $this->assertSame($tentative->total - 1, $tentative->refresh()->bonnes);
    }

    public function test_le_resultat_attend_la_fin_du_test(): void
    {
        $tentative = $this->demarrer();

        $this->get(route('test-blanc.resultat', $tentative))->assertRedirect(route('test-blanc', $this->comprehensionOrale));

        // Chrono écoulé sans que le navigateur ait terminé : le test est terminé à l'affichage.
        $this->travel(36)->minutes();
        $this->get(route('test-blanc.resultat', $tentative))->assertOk()->assertInertia(fn (Assert $page) => $page->where('tentative.bonnes', 0));
        $this->assertNotNull($tentative->refresh()->terminee_le);
    }

    public function test_abandonner_supprime_le_test_et_ramene_a_l_espace(): void
    {
        $tentative = $this->demarrer();

        $this->delete(route('test-blanc.abandonner', $tentative))
            ->assertRedirect(route('espace'))
            ->assertSessionHas('succes');

        $this->assertModelMissing($tentative);
    }

    public function test_le_test_d_un_autre_utilisateur_est_introuvable(): void
    {
        $tentative = $this->demarrer();
        $this->actingAs(User::factory()->create());

        $this->putJson(route('test-blanc.sauvegarder', $tentative), $this->etat($tentative))->assertNotFound();
        $this->post(route('test-blanc.terminer', $tentative), $this->etat($tentative))->assertNotFound();
        $this->get(route('test-blanc.resultat', $tentative))->assertNotFound();
        $this->delete(route('test-blanc.abandonner', $tentative))->assertNotFound();
        $this->assertModelExists($tentative);
    }

    private function demarrer(): Tentative
    {
        return Tentative::demarrer($this->user, $this->comprehensionOrale);
    }

    /**
     * @param  array<int, int|null>  $reponses  index de question => choix
     * @param  list<int>  $marquees
     * @param  list<int>  $ecoutees
     * @return array{reponses: list<int|null>, marquees: list<int>, ecoutees: list<int>}
     */
    private function etat(Tentative $tentative, array $reponses = [], array $marquees = [], array $ecoutees = []): array
    {
        return [
            'reponses' => array_replace(array_fill(0, $tentative->total, null), $reponses),
            'marquees' => $marquees,
            'ecoutees' => $ecoutees,
        ];
    }
}
