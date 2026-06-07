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
    public static function resolverDestinatarioPorNome(Aluno $remetente, string $nome): Aluno
    {
        $nome = trim($nome);

        if ($nome === '') {
            throw ValidationException::withMessages([
                'nome_destino' => ['Informe o nome do aluno destino.'],
            ]);
        }

        $baseQuery = Aluno::query()
            ->where('unidade_id', $remetente->unidade_id)
            ->where('id', '!=', $remetente->id);

        $exatos = (clone $baseQuery)
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->get();

        if ($exatos->count() === 1) {
            return $exatos->first();
        }

        if ($exatos->count() > 1) {
            throw ValidationException::withMessages([
                'nome_destino' => [
                    'Existem vários alunos com este nome. Seja mais específico: '
                    . $exatos->pluck('nome')->unique()->join(', '),
                ],
            ]);
        }

        $parciais = (clone $baseQuery)
            ->where('nome', 'like', '%' . $nome . '%')
            ->orderBy('nome')
            ->limit(10)
            ->get();

        if ($parciais->isEmpty()) {
            throw ValidationException::withMessages([
                'nome_destino' => ['Nenhum aluno encontrado com este nome na sua escola.'],
            ]);
        }

        if ($parciais->count() === 1) {
            return $parciais->first();
        }

        throw ValidationException::withMessages([
            'nome_destino' => [
                'Vários alunos encontrados. Informe o nome completo: '
                . $parciais->pluck('nome')->unique()->join(', '),
            ],
        ]);
    }

    public static function enviar(Aluno $remetente, Aluno $destinatario, AlunoItem $alunoItem, int $quantidade, ?string $mensagem): AlunoPresente
    {
        if ((int) $remetente->id === (int) $destinatario->id) {
            throw ValidationException::withMessages([
                'nome_destino' => ['Você não pode enviar presente para si mesmo.'],
            ]);
        }

        if ((int) $remetente->unidade_id !== (int) $destinatario->unidade_id) {
            throw ValidationException::withMessages([
                'nome_destino' => ['Só é possível enviar presentes para alunos da mesma escola.'],
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
