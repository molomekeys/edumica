<?php

namespace Tests\Feature;

use App\Mail\MessageContact;
use App\Models\Epreuve;
use Database\Seeders\EpreuveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
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

    public function test_les_pages_du_site_s_affichent(): void
    {
        $this->get('/epreuves')->assertOk()->assertSee('du TCF, une par une')->assertSee('Expression orale');
        $this->get('/epreuves/comprehension-orale')->assertOk()->assertSee('Une difficulté qui monte')->assertSee('Où se trouve la personne qui parle ?');
        $this->get('/epreuves/expression-orale')->assertOk()->assertSee('Entretien dirigé')->assertSee('Découvrir les tests blancs');
        $this->get('/epreuves/inconnue')->assertNotFound();
        $this->get('/tests-blancs')->assertOk()->assertSee('Quiz ou test blanc');
        $this->get('/scores-nclc')->assertOk()->assertSee('Le barème complet')->assertSee('458 – 502');
        $this->get('/tarifs')->assertOk()->assertSee('Ce qui est inclus');
        $this->get('/faq')->assertOk()->assertSee('Les quiz sont-ils vraiment gratuits ?');
        $this->get('/contact')->assertOk()->assertSee('Ton message');
        $this->get('/mentions-legales')->assertOk()->assertSee('Éditeur du site');
        $this->get('/cgv')->assertOk()->assertSee('Droit de rétractation');
        $this->get('/confidentialite')->assertOk()->assertSee('Tes droits');
    }

    public function test_la_navigation_mene_aux_pages(): void
    {
        $this->get('/')
            ->assertSee(route('epreuves'))
            ->assertSee(route('tests-blancs'))
            ->assertSee(route('scores'))
            ->assertSee(route('tarifs'))
            ->assertSee(route('faq'))
            ->assertSee(route('contact'))
            ->assertSee(route('epreuve', 'expression-ecrite'))
            ->assertSee(route('cgv'));
    }

    public function test_l_objectif_surligne_le_bareme(): void
    {
        Livewire::test('pages::scores')
            ->assertSet('cible', '7')
            ->dispatch('cible-choisie', niveau: '9')
            ->assertSet('cible', '9')
            ->assertSee('NCLC 9</span>, est surligné', false)
            ->dispatch('cible-choisie', niveau: '42')
            ->assertSet('cible', '9');

        Livewire::test('nclc-cible')->call('choisir', '8')->assertDispatched('cible-choisie', niveau: '8');
    }

    public function test_le_formulaire_de_contact(): void
    {
        Mail::fake();

        Livewire::test('pages::contact')
            ->call('envoyer')
            ->assertHasErrors(['nom', 'email', 'sujet', 'contenu'])
            ->set('nom', 'Awa')
            ->set('email', 'awa@example.com')
            ->set('sujet', 'Question sur le TCF')
            ->set('contenu', 'Combien de temps dure le TCF Canada ?')
            ->call('envoyer')
            ->assertHasNoErrors()
            ->assertSet('envoye', true)
            ->assertSet('contenu', '');

        Mail::assertSent(MessageContact::class, fn (MessageContact $mail) => $mail->hasTo(config('mail.contact')) && $mail->hasReplyTo('awa@example.com'));
    }

    public function test_le_formulaire_de_contact_ignore_les_robots(): void
    {
        Mail::fake();

        Livewire::test('pages::contact')
            ->set('nom', 'Robot')
            ->set('email', 'robot@example.com')
            ->set('sujet', 'Autre')
            ->set('contenu', 'Message automatique indésirable.')
            ->set('site', 'https://spam.example')
            ->call('envoyer')
            ->assertSet('envoye', true);

        Mail::assertNothingSent();
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
        $this->expectException(CannotUpdateLockedPropertyException::class);

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
