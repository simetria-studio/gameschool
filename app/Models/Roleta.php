<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roleta extends Model
{
    protected $fillable = [
        'unidade_id',
        'titulo',
        'descricao',
        'custo_coins',
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
        return $this->belongsToMany(Turma::class, 'roleta_turma');
    }

    public function segmentos(): HasMany
    {
        return $this->hasMany(RoletaSegmento::class)->orderBy('ordem');
    }

    public function giros(): HasMany
    {
        return $this->hasMany(RoletaGiro::class);
    }

    public function isAtiva(): bool
    {
        if ($this->status !== 'ativa') {
            return false;
        }

        if ($this->data_encerramento && $this->data_encerramento->isPast()) {
            return false;
        }

        return $this->segmentos()->where('peso', '>', 0)->exists();
    }
}
