<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizResposta extends Model
{
    protected $table = 'quiz_respostas';

    protected $fillable = [
        'quiz_tentativa_id',
        'pergunta_id',
        'opcao_id',
        'correta',
    ];

    protected $casts = [
        'correta' => 'boolean',
    ];

    public function tentativa(): BelongsTo
    {
        return $this->belongsTo(QuizTentativa::class, 'quiz_tentativa_id');
    }

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(QuizPergunta::class, 'pergunta_id');
    }

    public function opcao(): BelongsTo
    {
        return $this->belongsTo(QuizOpcao::class, 'opcao_id');
    }
}
