<?php

namespace App\Models;

use App\Support\Nclc;
use Database\Factories\TentativeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * Un test blanc passé par un utilisateur : les questions tirées, ses réponses et le chrono.
 * L'état du test est gardé côté serveur pour que le chrono et les écoutes survivent à un rechargement.
 */
#[Fillable(['epreuve_id', 'questions', 'reponses', 'marquees', 'ecoutees', 'total', 'fin_chrono'])]
class Tentative extends Model
{
    /** @use HasFactory<TentativeFactory> */
    use HasFactory;

    /** Durée par défaut d'une épreuve si elle n'en définit pas, en minutes. */
    public const DUREE_PAR_DEFAUT = 30;

    /** Marge accordée à la latence réseau après la fin du chrono, en secondes. */
    public const MARGE = 5;

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'reponses' => 'array',
            'marquees' => 'array',
            'ecoutees' => 'array',
            'resultat' => 'array',
            'total' => 'integer',
            'bonnes' => 'integer',
            'fin_chrono' => 'datetime',
            'terminee_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Épreuve du test, ou null pour le test complet qui enchaîne toutes les épreuves. */
    public function epreuve(): BelongsTo
    {
        return $this->belongsTo(Epreuve::class);
    }

    /** Tests commencés dont le chrono tourne encore. */
    #[Scope]
    protected function enCours(Builder $query): void
    {
        $query->whereNull('terminee_le')->where('fin_chrono', '>', now());
    }

    #[Scope]
    protected function terminees(Builder $query): void
    {
        $query->whereNotNull('terminee_le');
    }

    /**
     * Épreuves d'un test blanc, dans l'ordre : l'épreuve demandée, ou toutes pour le test complet.
     * Seules les épreuves qui ont des questions comptent.
     *
     * @return EloquentCollection<int, Epreuve>
     */
    public static function epreuvesPour(?Epreuve $epreuve): EloquentCollection
    {
        return Epreuve::query()
            ->when($epreuve, fn (Builder $requete) => $requete->whereKey($epreuve->id))
            ->has('questions')
            ->orderBy('ordre')
            ->get();
    }

    /** Démarre un test blanc, ou renvoie null s'il n'y a aucune question. */
    public static function demarrer(User $user, ?Epreuve $epreuve): ?self
    {
        $epreuves = self::epreuvesPour($epreuve)->load('questions');

        if ($epreuves->isEmpty()) {
            return null;
        }

        // Les épreuves se suivent dans leur ordre ; dans chacune, les catégories
        // apparaissent dans l'ordre de leur première question.
        $ids = $epreuves
            ->flatMap(fn (Epreuve $e) => $e->questions->groupBy('categorie')->flatMap(fn (Collection $questions) => $questions->pluck('id')))
            ->values()
            ->all();

        $minutes = $epreuves->sum(fn (Epreuve $e) => $e->duree_test ?? self::DUREE_PAR_DEFAUT);

        return $user->tentatives()->create([
            'epreuve_id' => $epreuve?->id,
            'questions' => $ids,
            'reponses' => array_fill(0, count($ids), null),
            'marquees' => [],
            'ecoutees' => [],
            'total' => count($ids),
            'fin_chrono' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Questions du test, indexées par leur position dans le test. Une question
     * supprimée entre-temps est ignorée, sans décaler les autres.
     *
     * @return Collection<int, Question>
     */
    public function questionsDuTest(): Collection
    {
        return once(function () {
            $questions = Question::findMany($this->questions)->keyBy('id');

            return collect($this->questions)->map(fn (int $id) => $questions->get($id))->filter();
        });
    }

    /** Test terminé, ou chrono écoulé : les réponses ne peuvent plus changer. */
    public function estVerrouille(): bool
    {
        return $this->terminee_le !== null
            || now()->getTimestamp() > $this->fin_chrono->getTimestamp() + self::MARGE;
    }

    /**
     * Enregistre l'état envoyé par le navigateur. Une fois le chrono écoulé, les réponses
     * sont figées ; une écoute lancée, elle, reste acquise et n'est jamais retirée.
     *
     * @param  array{reponses: list<int|null>, marquees: list<int>, ecoutees: list<int>}  $etat
     */
    public function enregistrer(array $etat): void
    {
        if ($this->terminee_le) {
            return;
        }

        $this->ecoutees = array_values(array_unique([...$this->ecoutees, ...$etat['ecoutees']]));

        if (! $this->estVerrouille()) {
            $this->reponses = $etat['reponses'];
            $this->marquees = $etat['marquees'];
        }

        $this->save();
    }

    /** Termine le test et fige son résultat. Sans effet s'il est déjà terminé. */
    public function terminer(): void
    {
        if ($this->terminee_le) {
            return;
        }

        $resultat = $this->calculerResultat();

        $this->forceFill([
            'terminee_le' => now(),
            'bonnes' => $resultat['bonnes'],
            'niveau' => $resultat['niveau'],
            'resultat' => $resultat['epreuves'],
        ])->save();
    }

    /**
     * Bonnes réponses et score NCLC estimé, par épreuve puis par catégorie. Le niveau global
     * est le plus faible des épreuves, comme pour l'IRCC.
     *
     * @return array{
     *     bonnes: int,
     *     niveau: ?string,
     *     epreuves: list<array{epreuve_id: int, bonnes: int, total: int, score: ?int, niveau: ?string, categories: list<array{nom: string, bonnes: int, total: int}>}>
     * }
     */
    public function calculerResultat(): array
    {
        $parEpreuve = [];

        foreach ($this->questionsDuTest() as $i => $question) {
            $correcte = (int) $question->estCorrecte($this->reponses[$i] ?? null);
            $stats = $parEpreuve[$question->epreuve_id] ?? ['bonnes' => 0, 'total' => 0, 'categories' => []];
            $categorie = $stats['categories'][$question->categorie] ?? ['nom' => $question->categorie, 'bonnes' => 0, 'total' => 0];

            $stats['bonnes'] += $correcte;
            $stats['total']++;
            $stats['categories'][$question->categorie] = ['nom' => $categorie['nom'], 'bonnes' => $categorie['bonnes'] + $correcte, 'total' => $categorie['total'] + 1];
            $parEpreuve[$question->epreuve_id] = $stats;
        }

        $codes = Epreuve::whereKey(array_keys($parEpreuve))->pluck('code', 'id');
        $epreuves = [];

        foreach ($parEpreuve as $id => $stats) {
            $code = $codes[$id];
            $score = isset(Nclc::EPREUVES[$code]) ? (int) round($stats['bonnes'] / $stats['total'] * Nclc::maximum($code)) : null;

            $epreuves[] = [
                'epreuve_id' => $id,
                'bonnes' => $stats['bonnes'],
                'total' => $stats['total'],
                'score' => $score,
                'niveau' => $score === null ? null : Nclc::niveauPour($code, $score),
                'categories' => array_values($stats['categories']),
            ];
        }

        return [
            'bonnes' => array_sum(array_column($epreuves, 'bonnes')),
            'niveau' => Nclc::plusFaible(array_column(array_filter($epreuves, fn (array $e) => $e['score'] !== null), 'niveau')),
            'epreuves' => $epreuves,
        ];
    }
}
