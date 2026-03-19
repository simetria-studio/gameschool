<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aluno extends Model
{
    protected $fillable = [
        'genero',
        'nome',
        'data_nascimento',
        'coins',
        'xp',
        'unidade_id',
        'turma_id',
        'user_id',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
