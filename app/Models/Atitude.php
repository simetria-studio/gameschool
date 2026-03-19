<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atitude extends Model
{
    protected $fillable = [
        'titulo',
        'descricao',
        'tipo',
        'coins',
        'xp',
    ];
}
