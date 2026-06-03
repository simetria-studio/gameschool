<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizOpcao extends Model
{
    protected $table = 'quiz_opcoes';

    protected $fillable = [
        'pergunta_id',
        'texto',
        'correta',
    ];

    protected $casts = [
        'correta' => 'boolean',
    ];

    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(QuizPergunta::class, 'pergunta_id');
    }
}
