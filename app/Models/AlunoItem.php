<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlunoItem extends Model
{
    protected $table = 'aluno_itens';

    protected $fillable = [
        'aluno_id',
        'roleta_item_id',
        'quantidade',
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RoletaItem::class, 'roleta_item_id');
    }
}
