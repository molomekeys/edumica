<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'code', 'nom', 'description', 'format', 'duree_test', 'icone', 'ordre'])]
class Epreuve extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('ordre');
    }
}
