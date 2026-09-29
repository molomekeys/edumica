<?php

namespace Tests\Feature;

use App\Models\Epreuve;
use Database\Seeders\EpreuveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EpreuveSeeder::class);
    }

    public function test_les_pages_s_affichent(): void
    {
        $this->get('/')->assertOk()->assertSee('Sache ton')->assertSee('Compréhension orale');
        $this->get('/quiz/comprehension-orale')->assertOk()->assertSee('Question suivante');
        $this->get('/quiz/expression-orale')->assertOk()->assertSee('Pas encore de quiz');
        $this->get('/bilan')->assertOk()->assertSee('Presque NCLC 7 partout.');
        $this->get('/quiz/inconnue')->assertNotFound();
    }

    public function test_un_quiz_se_deroule_et_la_correction_arrive_a_la_fin(): void
    {
        $epreuve = Epreuve::where('code', 'co')->first();
        $quiz = Livewire::test('pages::quiz', ['epreuve' => $epreuve]);
        $ids = $quiz->get('ids');
        $total = count($ids);

        // Continuer sans choix ne fait rien.
        $quiz->call('suivante')->assertSet('index', 0);

        $premiere = $epreuve->questions()->find($ids[0]);
        $quiz->call('choisir', $premiere->bonne_reponse)
            ->call('suivante')
            ->assertSet('index', 1)
            ->assertDontSee($premiere->explication);

        $deuxieme = $epreuve->questions()->find($ids[1]);
        $quiz->call('choisir', ($deuxieme->bonne_reponse + 1) % count($deuxieme->choix))
            ->call('suivante')
            ->assertSet('index', 2)
            ->assertSet('termine', false)
            ->assertDontSee('Quiz terminé');

        for ($i = 2; $i < $total; $i++) {
            $quiz->call('passer');
        }

        $quiz->assertSet('termine', true)
            ->assertSee('Quiz terminé')
            ->assertSee("1 bonne réponse sur {$total}")
            ->assertSee('La correction')
            ->assertSee($premiere->explication)
            ->assertSee($deuxieme->explication)
            ->assertSee('À revoir')
            ->assertSee('Passée');
    }

    public function test_la_fin_du_chrono_garde_la_reponse_en_cours(): void
    {
        $epreuve = Epreuve::where('code', 'co')->first();
        $quiz = Livewire::test('pages::quiz', ['epreuve' => $epreuve]);
        $question = $epreuve->questions()->find($quiz->get('ids')[0]);

        $quiz->call('choisir', $question->bonne_reponse)
            ->call('terminer')
            ->assertSet('termine', true)
            ->assertSet('reponses', [0 => $question->bonne_reponse])
            ->assertSee('1 bonne réponse sur')
            ->assertSee('Le temps est écoulé avant la fin.')
            ->assertSee('Pas répondu');
    }

    public function test_les_proprietes_du_quiz_sont_verrouillees(): void
    {
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

        Livewire::test('pages::quiz', ['epreuve' => Epreuve::first()])->set('termine', true);
    }

    public function test_changer_l_objectif_recalcule_le_bilan(): void
    {
        Livewire::test('pages::bilan')
            ->call('choisir', '8')
            ->assertSet('cible', '8')
            ->assertSee('En route vers le NCLC 8.')
            ->assertSee('Encore 23 points pour le NCLC 8')
            ->call('choisir', '42')
            ->assertSet('cible', '8');
    }

    public function test_le_selecteur_de_nclc_cible(): void
    {
        Livewire::test('nclc-cible')
            ->assertSee('458')
            ->call('choisir', '9')
            ->assertSee('523')
            ->assertSee('524');
    }
}
