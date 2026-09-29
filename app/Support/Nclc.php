<?php

namespace App\Support;

/**
 * Correspondance entre les résultats du TCF Canada et les niveaux NCLC (barème IRCC).
 */
class Nclc
{
    /** Épreuves : code => [libellé, libellé court, score maximal]. */
    public const EPREUVES = [
        'co' => ['Compréhension orale', 'Compr. orale', 699],
        'ce' => ['Compréhension écrite', 'Compr. écrite', 699],
        'eo' => ['Expression orale', 'Expr. orale', 20],
        'ee' => ['Expression écrite', 'Expr. écrite', 20],
    ];

    /** Niveau NCLC => [code d'épreuve => [score minimal, score maximal]]. */
    public const BAREME = [
        '5' => ['co' => [369, 397], 'ce' => [375, 405], 'eo' => [6, 6], 'ee' => [6, 6]],
        '6' => ['co' => [398, 457], 'ce' => [406, 452], 'eo' => [7, 9], 'ee' => [7, 9]],
        '7' => ['co' => [458, 502], 'ce' => [453, 498], 'eo' => [10, 11], 'ee' => [10, 11]],
        '8' => ['co' => [503, 522], 'ce' => [499, 523], 'eo' => [12, 13], 'ee' => [12, 13]],
        '9' => ['co' => [523, 548], 'ce' => [524, 548], 'eo' => [14, 15], 'ee' => [14, 15]],
        '10+' => ['co' => [549, 699], 'ce' => [549, 699], 'eo' => [16, 20], 'ee' => [16, 20]],
    ];

    public const CIBLE_PAR_DEFAUT = '7';

    /** @return list<string> */
    public static function niveaux(): array
    {
        return array_map('strval', array_keys(self::BAREME));
    }

    public static function existe(string $niveau): bool
    {
        return array_key_exists($niveau, self::BAREME);
    }

    public static function minimum(string $niveau, string $epreuve): int
    {
        return self::BAREME[$niveau][$epreuve][0];
    }

    /** @return array<string, int> code d'épreuve => score minimal */
    public static function minimums(string $niveau): array
    {
        return array_map(fn (array $plage) => $plage[0], self::BAREME[$niveau]);
    }

    public static function maximum(string $epreuve): int
    {
        return self::EPREUVES[$epreuve][2];
    }

    public static function libelle(string $epreuve): string
    {
        return self::EPREUVES[$epreuve][0];
    }

    /** Niveau NCLC atteint pour un score, ou null sous le NCLC 5. */
    public static function niveauPour(string $epreuve, int $score): ?string
    {
        foreach (array_reverse(self::BAREME, true) as $niveau => $plages) {
            if ($score >= $plages[$epreuve][0]) {
                return (string) $niveau;
            }
        }

        return null;
    }

    /** @param  array{0: int, 1: int}  $plage */
    public static function plage(array $plage): string
    {
        return $plage[0] === $plage[1] ? (string) $plage[0] : "{$plage[0]} – {$plage[1]}";
    }
}
