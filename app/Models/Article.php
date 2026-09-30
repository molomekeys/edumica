<?php

namespace App\Models;

use App\Support\NettoyeurHtml;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Article du blog : conseils de préparation au TCF, écrit depuis le panel admin.
 * Visible sur le site une fois publié et sa date de publication passée.
 */
#[Fillable(['user_id', 'titre', 'slug', 'extrait', 'contenu', 'couverture', 'categorie', 'statut', 'publie_le', 'meta_titre', 'meta_description'])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    public const CATEGORIES = [
        'Conseils TCF',
        'Compréhension orale',
        'Compréhension écrite',
        'Expression écrite',
        'Expression orale',
        'Immigration Canada',
    ];

    public const BROUILLON = 'brouillon';

    public const PUBLIE = 'publie';

    public const STATUTS = [self::BROUILLON, self::PUBLIE];

    /** Vitesse de lecture retenue pour le temps de lecture. */
    public const MOTS_PAR_MINUTE = 200;

    protected $attributes = [
        'statut' => self::BROUILLON,
    ];

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            $article->slug = self::slugUnique($article->slug ?: $article->titre, $article->id);
            $article->temps_lecture = self::tempsLecture($article->contenu);
        });
    }

    protected function casts(): array
    {
        return [
            'publie_le' => 'datetime',
            'temps_lecture' => 'integer',
        ];
    }

    /** Le HTML de l'éditeur est toujours nettoyé avant l'enregistrement (XSS). */
    protected function contenu(): Attribute
    {
        return Attribute::set(fn (?string $html) => NettoyeurHtml::nettoyer($html));
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Articles visibles sur le site : publiés, date de publication passée. */
    #[Scope]
    protected function publies(Builder $requete): void
    {
        $requete->where('statut', self::PUBLIE)->where('publie_le', '<=', now());
    }

    public function estVisible(): bool
    {
        return $this->statut === self::PUBLIE && $this->publie_le !== null && ! $this->publie_le->isFuture();
    }

    /** Publié mais à une date future. */
    public function estProgramme(): bool
    {
        return $this->statut === self::PUBLIE && $this->publie_le?->isFuture();
    }

    public function urlCouverture(): ?string
    {
        if (! $this->couverture) {
            return null;
        }

        return str_starts_with($this->couverture, 'http') ? $this->couverture : Storage::disk('public')->url($this->couverture);
    }

    /** Catégorie dans l'URL (?categorie=conseils-tcf). */
    public static function slugCategorie(string $categorie): string
    {
        return Str::slug($categorie);
    }

    public static function categoriePourSlug(?string $slug): ?string
    {
        return collect(self::CATEGORIES)->first(fn (string $categorie) => self::slugCategorie($categorie) === $slug);
    }

    /**
     * Contenu avec une ancre sur chaque intertitre h2, et le sommaire qui y renvoie.
     *
     * @return array{html: string, sommaire: list<array{id: string, titre: string}>}
     */
    public function contenuAvecSommaire(): array
    {
        $sommaire = [];

        $html = preg_replace_callback('/<h2>(.*?)<\/h2>/is', function (array $correspondance) use (&$sommaire) {
            $titre = trim(html_entity_decode(strip_tags($correspondance[1]), ENT_QUOTES | ENT_HTML5));
            $base = Str::slug($titre) ?: 'section';
            $id = $base;

            for ($i = 2; in_array($id, array_column($sommaire, 'id'), true); $i++) {
                $id = "{$base}-{$i}";
            }

            $sommaire[] = ['id' => $id, 'titre' => $titre];

            return "<h2 id=\"{$id}\">{$correspondance[1]}</h2>";
        }, $this->contenu);

        return ['html' => $html, 'sommaire' => $sommaire];
    }

    /** Minutes de lecture du contenu HTML, une au minimum. */
    public static function tempsLecture(?string $html): int
    {
        $texte = trim(html_entity_decode(strip_tags(str_replace('<', ' <', (string) $html)), ENT_QUOTES | ENT_HTML5));
        $mots = $texte === '' ? 0 : count(preg_split('/\s+/u', $texte));

        return max(1, (int) ceil($mots / self::MOTS_PAR_MINUTE));
    }

    /** « L’écoute au TCF » → « l-ecoute-au-tcf » : l'apostrophe sépare les mots, comme dans l'éditeur. */
    public static function slugifier(string $texte): string
    {
        return rtrim(Str::limit(Str::slug(str_replace(["'", '’'], ' ', $texte)), 180, ''), '-');
    }

    /** Slug tiré du titre (ou saisi), suffixé -2, -3… s'il est déjà pris par un autre article. */
    public static function slugUnique(string $source, ?int $ignorer = null): string
    {
        $base = self::slugifier($source) ?: 'article';
        $slug = $base;

        for ($i = 2; static::where('slug', $slug)->when($ignorer, fn (Builder $requete) => $requete->whereKeyNot($ignorer))->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
