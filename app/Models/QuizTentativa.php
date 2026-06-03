<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizTentativa extends Model
{
    protected $table = 'quiz_tentativas';

    protected $fillable = [
        'quiz_id',
        'aluno_id',
        'acertos',
        'total_perguntas',
        'nota',
        'xp_ganho',
        'coins_ganho',
        'aprovado',
        'completed_at',
    ];

    protected $casts = [
        'aprovado' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(QuizResposta::class, 'quiz_tentativa_id');
    }
}
