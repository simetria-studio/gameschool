<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoletaSegmento extends Model
{
    protected $table = 'roleta_segmentos';

    protected $fillable = [
        'roleta_id',
        'titulo',
        'tipo',
        'roleta_item_id',
        'coins',
        'xp',
        'peso',
        'cor',
        'ordem',
    ];

    public const TIPOS = ['item', 'item_aleatorio', 'coins', 'xp', 'bau'];

    public function roleta(): BelongsTo
    {
        return $this->belongsTo(Roleta::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RoletaItem::class, 'roleta_item_id');
    }

    public function bauItens(): HasMany
    {
        return $this->hasMany(RoletaBauItem::class, 'segmento_id');
    }
}
