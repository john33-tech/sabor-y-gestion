<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se emite cuando un pedido se EDITA (se quita/cambia un producto) y sigue en
 * cocina. La cocina reacciona con un refresco silencioso del kitchen display
 * (sin campana ni toast, porque es una corrección, no un pedido nuevo).
 */
class PedidoActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pedido;

    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('pedidos.cocineros'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pedido.actualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'id'            => $this->pedido->id,
            'numero_pedido' => $this->pedido->numero_pedido,
            'estado'        => $this->pedido->estado,
        ];
    }
}
