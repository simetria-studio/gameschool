<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoletaItem extends Model
{
    protected $table = 'roleta_itens';

    protected $fillable = [
        'unidade_id',
        'titulo',
        'tipo',
        'emoji',
        'imagem',
        'imagem_bloqueada',
        'raridade',
        'status',
    ];

    public const TIPOS = ['personagem', 'figurinha', 'emote'];

    public const RARIDADES = ['comum', 'raro', 'epico', 'lendario'];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function label(): string
    {
        if ($this->emoji) {
            return $this->emoji . ' ' . $this->titulo;
        }

        return $this->titulo;
    }
}
