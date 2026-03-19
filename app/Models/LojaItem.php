<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LojaItem extends Model
{
    protected $table = 'loja_itens';

    protected $fillable = [
        'unidade_id',
        'titulo',
        'quantidade',
        'coins',
        'status',
    ];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }
}
