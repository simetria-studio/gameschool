<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoletaGiro extends Model
{
    protected $table = 'roleta_giros';

    protected $fillable = [
        'roleta_id',
        'aluno_id',
        'segmento_id',
        'tipo',
        'custo_coins',
        'coins_ganho',
        'xp_ganho',
        'premios_json',
    ];

    protected $casts = [
        'premios_json' => 'array',
    ];

    public function roleta(): BelongsTo
    {
        return $this->belongsTo(Roleta::class);
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function segmento(): BelongsTo
    {
        return $this->belongsTo(RoletaSegmento::class, 'segmento_id');
    }
}
