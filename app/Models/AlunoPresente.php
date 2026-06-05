<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlunoPresente extends Model
{
    protected $table = 'aluno_presentes';

    protected $fillable = [
        'remetente_id',
        'destinatario_id',
        'roleta_item_id',
        'quantidade',
        'mensagem',
        'lido',
    ];

    protected $casts = [
        'lido' => 'boolean',
    ];

    public function remetente(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'remetente_id');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'destinatario_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RoletaItem::class, 'roleta_item_id');
    }
}
