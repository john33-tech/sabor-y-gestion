<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se emite cuando un Pedido es eliminado (hard delete). El kitchen display
 * lo escucha en el canal "pedidos.cocineros" para que el card desaparezca
 * en vivo sin necesidad de recargar.
 */
class PedidoEliminado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $pedidoId;
    public string $numeroPedido;

    public function __construct(int $pedidoId, string $numeroPedido)
    {
        $this->pedidoId = $pedidoId;
        $this->numeroPedido = $numeroPedido;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('pedidos.cocineros'),
            new Channel('pedidos.meseros'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pedido.eliminado';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id'     => $this->pedidoId,
            'numero_pedido' => $this->numeroPedido,
        ];
    }
}
