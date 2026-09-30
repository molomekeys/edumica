<?php

namespace App\Http\Requests\Admin;

use App\Models\Article;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création et modification d'un article depuis le panel admin. Le contenu HTML est nettoyé
 * par le modèle (Article::contenu) avant l'enregistrement.
 */
class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        // Slug saisi : normalisé (un doublon est refusé). Slug vide : tiré du titre et rendu unique.
        $this->merge([
            'slug' => $this->filled('slug')
                ? Article::slugifier((string) $this->input('slug'))
                : Article::slugUnique((string) $this->input('titre'), $this->route('article')?->id),
            'supprimer_couverture' => $this->boolean('supprimer_couverture'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('articles', 'slug')->ignore($this->route('article'))],
            'extrait' => ['required', 'string', 'max:300'],
            'contenu' => ['required', 'string', 'max:200000', function (string $attribut, mixed $valeur, \Closure $echec) {
                if (trim(strip_tags((string) $valeur)) === '') {
                    $echec('Le contenu est vide.');
                }
            }],
            'categorie' => ['required', 'string', Rule::in(Article::CATEGORIES)],
            'statut' => ['required', Rule::in(Article::STATUTS)],
            'publie_le' => ['nullable', 'date'],
            'couverture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'supprimer_couverture' => ['boolean'],
            'meta_titre' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }

    /**
     * Champs de l'article, sans le fichier de couverture (géré par le contrôleur).
     * Un article publié sans date l'est tout de suite.
     *
     * @return array<string, mixed>
     */
    public function donneesArticle(): array
    {
        $donnees = $this->safe()->except(['couverture', 'supprimer_couverture']);

        if ($donnees['statut'] === Article::PUBLIE && empty($donnees['publie_le'])) {
            $donnees['publie_le'] = now();
        }

        return $donnees;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'categorie' => 'catégorie',
            'publie_le' => 'date de publication',
            'meta_titre' => 'titre SEO',
            'meta_description' => 'description SEO',
        ];
    }
}
