<?php

namespace App\Support;

use App\Models\Aluno;
use App\Models\Atitude;
use App\Notifications\RecompensaRecebida;

class NotificarRecompensaAluno
{
    public static function porAtitude(Aluno $aluno, Atitude $atitude): void
    {
        $aluno->notify(new RecompensaRecebida(
            coins: (int) $atitude->coins,
            xp: (int) $atitude->xp,
            origemTipo: 'atitude',
            origemTitulo: (string) $atitude->titulo,
            origemId: $atitude->id,
        ));
    }
}
