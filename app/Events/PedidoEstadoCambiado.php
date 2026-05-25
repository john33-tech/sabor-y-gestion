<?php

namespace App\Events;

use App\Models\Pedido;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se emite cuando un Pedido cambia de estado. El frontend del cliente
 * dueño del pedido lo escucha vía Reverb para mostrar toast + sonido
 * y actualizar el timeline en vivo.
 */
class PedidoEstadoCambiado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Pedido $pedido;
    public string $estadoAnterior;

    public function __construct(Pedido $pedido, string $estadoAnterior = '')
    {
        $this->pedido = $pedido;
        $this->estadoAnterior = $estadoAnterior;
    }

    public function broadcastOn(): array
    {
        return [
            // Canal del cliente dueño del pedido (toast en su pantalla).
            new Channel('cliente.' . $this->pedido->usuario_id . '.pedidos'),
            // Canal global para meseros/cajeros: filtran por estado="listo"
            // en el JS para no recibir todos los cambios.
            new Channel('pedidos.meseros'),
            // Canal de cocina: el kitchen display refresca para que los pedidos
            // cancelados/entregados/facturados desaparezcan en vivo.
            new Channel('pedidos.cocineros'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pedido.estado.cambiado';
    }

    public function broadcastWith(): array
    {
        return [
            'pedido_id'       => $this->pedido->id,
            'numero_pedido'   => $this->pedido->numero_pedido,
            'estado'          => $this->pedido->estado,
            'estado_anterior' => $this->estadoAnterior,
            'tipo_pedido'     => $this->pedido->tipo_pedido,
            'total'           => (float) $this->pedido->total,
        ];
    }
}
