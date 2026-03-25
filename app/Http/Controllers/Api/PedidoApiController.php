<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\LojaItem;
use App\Models\Pedido;
use App\Models\User;
use App\Support\PedidoAprovacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PedidoApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadMissing('aluno', 'turmas');

        $validated = $request->validate([
            'id_aluno' => ['required', 'integer', 'exists:alunos,id'],
            'id_produto' => ['required', 'integer', 'exists:loja_itens,id'],
            'quantidade' => ['sometimes', 'integer', 'min:1'],
        ], [], [
            'id_aluno' => 'aluno',
            'id_produto' => 'produto',
            'quantidade' => 'quantidade',
        ]);

        $qnt = (int) ($validated['quantidade'] ?? 1);
        $aluno = Aluno::findOrFail((int) $validated['id_aluno']);
        $produto = LojaItem::findOrFail((int) $validated['id_produto']);

        $this->authorizePedidoApp($user, $aluno, $produto);

        if ((int) $aluno->unidade_id !== (int) $produto->unidade_id) {
            throw ValidationException::withMessages([
                'id_produto' => ['O produto não pertence à mesma escola do aluno.'],
            ]);
        }

        if ($produto->status !== 'ativo' || (int) $produto->quantidade < $qnt) {
            throw ValidationException::withMessages([
                'id_produto' => ['Produto indisponível ou sem estoque suficiente.'],
            ]);
        }

        $coinsTotais = (int) $produto->coins * $qnt;

        $pedido = Pedido::create([
            'aluno_id' => $aluno->id,
            'loja_item_id' => $produto->id,
            'qnt_atual' => $qnt,
            'coins' => $coinsTotais,
            'status' => 'pendente',
        ]);

        $pedido->load(['aluno:id,nome,unidade_id,coins', 'produto:id,titulo,unidade_id,coins,quantidade']);

        return response()->json([
            'message' => 'Pedido registrado. Aguardando aprovação.',
            'data' => $this->formatPedido($pedido),
        ], 201);
    }

    public function approve(Request $request, Pedido $pedido): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $isMaster = ($user->access_role ?? 'professor') === 'master';

        $pedido->loadMissing('aluno');

        if (! $isMaster && (int) ($pedido->aluno->unidade_id ?? 0) !== (int) $user->unidade_id) {
            abort(403);
        }

        $role = $user->access_role ?? 'professor';
        if (in_array($role, ['aluno', 'professor'], true)) {
            abort(403, 'Somente direção ou master podem aprovar pedidos.');
        }

        if ($pedido->processado_em) {
            return response()->json([
                'message' => 'Pedido já processado.',
                'data' => $this->formatPedido($pedido->load(['aluno', 'produto'])),
            ]);
        }

        if (! in_array($pedido->status, ['pendente', 'aprovado'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Só é possível aprovar pedidos pendentes ou concluir aprovações pendentes de processamento.'],
            ]);
        }

        DB::transaction(function () use ($pedido) {
            if ($pedido->status === 'pendente') {
                $pedido->update(['status' => 'aprovado']);
            }

            PedidoAprovacao::aplicar($pedido->fresh());
        });

        $pedido->refresh()->load(['aluno:id,nome,unidade_id,coins,xp', 'produto:id,titulo,unidade_id,coins,quantidade,status']);

        return response()->json([
            'message' => 'Pedido aprovado. Coins descontados e estoque atualizado.',
            'data' => $this->formatPedido($pedido),
        ]);
    }

    private function authorizePedidoApp(User $user, Aluno $aluno, LojaItem $produto): void
    {
        $role = $user->access_role ?? 'professor';

        if ($role === 'aluno') {
            if (! $user->aluno || (int) $user->aluno->id !== (int) $aluno->id) {
                abort(403, 'Você só pode criar pedidos para o seu próprio usuário.');
            }

            return;
        }

        if ($role === 'master') {
            return;
        }

        if ((int) $aluno->unidade_id !== (int) $user->unidade_id || (int) $produto->unidade_id !== (int) $user->unidade_id) {
            abort(403);
        }

        if ($role === 'professor') {
            $ids = $user->turmas()->pluck('turmas.id')->all();
            if (! in_array((int) $aluno->turma_id, $ids, true)) {
                abort(403, 'Aluno fora das suas turmas.');
            }
        }
    }

    private function formatPedido(Pedido $pedido): array
    {
        return [
            'id' => $pedido->id,
            'id_aluno' => $pedido->aluno_id,
            'id_produto' => $pedido->loja_item_id,
            'quantidade' => (int) $pedido->qnt_atual,
            'coins' => (int) $pedido->coins,
            'status' => $pedido->status,
            'aluno' => $pedido->aluno ? [
                'id' => $pedido->aluno->id,
                'nome' => $pedido->aluno->nome,
                'coins' => (int) ($pedido->aluno->coins ?? 0),
            ] : null,
            'produto' => $pedido->produto ? [
                'id' => $pedido->produto->id,
                'titulo' => $pedido->produto->titulo,
                'quantidade' => (int) ($pedido->produto->quantidade ?? 0),
            ] : null,
            'created_at' => $pedido->created_at?->toIso8601String(),
        ];
    }
}
