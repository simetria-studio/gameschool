<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'titulo',
        'unidade_id',
        'descricao',
        'xp',
        'coins',
        'nota_minima',
        'tentativas_max',
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

    public function turmas(): BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'quiz_turma');
    }

    public function perguntas(): HasMany
    {
        return $this->hasMany(QuizPergunta::class)->orderBy('ordem');
    }

    public function tentativas(): HasMany
    {
        return $this->hasMany(QuizTentativa::class);
    }

    public function isAtivo(): bool
    {
        if ($this->status !== 'ativa') {
            return false;
        }

        if ($this->data_encerramento && $this->data_encerramento->isPast()) {
            return false;
        }

        return true;
    }
}
