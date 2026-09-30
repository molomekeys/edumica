<?php

namespace App\Http\Controllers;

use App\Models\Epreuve;
use App\Models\Tentative;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Espace utilisateur : tableau de bord, catalogue des tests blancs et résultats.
 */
class EspaceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $statistiques = $user->tentatives()->terminees()
            ->selectRaw('count(*) as tests, avg(bonnes * 100.0 / total) as score')
            ->first();

        $dernier = $user->tentatives()->terminees()->latest('terminee_le')->latest('id')->first(['niveau', 'terminee_le']);

        return Inertia::render('espace/index', [
            'statistiques' => [
                'tests' => (int) $statistiques->tests,
                'scoreMoyen' => $statistiques->score === null ? null : (int) round($statistiques->score),
                'dernierTest' => $dernier ? [
                    'niveau' => $dernier->niveau,
                    'le' => $dernier->terminee_le->toIso8601String(),
                ] : null,
            ],
            ...$this->catalogue($user),
        ]);
    }

    public function testsBlancs(Request $request): Response
    {
        return Inertia::render('espace/tests-blancs', $this->catalogue($request->user()));
    }

    /** Page à venir : historique détaillé des tests blancs. */
    public function resultats(): Response
    {
        return Inertia::render('espace/resultats');
    }

    /**
     * Tests blancs proposés : le test complet (toutes les épreuves qui ont des questions)
     * puis un test par épreuve. « enCours » signale un test commencé à reprendre.
     * Les noms et descriptions sont traduits dans la langue de l'interface (lang/ar.json).
     *
     * @return array{complet: ?array{epreuves: list<string>, questions: int, duree: int, enCours: bool}, epreuves: list<array<string, mixed>>}
     */
    private function catalogue(User $user): array
    {
        $epreuves = Epreuve::orderBy('ordre')->withCount('questions')->get();
        $disponibles = $epreuves->where('questions_count', '>', 0);
        $enCours = $user->tentatives()->enCours()->pluck('epreuve_id');
        $duree = fn (Epreuve $epreuve) => $epreuve->duree_test ?? Tentative::DUREE_PAR_DEFAUT;

        return [
            'complet' => $disponibles->isEmpty() ? null : [
                'epreuves' => $disponibles->map(fn (Epreuve $epreuve) => __($epreuve->nom))->values()->all(),
                'questions' => $disponibles->sum('questions_count'),
                'duree' => $disponibles->sum($duree),
                'enCours' => $enCours->containsStrict(null),
            ],
            'epreuves' => $epreuves->map(fn (Epreuve $epreuve) => [
                'id' => $epreuve->id,
                'slug' => $epreuve->slug,
                'nom' => __($epreuve->nom),
                'description' => __($epreuve->description),
                'icone' => $epreuve->icone,
                'questions' => $epreuve->questions_count,
                'duree' => $duree($epreuve),
                'enCours' => $enCours->containsStrict($epreuve->id),
            ])->all(),
        ];
    }
}
