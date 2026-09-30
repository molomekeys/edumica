<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminArticlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_crud_des_articles_est_reserve_aux_admins(): void
    {
        $article = Article::factory()->create();

        $this->get(route('admin.articles'))->assertRedirect(route('admin.connexion'));

        $this->actingAs(User::factory()->create());

        $this->get(route('admin.articles'))->assertForbidden();
        $this->get(route('admin.articles.creer'))->assertForbidden();
        $this->post(route('admin.articles.enregistrer'), $this->donnees())->assertForbidden();
        $this->get(route('admin.articles.modifier', $article))->assertForbidden();
        $this->put(route('admin.articles.mettre-a-jour', $article), $this->donnees())->assertForbidden();
        $this->delete(route('admin.articles.supprimer', $article))->assertForbidden();
        $this->post(route('admin.articles.image'), ['image' => UploadedFile::fake()->image('photo.jpg')])->assertForbidden();

        $this->assertSame(1, Article::count());
        $this->assertModelExists($article);
    }

    public function test_la_liste_est_filtree_par_statut_categorie_et_recherche(): void
    {
        $this->actingAs($this->admin());
        $publie = Article::factory()->create(['titre' => 'Réussir la compréhension orale', 'categorie' => 'Compréhension orale']);
        $brouillon = Article::factory()->brouillon()->create(['categorie' => 'Conseils TCF']);
        $programme = Article::factory()->programme()->create(['categorie' => 'Conseils TCF']);

        $this->get(route('admin.articles'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('admin/articles/index')
            ->where('articles.total', 3)
            // Brouillons (sans date) en tête, puis par date de publication décroissante.
            ->where('articles.data.0.id', $brouillon->id)
            ->where('articles.data.0.statut', 'brouillon')
            ->where('articles.data.1.statut', 'programme')
            ->where('articles.data.2.statut', 'publie')
            ->has('categories', count(Article::CATEGORIES))
            ->where('filtres', ['q' => '', 'categorie' => '', 'statut' => '']));

        $this->get(route('admin.articles', ['statut' => 'programme']))->assertInertia(fn (Assert $page) => $page
            ->where('articles.total', 1)
            ->where('articles.data.0.id', $programme->id));

        $this->get(route('admin.articles', ['statut' => 'publie']))->assertInertia(fn (Assert $page) => $page
            ->where('articles.total', 1)
            ->where('articles.data.0.id', $publie->id));

        $this->get(route('admin.articles', ['categorie' => 'Conseils TCF']))->assertInertia(fn (Assert $page) => $page
            ->where('articles.total', 2));

        $this->get(route('admin.articles', ['q' => 'orale']))->assertInertia(fn (Assert $page) => $page
            ->where('articles.total', 1)
            ->where('articles.data.0.id', $publie->id)
            ->where('filtres.q', 'orale'));

        // Filtres inconnus ignorés.
        $this->get(route('admin.articles', ['statut' => 'supprime', 'categorie' => 'Cuisine']))->assertInertia(fn (Assert $page) => $page
            ->where('articles.total', 3)
            ->where('filtres', ['q' => '', 'categorie' => '', 'statut' => '']));
    }

    public function test_l_admin_cree_un_brouillon_au_slug_tire_du_titre(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->get(route('admin.articles.creer'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('admin/articles/formulaire')
            ->where('article.id', null)
            ->where('article.statut', Article::BROUILLON)
            ->where('article.contenu', ''));

        $reponse = $this->post(route('admin.articles.enregistrer'), $this->donnees(['slug' => '']));

        $article = Article::sole();
        $reponse->assertRedirect(route('admin.articles.modifier', $article))->assertSessionHas('succes', 'Brouillon créé.');
        $this->assertSame('reussir-l-expression-ecrite-du-tcf', $article->slug);
        $this->assertTrue($article->auteur->is($admin));
        $this->assertSame(Article::BROUILLON, $article->statut);
        $this->assertNull($article->publie_le);
        $this->assertNull($article->meta_titre);
    }

    public function test_un_slug_deja_pris_est_complete_s_il_est_genere_et_refuse_s_il_est_saisi(): void
    {
        $this->actingAs($this->admin());
        Article::factory()->create(['titre' => 'Réussir l’expression écrite du TCF', 'slug' => 'reussir-l-expression-ecrite-du-tcf']);

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['slug' => '']))->assertSessionHasNoErrors();
        $this->assertTrue(Article::where('slug', 'reussir-l-expression-ecrite-du-tcf-2')->exists());

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['slug' => 'Réussir l’expression écrite du TCF']))
            ->assertSessionHasErrors(['slug' => 'The slug has already been taken.']);

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['slug' => '  Mon Slug  Personnalisé ']))->assertSessionHasNoErrors();
        $this->assertTrue(Article::where('slug', 'mon-slug-personnalise')->exists());
    }

    public function test_un_article_publie_sans_date_l_est_immediatement(): void
    {
        $this->actingAs($this->admin());
        $this->freezeSecond();

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['statut' => Article::PUBLIE]))
            ->assertSessionHas('succes', 'Article créé et publié.');

        $article = Article::sole();
        $this->assertTrue($article->publie_le->equalTo(now()));
        $this->assertTrue($article->estVisible());
    }

    public function test_un_article_publie_a_une_date_future_est_programme(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['statut' => Article::PUBLIE, 'publie_le' => now()->addDays(3)->format('Y-m-d\TH:i')]))
            ->assertSessionHas('succes', 'Article créé et programmé.');

        $this->assertTrue(Article::sole()->estProgramme());
    }

    public function test_le_contenu_est_nettoye_avant_l_enregistrement(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.articles.enregistrer'), $this->donnees([
            'contenu' => '<h2 onclick="alert(1)">Titre</h2><script>alert("xss")</script><p style="color:red">Texte <a href="javascript:alert(1)">piégé</a> '
                .'et <a href="https://edumica.test/tarifs" target="_blank">sûr</a>.</p><img src="x" onerror="alert(1)"><iframe src="https://exemple.com"></iframe>',
        ]))->assertSessionHasNoErrors();

        $contenu = Article::sole()->contenu;
        $this->assertStringContainsString('<h2>Titre</h2>', $contenu);
        $this->assertStringContainsString('href="https://edumica.test/tarifs"', $contenu);
        $this->assertStringContainsString('rel="noopener noreferrer"', $contenu);
        foreach (['<script', 'alert("xss")', 'onclick', 'onerror', 'javascript:', 'style=', '<iframe'] as $interdit) {
            $this->assertStringNotContainsString($interdit, $contenu);
        }
    }

    public function test_l_article_est_valide(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.articles.enregistrer'), [])
            ->assertSessionHasErrors(['titre', 'extrait', 'contenu', 'categorie', 'statut']);

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['contenu' => '<p></p><h2> </h2>']))
            ->assertSessionHasErrors(['contenu' => 'Le contenu est vide.']);

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['categorie' => 'Cuisine']))
            ->assertSessionHasErrors(['categorie' => 'The selected catégorie is invalid.']);

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['statut' => 'archive']))
            ->assertSessionHasErrors(['statut' => 'The selected statut is invalid.']);

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['meta_description' => str_repeat('a', 161)]))
            ->assertSessionHasErrors(['meta_description' => 'The description SEO field must not be greater than 160 characters.']);

        $this->post(route('admin.articles.enregistrer'), $this->donnees(['couverture' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf')]))
            ->assertSessionHasErrors(['couverture' => 'The couverture field must be an image.']);

        $this->assertSame(0, Article::count());
    }

    public function test_l_admin_modifie_un_article_et_sa_couverture(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $article = Article::factory()->brouillon()->create(['couverture' => UploadedFile::fake()->image('ancienne.jpg')->store('articles/couvertures', 'public')]);
        $ancienne = $article->couverture;

        $this->get(route('admin.articles.modifier', $article))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('admin/articles/formulaire')
            ->where('article.id', $article->id)
            ->where('article.titre', $article->titre)
            ->where('article.meta_titre', '')
            ->where('article.couverture', Storage::disk('public')->url($ancienne))
            ->where('article.url', null));

        $this->put(route('admin.articles.mettre-a-jour', $article), $this->donnees([
            'slug' => $article->slug,
            'couverture' => UploadedFile::fake()->image('nouvelle.jpg', 1200, 750),
        ]))->assertRedirect(route('admin.articles.modifier', $article))->assertSessionHas('succes', 'Brouillon mis à jour.');

        $article->refresh();
        $this->assertSame('Réussir l’expression écrite du TCF', $article->titre);
        Storage::disk('public')->assertMissing($ancienne);
        Storage::disk('public')->assertExists($article->couverture);

        $this->put(route('admin.articles.mettre-a-jour', $article), $this->donnees(['slug' => $article->slug, 'supprimer_couverture' => '1']))
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($article->couverture);
        $this->assertNull($article->refresh()->couverture);
    }

    public function test_l_admin_supprime_un_article_et_sa_couverture(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $article = Article::factory()->create(['couverture' => UploadedFile::fake()->image('couverture.jpg')->store('articles/couvertures', 'public')]);

        $this->from(route('admin.articles'))
            ->delete(route('admin.articles.supprimer', $article))
            ->assertRedirect(route('admin.articles'))
            ->assertSessionHas('succes', 'Article supprimé.');

        $this->assertModelMissing($article);
        Storage::disk('public')->assertMissing($article->couverture);
    }

    public function test_l_editeur_televerse_une_image_du_contenu(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $reponse = $this->postJson(route('admin.articles.image'), ['image' => UploadedFile::fake()->image('schema.png')])->assertOk();

        $this->assertStringStartsWith(Storage::disk('public')->url('articles/contenu/'), $reponse->json('url'));
        $this->assertCount(1, Storage::disk('public')->files('articles/contenu'));

        $this->postJson(route('admin.articles.image'), ['image' => UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml')])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');
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
            'titre' => 'Réussir l’expression écrite du TCF',
            'slug' => '',
            'extrait' => 'Trois tâches, soixante minutes : la méthode pour ne pas manquer de temps.',
            'contenu' => '<p>Introduction.</p><h2>Répartir son temps</h2><p>Dix minutes pour la tâche 1.</p>',
            'categorie' => 'Expression écrite',
            'statut' => Article::BROUILLON,
            'publie_le' => '',
            'meta_titre' => '',
            'meta_description' => '',
            ...$changements,
        ];
    }
}
