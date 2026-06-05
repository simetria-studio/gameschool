<?php

namespace App\Support;

use App\Models\Aluno;
use App\Models\AlunoItem;
use App\Models\AlunoPresente;
use App\Models\Roleta;
use App\Models\RoletaGiro;
use App\Models\RoletaItem;
use App\Models\RoletaSegmento;
use App\Notifications\PresenteRecebido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PresenteAlunoProcessor
{
    public static function enviar(Aluno $remetente, Aluno $destinatario, AlunoItem $alunoItem, int $quantidade, ?string $mensagem): AlunoPresente
    {
        if ((int) $remetente->id === (int) $destinatario->id) {
            throw ValidationException::withMessages([
                'id_aluno_destino' => ['Você não pode enviar presente para si mesmo.'],
            ]);
        }

        if ((int) $remetente->unidade_id !== (int) $destinatario->unidade_id) {
            throw ValidationException::withMessages([
                'id_aluno_destino' => ['Só é possível enviar presentes para alunos da mesma escola.'],
            ]);
        }

        if ((int) $alunoItem->aluno_id !== (int) $remetente->id) {
            abort(403);
        }

        $alunoItem->loadMissing('item');
        $item = $alunoItem->item;

        if (! $item || $item->status !== 'ativo') {
            throw ValidationException::withMessages([
                'aluno_item_id' => ['Este item não está disponível para envio.'],
            ]);
        }

        if ((int) $alunoItem->quantidade < $quantidade) {
            throw ValidationException::withMessages([
                'quantidade' => ['Quantidade insuficiente no seu inventário.'],
            ]);
        }

        return DB::transaction(function () use ($remetente, $destinatario, $alunoItem, $item, $quantidade, $mensagem) {
            InventarioAluno::remover($remetente, $item, $quantidade);
            InventarioAluno::adicionar($destinatario, $item, $quantidade);

            $presente = AlunoPresente::create([
                'remetente_id' => $remetente->id,
                'destinatario_id' => $destinatario->id,
                'roleta_item_id' => $item->id,
                'quantidade' => $quantidade,
                'mensagem' => $mensagem,
            ]);

            $destinatario->notify(new PresenteRecebido(
                remetenteNome: $remetente->nome,
                itemTitulo: $item->titulo,
                emoji: $item->emoji,
                quantidade: $quantidade,
                mensagem: $mensagem,
            ));

            return $presente->load(['item', 'remetente:id,nome', 'destinatario:id,nome']);
        });
    }
}
