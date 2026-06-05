<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PresenteRecebido extends Notification
{
    public function __construct(
        public string $remetenteNome,
        public string $itemTitulo,
        public ?string $emoji,
        public int $quantidade,
        public ?string $mensagem,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $itemLabel = trim(($this->emoji ?? '') . ' ' . $this->itemTitulo);

        return [
            'event' => 'presente',
            'titulo' => 'Você recebeu um presente!',
            'mensagem' => sprintf(
                '%s enviou %dx %s para você.%s',
                $this->remetenteNome,
                $this->quantidade,
                $itemLabel,
                $this->mensagem ? ' Mensagem: ' . $this->mensagem : ''
            ),
            'remetente_nome' => $this->remetenteNome,
            'item_titulo' => $this->itemTitulo,
            'emoji' => $this->emoji,
            'quantidade' => $this->quantidade,
            'mensagem' => $this->mensagem,
        ];
    }
}
