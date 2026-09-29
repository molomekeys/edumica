<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['epreuve_id', 'categorie', 'enonce', 'support', 'audio', 'duree_audio', 'transcription', 'choix', 'bonne_reponse', 'feedback', 'explication', 'ordre'])]
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

    /** URL publique de l'audio : lien externe tel quel, sinon fichier du disque public. */
    public function urlAudio(): ?string
    {
        if (! $this->audio) {
            return null;
        }

        return str_starts_with($this->audio, 'http') ? $this->audio : Storage::url($this->audio);
    }
}
