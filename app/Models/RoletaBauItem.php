<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoletaBauItem extends Model
{
    protected $table = 'roleta_bau_itens';

    protected $fillable = [
        'segmento_id',
        'roleta_item_id',
        'peso',
    ];

    public function segmento(): BelongsTo
    {
        return $this->belongsTo(RoletaSegmento::class, 'segmento_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RoletaItem::class, 'roleta_item_id');
    }
}
