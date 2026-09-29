<?php

namespace Database\Factories;

use App\Models\Tentative;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tentative>
 */
class TentativeFactory extends Factory
{
    /**
     * Test complet sans question, à compléter avec l'état « terminee » ou des questions.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'epreuve_id' => null,
            'questions' => [],
            'reponses' => [],
            'marquees' => [],
            'ecoutees' => [],
            'total' => 0,
            'fin_chrono' => now()->addMinutes(Tentative::DUREE_PAR_DEFAUT),
        ];
    }

    /** Test terminé avec un score donné. */
    public function terminee(int $bonnes, int $total, ?string $niveau = null): static
    {
        return $this->state(fn () => [
            'total' => $total,
            'bonnes' => $bonnes,
            'niveau' => $niveau,
            'resultat' => [],
            'terminee_le' => now(),
        ]);
    }
}
