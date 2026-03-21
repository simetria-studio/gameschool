<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Turma extends Model
{
    protected $fillable = [
        'unidade_id',
        'nome',
        'ativo',
        'periodo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function professores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'turma_user');
    }

    public function missoes(): BelongsToMany
    {
        return $this->belongsToMany(Missao::class, 'missao_turma');
    }
}
