<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Panel admin : articles du blog.
 */
class ArticleController extends Controller
{
    public const PAR_PAGE = 15;

    /** Filtre de statut de la liste : brouillon, publie (en ligne) ou programme (publié à une date future). */
    public const FILTRES_STATUT = ['brouillon', 'publie', 'programme'];

    /** Liste paginée, filtrée par recherche (?q=), catégorie (?categorie=) et statut (?statut=). */
    public function index(Request $request): Response|RedirectResponse
    {
        $filtres = [
            'q' => $request->string('q')->trim()->toString(),
            'categorie' => in_array($request->query('categorie'), Article::CATEGORIES, true) ? $request->query('categorie') : '',
            'statut' => in_array($request->query('statut'), self::FILTRES_STATUT, true) ? $request->query('statut') : '',
        ];

        $articles = Article::query()
            ->when($filtres['q'], fn (Builder $requete, string $terme) => $requete->where(fn (Builder $requete) => $requete
                ->where('titre', 'like', "%{$terme}%")
                ->orWhere('extrait', 'like', "%{$terme}%")))
            ->when($filtres['categorie'], fn (Builder $requete, string $categorie) => $requete->where('categorie', $categorie))
            ->when($filtres['statut'], fn (Builder $requete, string $statut) => match ($statut) {
                'brouillon' => $requete->where('statut', Article::BROUILLON),
                'publie' => $requete->publies(),
                'programme' => $requete->where('statut', Article::PUBLIE)->where('publie_le', '>', now()),
            })
            ->orderByRaw('publie_le is null desc')
            ->latest('publie_le')
            ->latest('id')
            ->paginate(self::PAR_PAGE)
            ->withQueryString();

        // Page devenue vide (dernier article de la page supprimé) : on recule d'une page.
        if ($articles->isEmpty() && $articles->currentPage() > 1) {
            return to_route('admin.articles', [...array_filter($filtres), 'page' => $articles->lastPage()]);
        }

        return Inertia::render('admin/articles/index', [
            'articles' => $articles->through(fn (Article $article) => [
                'id' => $article->id,
                'titre' => $article->titre,
                'slug' => $article->slug,
                'categorie' => $article->categorie,
                'statut' => $article->estProgramme() ? 'programme' : ($article->estVisible() ? 'publie' : 'brouillon'),
                'publie_le' => $article->publie_le?->toIso8601String(),
                'couverture' => $article->urlCouverture(),
                'temps_lecture' => $article->temps_lecture,
            ]),
            'categories' => Article::CATEGORIES,
            'filtres' => $filtres,
        ]);
    }

    public function create(): Response
    {
        return $this->formulaire(new Article(['categorie' => Article::CATEGORIES[0], 'contenu' => '']));
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $article = new Article($request->donneesArticle());
        $article->auteur()->associate($request->user());
        $this->enregistrerCouverture($request, $article);
        $article->save();

        return to_route('admin.articles.modifier', $article)->with('succes', $this->message($article, 'créé'));
    }

    public function edit(Article $article): Response
    {
        return $this->formulaire($article);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $article->fill($request->donneesArticle());
        $this->enregistrerCouverture($request, $article);
        $article->save();

        return to_route('admin.articles.modifier', $article)->with('succes', $this->message($article, 'mis à jour'));
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        if ($article->couverture) {
            Storage::disk('public')->delete($article->couverture);
        }

        return back()->with('succes', 'Article supprimé.');
    }

    /** Image insérée dans le contenu depuis l'éditeur : renvoie son URL publique. */
    public function image(Request $request): JsonResponse
    {
        $request->validate(['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096']]);

        $chemin = $request->file('image')->store('articles/contenu', 'public');

        return response()->json(['url' => Storage::disk('public')->url($chemin)]);
    }

    /** Nouvelle couverture téléversée, ou couverture retirée : l'ancien fichier est supprimé. */
    private function enregistrerCouverture(ArticleRequest $request, Article $article): void
    {
        $nouvelle = $request->file('couverture');

        if (! $nouvelle && ! $request->boolean('supprimer_couverture')) {
            return;
        }

        if ($article->couverture) {
            Storage::disk('public')->delete($article->couverture);
        }

        $article->couverture = $nouvelle?->store('articles/couvertures', 'public');
    }

    private function message(Article $article, string $action): string
    {
        return match (true) {
            $article->estProgramme() => "Article {$action} et programmé.",
            $article->estVisible() => "Article {$action} et publié.",
            default => "Brouillon {$action}.",
        };
    }

    /** Formulaire de création ou de modification. Les champs texte vides sont envoyés en chaîne vide. */
    private function formulaire(Article $article): Response
    {
        return Inertia::render('admin/articles/formulaire', [
            'article' => [
                'id' => $article->id,
                ...array_map(fn (?string $valeur) => (string) $valeur, $article->only(['titre', 'slug', 'extrait', 'contenu', 'categorie', 'statut', 'meta_titre', 'meta_description'])),
                // Format de <input type="datetime-local">, à l'heure de l'application.
                'publie_le' => $article->publie_le?->format('Y-m-d\TH:i') ?? '',
                'couverture' => $article->urlCouverture(),
                'temps_lecture' => $article->temps_lecture ?? 1,
                'url' => $article->estVisible() ? route('article', $article->slug) : null,
            ],
            'categories' => Article::CATEGORIES,
        ]);
    }
}
