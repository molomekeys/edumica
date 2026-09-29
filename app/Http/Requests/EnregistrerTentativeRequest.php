<?php

namespace App\Http\Requests;

use App\Models\Question;
use App\Models\Tentative;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

/**
 * État d'un test blanc envoyé par le navigateur : une réponse par question (ou null),
 * les questions marquées « à revoir » et celles dont l'audio a déjà été écouté.
 */
class EnregistrerTentativeRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->tentative());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $total = $this->tentative()->total;

        return [
            'reponses' => ['present', 'array', 'list', 'size:'.$total],
            'reponses.*' => ['nullable', 'integer', 'min:0', 'max:'.(Question::CHOIX_MAX - 1)],
            'marquees' => ['present', 'array', 'list'],
            'marquees.*' => ['integer', 'distinct', 'min:0', 'max:'.($total - 1)],
            'ecoutees' => ['present', 'array', 'list'],
            'ecoutees.*' => ['integer', 'distinct', 'min:0', 'max:'.($total - 1)],
        ];
    }

    /**
     * Chaque réponse doit désigner un des choix de sa question.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('reponses*')) {
                    return;
                }

                foreach ($this->tentative()->questionsDuTest() as $i => $question) {
                    $choix = $this->input("reponses.{$i}");

                    if ($choix !== null && ! isset($question->choix[$choix])) {
                        $validator->errors()->add("reponses.{$i}", 'Ce choix n\'existe pas pour cette question.');
                    }
                }
            },
        ];
    }

    private function tentative(): Tentative
    {
        return $this->route('tentative');
    }
}
