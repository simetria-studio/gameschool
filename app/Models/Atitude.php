<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Atitude extends Model
{
    protected $fillable = [
        'unidade_id',
        'titulo',
        'descricao',
        'tipo',
        'coins',
        'xp',
    ];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }
}
