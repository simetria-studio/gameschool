<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turma extends Model
{
    protected $fillable = [
        'nome',
        'ativo',
        'periodo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}
