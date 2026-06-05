<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Aluno extends Model
{
    use Notifiable;
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

    public function tentativas(): HasMany
    {
        return $this->hasMany(QuizTentativa::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(AlunoItem::class);
    }

    public function roletaGiros(): HasMany
    {
        return $this->hasMany(RoletaGiro::class);
    }

    public function presentesRecebidos(): HasMany
    {
        return $this->hasMany(AlunoPresente::class, 'destinatario_id');
    }

    public function presentesEnviados(): HasMany
    {
        return $this->hasMany(AlunoPresente::class, 'remetente_id');
    }
}
