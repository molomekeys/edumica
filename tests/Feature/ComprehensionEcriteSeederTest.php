<?php

namespace Tests\Feature;

use App\Models\Epreuve;
use App\Models\Question;
use Database\Seeders\ComprehensionEcriteSeeder;
use Database\Seeders\EpreuveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensionEcriteSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([EpreuveSeeder::class, ComprehensionEcriteSeeder::class]);
    }

    public function test_les_questions_montent_de_a1_bas_a_c2_haut_avec_deux_questions_par_sous_niveau(): void
    {
        $paliers = [];
        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $niveau) {
            foreach (['bas', 'moyen', 'haut'] as $sousNiveau) {
                array_push($paliers, "{$niveau}-{$sousNiveau}", "{$niveau}-{$sousNiveau}");
            }
        }

        $questions = Epreuve::where('code', 'ce')->first()->questions()->whereNotNull('niveau')->get();

        $this->assertSame($paliers, $questions->map(fn (Question $question) => "{$question->niveau}-{$question->sous_niveau}")->all());
    }

    public function test_chaque_question_propose_quatre_choix_et_tient_dans_les_colonnes(): void
    {
        foreach (Question::whereNotNull('niveau')->get() as $question) {
            $this->assertCount(4, $question->choix, $question->enonce);
            $this->assertArrayHasKey($question->bonne_reponse, $question->choix, $question->enonce);
            $this->assertNotEmpty($question->support, $question->enonce);
            $this->assertNotEmpty($question->explication, $question->enonce);

            foreach ([$question->enonce, $question->feedback, ...$question->choix] as $texte) {
                $this->assertLessThanOrEqual(255, mb_strlen($texte), $texte);
            }
        }
    }

    public function test_relancer_le_seeder_remplace_le_lot_sans_toucher_aux_autres_questions(): void
    {
        $autres = Question::whereNull('niveau')->pluck('id')->all();

        $this->seed(ComprehensionEcriteSeeder::class);

        $this->assertSame(36, Question::whereNotNull('niveau')->count());
        $this->assertSame($autres, Question::whereNull('niveau')->pluck('id')->all());
    }
}
