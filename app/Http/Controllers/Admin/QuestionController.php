<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuestionRequest;
use App\Models\Epreuve;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Panel admin : banque de questions des quiz et des tests blancs.
 */
class QuestionController extends Controller
{
    public const PAR_PAGE = 15;

    /** Liste paginée, filtrée par épreuve (?epreuve=slug) et par recherche (?q=). */
    public function index(Request $request): Response|RedirectResponse
    {
        $filtres = [
            'epreuve' => $request->string('epreuve')->trim()->toString(),
            'q' => $request->string('q')->trim()->toString(),
        ];

        $questions = Question::query()
            ->with('epreuve:id,slug,nom')
            ->when($filtres['epreuve'], fn (Builder $requete, string $slug) => $requete->whereRelation('epreuve', 'slug', $slug))
            ->when($filtres['q'], fn (Builder $requete, string $terme) => $requete->where(fn (Builder $requete) => $requete
                ->where('enonce', 'like', "%{$terme}%")
                ->orWhere('categorie', 'like', "%{$terme}%")))
            ->orderBy('epreuve_id')
            ->orderBy('categorie')
            ->orderBy('ordre')
            ->paginate(self::PAR_PAGE)
            ->withQueryString();

        // Page devenue vide (dernière question de la page supprimée) : on recule d'une page.
        if ($questions->isEmpty() && $questions->currentPage() > 1) {
            return to_route('admin.questions', [...array_filter($filtres), 'page' => $questions->lastPage()]);
        }

        return Inertia::render('admin/questions/index', [
            'questions' => $questions->through(fn (Question $question) => [
                'id' => $question->id,
                'enonce' => $question->enonce,
                'categorie' => $question->categorie,
                'ordre' => $question->ordre,
                'epreuve' => $question->epreuve->only(['slug', 'nom']),
                'choix' => count($question->choix),
                'bonne_reponse' => $question->choix[$question->bonne_reponse] ?? null,
                'support' => match (true) {
                    (bool) $question->audio => 'audio',
                    (bool) $question->transcription => 'voix',
                    (bool) $question->support => 'texte',
                    default => null,
                },
            ]),
            'epreuves' => Epreuve::orderBy('ordre')->withCount('questions')->get(['id', 'slug', 'nom']),
            'filtres' => $filtres,
        ]);
    }

    public function create(Request $request): Response
    {
        $epreuve = Epreuve::where('slug', $request->query('epreuve'))->first() ?? Epreuve::orderBy('ordre')->first();

        return $this->formulaire(new Question([
            'epreuve_id' => $epreuve?->id,
            'choix' => array_fill(0, 4, ''),
            'bonne_reponse' => 0,
            'ordre' => (int) Question::where('epreuve_id', $epreuve?->id)->max('ordre') + 1,
        ]));
    }

    public function store(QuestionRequest $request): RedirectResponse
    {
        Question::create($request->validated());

        return to_route('admin.questions')->with('succes', 'Question créée.');
    }

    public function edit(Question $question): Response
    {
        return $this->formulaire($question);
    }

    public function update(QuestionRequest $request, Question $question): RedirectResponse
    {
        $question->update($request->validated());

        return to_route('admin.questions')->with('succes', 'Question mise à jour.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('succes', 'Question supprimée.');
    }

    /** Formulaire de création ou de modification. Les champs texte vides sont envoyés en chaîne vide. */
    private function formulaire(Question $question): Response
    {
        $textes = ['categorie', 'enonce', 'support', 'audio', 'transcription', 'feedback', 'explication'];

        return Inertia::render('admin/questions/formulaire', [
            'question' => [
                'id' => $question->id,
                'epreuve_id' => $question->epreuve_id,
                ...array_map(fn (?string $valeur) => (string) $valeur, $question->only($textes)),
                'duree_audio' => $question->duree_audio ?? '',
                'choix' => $question->choix,
                'bonne_reponse' => $question->bonne_reponse,
                'ordre' => $question->ordre,
            ],
            'epreuves' => Epreuve::orderBy('ordre')->get(['id', 'nom']),
            // Suggestions de catégories et prochain numéro d'ordre, par épreuve.
            'categories' => Question::query()->select(['epreuve_id', 'categorie'])->distinct()->orderBy('categorie')->get()
                ->groupBy('epreuve_id')->map(fn ($questions) => $questions->pluck('categorie')),
            'prochainsOrdres' => Question::query()->selectRaw('epreuve_id, max(ordre) + 1 as prochain')->groupBy('epreuve_id')->pluck('prochain', 'epreuve_id'),
        ]);
    }
}
