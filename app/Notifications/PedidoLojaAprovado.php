<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PedidoLojaAprovado extends Notification
{
    public function __construct(
        public int $pedidoId,
        public string $produtoTitulo,
        public int $quantidade,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $q = $this->quantidade;
        $titulo = $this->produtoTitulo;

        $mensagem = $q > 1
            ? sprintf(
                'Seu pedido de %d unidades de "%s" foi aprovado. Você já pode retirar na secretaria.',
                $q,
                $titulo
            )
            : sprintf(
                'Seu pedido de "%s" foi aprovado. Você já pode retirar na secretaria.',
                $titulo
            );

        return [
            'event' => 'pedido_loja',
            'titulo' => 'Pedido aprovado',
            'mensagem' => $mensagem,
            'pedido_id' => $this->pedidoId,
            'produto_titulo' => $titulo,
            'quantidade' => $q,
        ];
    }
}
