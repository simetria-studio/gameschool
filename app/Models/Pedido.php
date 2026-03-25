<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    protected $fillable = [
        'aluno_id',
        'loja_item_id',
        'qnt_atual',
        'coins',
        'status',
        'processado_em',
    ];

    protected function casts(): array
    {
        return [
            'processado_em' => 'datetime',
        ];
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(LojaItem::class, 'loja_item_id');
    }
}

