<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizPergunta extends Model
{
    protected $table = 'quiz_perguntas';

    protected $fillable = [
        'quiz_id',
        'enunciado',
        'ordem',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function opcoes(): HasMany
    {
        return $this->hasMany(QuizOpcao::class, 'pergunta_id');
    }
}
