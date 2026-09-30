<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Nettoie le HTML riche des articles (éditeur de l'admin) contre le XSS : seules les balises
 * de mise en forme de l'éditeur sont gardées, sans script, style ni attribut d'événement.
 */
class NettoyeurHtml
{
    /** Balises autorisées => attributs autorisés. */
    public const BALISES = [
        'p' => [],
        'br' => [],
        'hr' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'code' => [],
        'pre' => [],
        'blockquote' => [],
        'ul' => [],
        'ol' => ['start'],
        'li' => [],
        'a' => ['href', 'title', 'target'],
        'img' => ['src', 'alt', 'title'],
        'figure' => [],
        'figcaption' => [],
    ];

    private static ?HtmlSanitizer $nettoyeur = null;

    public static function nettoyer(?string $html): string
    {
        return trim(self::nettoyeur()->sanitize((string) $html));
    }

    private static function nettoyeur(): HtmlSanitizer
    {
        if (self::$nettoyeur) {
            return self::$nettoyeur;
        }

        $config = (new HtmlSanitizerConfig)
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['https', 'http'])
            ->allowRelativeMedias()
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(500_000);

        foreach (self::BALISES as $balise => $attributs) {
            $config = $config->allowElement($balise, $attributs);
        }

        return self::$nettoyeur = new HtmlSanitizer($config);
    }
}
