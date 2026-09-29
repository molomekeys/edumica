<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnregistrerTentativeRequest;
use App\Models\Epreuve;
use App\Models\Question;
use App\Models\Tentative;
use App\Support\Nclc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Test blanc en conditions réelles : chrono fixé par le serveur, une seule écoute par audio,
 * correction à la fin. Le navigateur envoie son état au fil du test (sauvegarder) puis à la fin
 * (terminer) ; après le chrono, le serveur n'accepte plus de nouvelles réponses.
 */
class TestBlancController extends Controller
{
    /** Démarre un test blanc, ou reprend celui en cours pour la même épreuve (après un rechargement par exemple). */
    public function show(Request $request, ?Epreuve $epreuve = null): Response
    {
        $user = $request->user();

        $tentative = $user->tentatives()->enCours()->where('epreuve_id', $epreuve?->id)->latest('id')->first()
            ?? Tentative::demarrer($user, $epreuve);

        return Inertia::render('test-blanc/passer', [
            'epreuve' => $epreuve?->only(['nom', 'slug']),
            'tentative' => $tentative ? [
                'id' => $tentative->id,
                'fin' => $tentative->fin_chrono->getTimestamp(),
                'maintenant' => now()->getTimestamp(),
                'reponses' => $tentative->reponses,
                'marquees' => $tentative->marquees,
                'ecoutees' => $tentative->ecoutees,
            ] : null,
            'epreuves' => Tentative::epreuvesPour($epreuve)->map->only(['id', 'nom', 'icone'])->values(),
            // Ni la bonne réponse ni l'explication : elles n'arrivent qu'avec le résultat.
            'questions' => $tentative?->questionsDuTest()->map(fn (Question $question, int $index) => [
                'index' => $index,
                'id' => $question->id,
                'epreuve_id' => $question->epreuve_id,
                'categorie' => $question->categorie,
                'enonce' => $question->enonce,
                'support' => $question->support,
                'audio' => $question->urlAudio(),
                'transcription' => $question->transcription,
                'duree_audio' => $question->duree_audio ?? 30,
                'choix' => $question->choix,
            ])->values() ?? [],
        ]);
    }

    /** Sauvegarde en cours de test : réponses, questions à revoir et audios déjà écoutés. */
    public function sauvegarder(EnregistrerTentativeRequest $request, Tentative $tentative): HttpResponse
    {
        $tentative->enregistrer($request->validated());

        return response()->noContent();
    }

    public function terminer(EnregistrerTentativeRequest $request, Tentative $tentative): RedirectResponse
    {
        $tentative->enregistrer($request->validated());
        $tentative->terminer();

        return to_route('test-blanc.resultat', $tentative);
    }

    /** Quitte le test sans résultat : les réponses ne sont pas gardées. */
    public function abandonner(Tentative $tentative): RedirectResponse
    {
        Gate::authorize('delete', $tentative);

        if ($tentative->terminee_le) {
            return to_route('test-blanc.resultat', $tentative);
        }

        $tentative->delete();

        return to_route('espace')->with('succes', 'Test abandonné : tes réponses n\'ont pas été gardées.');
    }

    public function resultat(Tentative $tentative): Response|RedirectResponse
    {
        Gate::authorize('view', $tentative);

        if (! $tentative->terminee_le) {
            // Chrono encore en cours : on retourne au test. Sinon, le temps est écoulé
            // sans que le navigateur ait terminé le test : on le termine maintenant.
            if (! $tentative->estVerrouille()) {
                return $tentative->epreuve
                    ? to_route('test-blanc', $tentative->epreuve)
                    : to_route('test-blanc.complet');
            }

            $tentative->terminer();
        }

        $epreuves = Epreuve::whereKey(array_column($tentative->resultat, 'epreuve_id'))->get()->keyBy('id');

        return Inertia::render('test-blanc/resultat', [
            'epreuve' => $tentative->epreuve?->only(['nom', 'slug']),
            'tentative' => [
                'id' => $tentative->id,
                'bonnes' => $tentative->bonnes,
                'total' => $tentative->total,
                'niveau' => $tentative->niveau,
                'terminee_le' => $tentative->terminee_le->toIso8601String(),
            ],
            'epreuves' => collect($tentative->resultat)
                ->filter(fn (array $stats) => $epreuves->has($stats['epreuve_id']))
                ->map(fn (array $stats) => [
                    ...$stats,
                    'nom' => $epreuves[$stats['epreuve_id']]->nom,
                    'icone' => $epreuves[$stats['epreuve_id']]->icone,
                    'maximum' => Nclc::EPREUVES[$epreuves[$stats['epreuve_id']]->code][2] ?? null,
                ])
                ->values(),
            'corrections' => $tentative->questionsDuTest()->map(fn (Question $question, int $index) => [
                'index' => $index,
                'epreuve_id' => $question->epreuve_id,
                'categorie' => $question->categorie,
                'enonce' => $question->enonce,
                'choix' => $question->choix,
                'bonne_reponse' => $question->bonne_reponse,
                'reponse' => $tentative->reponses[$index] ?? null,
                'explication' => $question->explication,
            ])->values(),
        ]);
    }
}
