<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlunoAvatarPeca extends Model
{
    protected $table = 'aluno_avatar_pecas';

    protected $fillable = [
        'aluno_id',
        'avatar_peca_id',
        'desbloqueado_em',
    ];

    protected $casts = [
        'desbloqueado_em' => 'datetime',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function peca(): BelongsTo
    {
        return $this->belongsTo(AvatarPeca::class, 'avatar_peca_id');
    }
}
