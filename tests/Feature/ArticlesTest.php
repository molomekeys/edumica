<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_seuls_les_articles_publies_et_dates_du_passe_sont_listes(): void
    {
        $ancien = Article::factory()->create(['titre' => 'Article plus ancien', 'publie_le' => now()->subWeek()]);
        $recent = Article::factory()->create(['titre' => 'Article le plus récent', 'publie_le' => now()->subHour()]);
        Article::factory()->brouillon()->create(['titre' => 'Brouillon en cours']);
        Article::factory()->programme()->create(['titre' => 'Article programmé']);

        $this->get(route('articles'))
            ->assertOk()
            ->assertSeeInOrder(['À la une', $recent->titre, $ancien->titre])
            ->assertDontSee('Brouillon en cours')
            ->assertDontSee('Article programmé');
    }

    public function test_la_liste_se_filtre_par_categorie_et_par_recherche(): void
    {
        Article::factory()->create(['titre' => 'Les pièges de l’écoute', 'categorie' => 'Compréhension orale']);
        Article::factory()->create(['titre' => 'Lire vite et bien', 'categorie' => 'Compréhension écrite', 'extrait' => 'Techniques de lecture en diagonale.']);

        Livewire::test('pages::articles')
            ->call('filtrer', 'comprehension-orale')
            ->assertSet('categorie', 'comprehension-orale')
            ->assertSee('Les pièges de l’écoute')
            ->assertDontSee('Lire vite et bien')
            // Liste filtrée : pas d'article à la une.
            ->assertDontSee('À la une')
            ->call('filtrer')
            ->set('q', 'diagonale')
            ->assertSee('Lire vite et bien')
            ->assertDontSee('Les pièges de l’écoute')
            ->set('q', 'introuvable')
            ->assertSee('Aucun article ne correspond à ta recherche.');

        $this->get(route('articles', ['categorie' => 'comprehension-ecrite']))
            ->assertSee('Lire vite et bien')
            ->assertDontSee('Les pièges de l’écoute');
    }

    public function test_la_liste_est_paginee(): void
    {
        // Un article à la une, puis neuf par page.
        Article::factory()->count(11)->sequence(fn ($sequence) => ['publie_le' => now()->subDays($sequence->index + 1)])->create();
        $dernier = Article::orderBy('publie_le')->first();

        Livewire::test('pages::articles')
            ->assertDontSee($dernier->titre)
            ->call('gotoPage', 2)
            ->assertSee($dernier->titre);
    }

    public function test_un_article_publie_s_affiche_avec_son_sommaire_et_ses_balises_seo(): void
    {
        $article = Article::factory()->create([
            'titre' => 'Préparer le TCF en 8 semaines',
            'meta_titre' => 'Plan de révision TCF',
            'meta_description' => 'Un plan semaine par semaine.',
            'contenu' => '<p>Intro.</p><h2>Le diagnostic</h2><p>Texte.</p><h2>Les tests blancs</h2><p>Texte.</p>',
        ]);

        $this->get(route('article', $article->slug))
            ->assertOk()
            ->assertSee('<title>Plan de révision TCF · Edumica</title>', false)
            ->assertSee('<meta name="description" content="Un plan semaine par semaine.">', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<link rel="canonical" href="'.route('article', $article->slug).'">', false)
            ->assertSee('<h2 id="le-diagnostic">Le diagnostic</h2>', false)
            ->assertSee('href="#les-tests-blancs"', false)
            ->assertSee('Préparer le TCF en 8 semaines');
    }

    public function test_brouillons_et_articles_programmes_sont_introuvables(): void
    {
        $brouillon = Article::factory()->brouillon()->create();
        $programme = Article::factory()->programme()->create();

        $this->get(route('article', $brouillon->slug))->assertNotFound();
        $this->get(route('article', $programme->slug))->assertNotFound();
        $this->get(route('article', 'slug-inconnu'))->assertNotFound();
    }

    public function test_trois_articles_lies_de_la_meme_categorie_en_priorite(): void
    {
        $article = Article::factory()->create(['categorie' => 'Expression orale']);
        $memeCategorie = Article::factory()->create(['categorie' => 'Expression orale', 'titre' => 'Même catégorie']);
        Article::factory()->brouillon()->create(['categorie' => 'Expression orale', 'titre' => 'Brouillon lié']);
        Article::factory()->count(3)->create(['categorie' => 'Conseils TCF']);

        Livewire::test('pages::article', ['article' => $article])
            ->assertViewHas('lecture')
            ->tap(function ($composant) use ($memeCategorie) {
                $lies = $composant->instance()->lies;
                $this->assertCount(3, $lies);
                $this->assertTrue($lies->first()->is($memeCategorie));
                $this->assertFalse($lies->contains('titre', 'Brouillon lié'));
            });
    }

    public function test_le_slug_est_tire_du_titre_et_rendu_unique(): void
    {
        $premier = Article::factory()->create(['titre' => 'Scores NCLC : quel niveau viser ?']);
        $second = Article::factory()->create(['titre' => 'Scores NCLC : quel niveau viser ?']);

        $this->assertSame('scores-nclc-quel-niveau-viser', $premier->slug);
        $this->assertSame('scores-nclc-quel-niveau-viser-2', $second->slug);

        // Réenregistrer un article garde son slug.
        $premier->update(['extrait' => 'Nouvel extrait.']);
        $this->assertSame('scores-nclc-quel-niveau-viser', $premier->slug);
    }

    public function test_le_temps_de_lecture_est_calcule_a_l_enregistrement(): void
    {
        $article = Article::factory()->create(['contenu' => '<p>'.str_repeat('mot ', Article::MOTS_PAR_MINUTE * 2 + 1).'</p>']);

        $this->assertSame(3, $article->temps_lecture);

        $article->update(['contenu' => '<p>Court.</p>']);
        $this->assertSame(1, $article->temps_lecture);
    }

    public function test_les_articles_sont_dans_la_navigation_et_le_pied_de_page(): void
    {
        $this->get(route('accueil'))->assertOk()->assertSee('href="'.route('articles').'"', false);
    }
}
