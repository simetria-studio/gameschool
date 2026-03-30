<?php

namespace App\Support;

use App\Models\Pedido;
use App\Notifications\PedidoLojaAprovado;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoAprovacao
{
    /**
     * Desconta coins do aluno e reduz estoque do produto. Idempotente se o pedido já estiver aprovado e processado.
     */
    public static function aplicar(Pedido $pedido): void
    {
        DB::transaction(function () use ($pedido) {
            $pedido = Pedido::query()->whereKey($pedido->getKey())->lockForUpdate()->firstOrFail();
            $pedido->load(['aluno', 'produto']);

            if ($pedido->status !== 'aprovado') {
                throw ValidationException::withMessages([
                    'status' => ['O pedido precisa estar com status aprovado para processar o pagamento.'],
                ]);
            }

            if ($pedido->getAttribute('processado_em')) {
                return;
            }

            $custo = (int) $pedido->coins;
            $qnt = (int) $pedido->qnt_atual;

            if ($pedido->produto->status !== 'ativo') {
                throw ValidationException::withMessages([
                    'produto' => ['Produto não está mais ativo.'],
                ]);
            }

            if ((int) $pedido->produto->quantidade < $qnt) {
                throw ValidationException::withMessages([
                    'produto' => ['Estoque insuficiente para este pedido.'],
                ]);
            }

            if ((int) $pedido->aluno->coins < $custo) {
                throw ValidationException::withMessages([
                    'aluno' => ['O aluno não possui coins suficientes para este pedido.'],
                ]);
            }

            $pedido->aluno->decrement('coins', $custo);
            $pedido->produto->decrement('quantidade', $qnt);

            $pedido->forceFill(['processado_em' => now()])->save();

            $pedido->aluno->notify(new PedidoLojaAprovado(
                pedidoId: $pedido->id,
                produtoTitulo: (string) $pedido->produto->titulo,
                quantidade: $qnt,
            ));
        });
    }
}
