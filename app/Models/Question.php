<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['epreuve_id', 'enonce', 'support', 'audio', 'duree_audio', 'transcription', 'choix', 'bonne_reponse', 'feedback', 'explication', 'ordre'])]
class Question extends Model
{
    protected function casts(): array
    {
        return [
            'choix' => 'array',
            'bonne_reponse' => 'integer',
            'duree_audio' => 'integer',
        ];
    }

    public function epreuve(): BelongsTo
    {
        return $this->belongsTo(Epreuve::class);
    }

    public function estCorrecte(?int $choix): bool
    {
        return $choix === $this->bonne_reponse;
    }
}
