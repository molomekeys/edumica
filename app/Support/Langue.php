<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

/**
 * Langues de l'interface. Le français est la langue source : les textes sont écrits en français
 * dans le code et traduits dans lang/ar.json. Le contenu des épreuves (questions, audios) reste en français.
 */
class Langue
{
    /** Code => [nom dans sa propre langue, sens d'écriture]. */
    public const LANGUES = [
        'fr' => ['Français', 'ltr'],
        'ar' => ['العربية', 'rtl'],
    ];

    public const CODES = ['fr', 'ar'];

    public const PAR_DEFAUT = 'fr';

    /** Cookie qui garde le choix du visiteur. */
    public const COOKIE = 'langue';

    public static function existe(string $code): bool
    {
        return array_key_exists($code, self::LANGUES);
    }

    public static function actuelle(): string
    {
        return App::getLocale();
    }

    public static function direction(?string $code = null): string
    {
        return self::LANGUES[$code ?? self::actuelle()][1] ?? 'ltr';
    }

    public static function nom(string $code): string
    {
        return self::LANGUES[$code][0];
    }

    /** L'autre langue, proposée par le sélecteur. */
    public static function autre(): string
    {
        return self::actuelle() === 'ar' ? 'fr' : 'ar';
    }

    /**
     * Traduit toutes les chaînes d'un tableau de contenu éditorial (FAQ, guides d'épreuve).
     *
     * @template T of array
     *
     * @param  T  $contenu
     * @return T
     */
    public static function traduire(array $contenu): array
    {
        array_walk_recursive($contenu, function (mixed &$valeur): void {
            if (is_string($valeur)) {
                $valeur = __($valeur);
            }
        });

        return $contenu;
    }
}
