<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidade extends Model
{
    protected $table = 'unidades';

    protected $fillable = [
        'titulo',
        'endereco',
        'email',
        'telefone',
    ];

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class);
    }
}
