<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Article publié hier, avec deux intertitres.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titre = rtrim(fake()->unique()->sentence(6), '.');

        return [
            'user_id' => User::factory(),
            'titre' => $titre,
            'extrait' => fake()->sentence(18),
            'contenu' => '<p>'.fake()->paragraph(6).'</p><h2>Premier conseil</h2><p>'.fake()->paragraph(8).'</p><h2>Deuxième conseil</h2><p>'.fake()->paragraph(8).'</p>',
            'categorie' => fake()->randomElement(Article::CATEGORIES),
            'statut' => Article::PUBLIE,
            'publie_le' => now()->subDay(),
        ];
    }

    public function brouillon(): static
    {
        return $this->state(fn () => ['statut' => Article::BROUILLON, 'publie_le' => null]);
    }

    /** Publié à une date future. */
    public function programme(): static
    {
        return $this->state(fn () => ['statut' => Article::PUBLIE, 'publie_le' => now()->addWeek()]);
    }
}
