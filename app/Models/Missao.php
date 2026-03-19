<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Missao extends Model
{
    protected $table = 'missoes';

    protected $fillable = [
        'titulo',
        'unidade_id',
        'turma_id',
        'descricao',
        'xp',
        'coins',
        'status',
        'data_encerramento',
    ];

    protected $casts = [
        'data_encerramento' => 'date',
    ];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }
}
