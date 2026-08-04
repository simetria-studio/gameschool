<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlunoAvatar extends Model
{
    protected $table = 'aluno_avatars';

    protected $fillable = [
        'aluno_id',
        'genero',
        'configuracao_json',
        'thumbnail_url',
    ];

    protected $casts = [
        'configuracao_json' => 'array',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}
