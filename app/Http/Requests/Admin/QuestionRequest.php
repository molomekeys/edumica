<?php

namespace App\Http\Requests\Admin;

use App\Models\Question;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création et modification d'une question depuis le panel admin. Les champs texte
 * laissés vides arrivent à null (middleware ConvertEmptyStringsToNull).
 */
class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $choix = $this->input('choix');
        $derniereReponse = is_array($choix) ? max(0, count($choix) - 1) : 0;

        return [
            'epreuve_id' => ['required', 'integer', Rule::exists('epreuves', 'id')],
            'categorie' => ['required', 'string', 'max:80'],
            'enonce' => ['required', 'string', 'max:255'],
            'support' => ['nullable', 'string', 'max:5000'],
            'audio' => ['nullable', 'string', 'max:255'],
            'duree_audio' => ['nullable', 'integer', 'min:1', 'max:600'],
            'transcription' => ['nullable', 'string', 'max:5000'],
            'choix' => ['required', 'array', 'list', 'min:'.Question::CHOIX_MIN, 'max:'.Question::CHOIX_MAX],
            'choix.*' => ['required', 'string', 'max:255'],
            'bonne_reponse' => ['required', 'integer', 'min:0', 'max:'.$derniereReponse],
            'feedback' => ['nullable', 'string', 'max:255'],
            'explication' => ['required', 'string', 'max:5000'],
            'ordre' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'epreuve_id' => 'épreuve',
            'categorie' => 'catégorie',
            'enonce' => 'énoncé',
            'choix.*' => 'choix',
            'bonne_reponse' => 'bonne réponse',
            'duree_audio' => 'durée',
        ];
    }
}
