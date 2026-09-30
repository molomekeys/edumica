<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Faq;
use App\Support\GuideEpreuve;
use App\Support\Langue;
use App\Support\Nclc;
use Database\Seeders\EpreuveSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LangueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EpreuveSeeder::class);
    }

    public function test_le_site_est_en_francais_par_defaut(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="fr" dir="ltr">', false)
            ->assertSee('Sache ton')
            ->assertSee('العربية');
    }

    public function test_le_selecteur_passe_le_site_en_arabe_et_revient_sur_la_page(): void
    {
        $this->from('/tarifs')->get('/langue/ar')
            ->assertRedirect('/tarifs')
            ->assertCookie(Langue::COOKIE, 'ar');

        $this->withCookie(Langue::COOKIE, 'ar')->get('/tarifs')
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('Noto+Kufi+Arabic', false)
            ->assertSee('ما الذي يتضمنه كل عرض')
            ->assertDontSee('Ce qui est inclus')
            ->assertSee('Français');

        $this->get('/langue/de')->assertNotFound();
    }

    public function test_la_langue_du_navigateur_est_utilisee_sans_choix(): void
    {
        $this->get('/', ['Accept-Language' => 'ar-MA,ar;q=0.9,fr;q=0.8'])->assertSee('dir="rtl"', false)->assertSee('اعرف');
        $this->get('/', ['Accept-Language' => 'en-US,en;q=0.9'])->assertSee('dir="ltr"', false);
        $this->withCookie(Langue::COOKIE, 'fr')->get('/', ['Accept-Language' => 'ar'])->assertSee('dir="ltr"', false);
    }

    public function test_le_contenu_des_epreuves_reste_en_francais(): void
    {
        $this->withCookie(Langue::COOKIE, 'ar')->get('/epreuves/comprehension-orale')
            ->assertOk()
            ->assertSee('صعوبة متصاعدة')
            ->assertSee('<h3 lang="fr" dir="ltr"', false)
            ->assertSee('Où se trouve la personne qui parle ?');
    }

    public function test_les_dashboards_suivent_la_langue(): void
    {
        $this->actingAs(User::factory()->create())
            ->withCookie(Langue::COOKIE, 'ar')
            ->get('/espace')
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('espace/index')
                ->where('epreuves.0.nom', 'الفهم الشفهي'));
    }

    public function test_le_panel_admin_reste_en_francais(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->withCookie(Langue::COOKIE, 'ar')
            ->get('/admin')
            ->assertOk()
            ->assertSee('<html lang="fr" dir="ltr">', false);
    }

    public function test_la_connexion_admin_reste_en_francais(): void
    {
        $this->withCookie(Langue::COOKIE, 'ar')
            ->get('/admin/connexion')
            ->assertOk()
            ->assertSee('<html lang="fr" dir="ltr">', false)
            ->assertSee('Épreuves');
    }

    /** Un texte ajouté à la FAQ, aux guides d'épreuve ou au barème doit aussi l'être dans lang/ar.json. */
    public function test_le_contenu_editorial_est_traduit_en_arabe(): void
    {
        $traductions = json_decode(file_get_contents(lang_path('ar.json')), true);
        $contenu = [Faq::THEMES, GuideEpreuve::CONTENU, Nclc::EPREUVES];
        $textes = [];

        array_walk_recursive($contenu, function (mixed $valeur) use (&$textes): void {
            if (is_string($valeur) && preg_match('/\p{L}{2}/u', $valeur) && ! preg_match('/^(tcf|tests-blancs|quiz|compte|[A-C][12])$/', $valeur)) {
                $textes[] = $valeur;
            }
        });
        $textes = [...$textes, ...array_keys(Faq::THEMES)];

        $this->assertSame([], array_values(array_diff($textes, array_keys($traductions))));
    }
}
