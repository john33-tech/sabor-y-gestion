<?php
// app/Http/Controllers/ComandaController.php
namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use App\Models\Consumo; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ComandaController extends Controller
{
    public function index(Request $request)
    {
        // visibleEnCocina(): muestra pendiente/en_preparacion/listo, pero los
        // pedidos "para llevar" solo cuando ya están PAGADOS (primero paga,
        // luego cocina). Mesa y delivery entran enseguida.
        $query = Pedido::with(['detalles.plato', 'mesa', 'usuario'])
            ->visibleEnCocina();

        // Filtro Solo Hoy (grupo)
        if ($request->boolean('soloHoy')) {
            $query->whereDate('created_at', now()->today());
        }

        // Filtro Solo Pendientes (grupo)
        if ($request->boolean('soloPendientes')) {
            $query->where('estado', 'pendiente');
        }

        // Filtrar por estado si se especifica manualmente
        if ($request->filled('estado') && $request->estado != 'todos') {
            $query->where('estado', $request->estado);
        }

        $comandas = $query->orderBy('created_at', 'asc')->paginate(12);

        $tipos = Pedido::getTipos();

        // Estadísticas: mismo criterio que la cocina (visibleEnCocina) + filtro
        // opcional "solo hoy" del grupo.
        $soloHoy = $request->boolean('soloHoy');
        $statBase = fn() => Pedido::visibleEnCocina()
            ->when($soloHoy, fn($q) => $q->whereDate('created_at', now()->today()));
        $stats = [
            'total' => $statBase()->whereIn('estado', ['pendiente', 'en_preparacion', 'listo'])->count(),
            'pendientes' => $statBase()->where('estado', 'pendiente')->count(),
            'en_preparacion' => $statBase()->where('estado', 'en_preparacion')->count(),
            'listos' => $statBase()->where('estado', 'listo')->count()
        ];
        
        // Si es petición AJAX, devolver solo el HTML de las tarjetas
        if ($request->ajax()) {
            $soloPlatos = $request->boolean('soloPlatos');
            $html = view('comandas.partials.comanda-cards', compact('comandas', 'tipos', 'soloPlatos'))->render();
            $pagination = $comandas->links()->render();
            
            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
                'stats' => $stats,
            ]);
        }
        
        return view('comandas.index', compact('comandas', 'tipos', 'stats'));
    }
    
    public function iniciarPreparacion(Pedido $comanda)
    {
        if ($comanda->estado !== 'pendiente') {
            return redirect()->back()->with('error', 'Este pedido ya no está pendiente');
        }
        
        DB::beginTransaction();
        try {
            $comanda->estado = 'en_preparacion';
            $comanda->save();
            
            // Actualizar todos los detalles a "en_preparacion"
            $comanda->detalles()->update(['estado' => 'en_preparacion']);
            
            DB::commit();

            // Emitir evento
            event(new \App\Events\PedidoEstadoActualizado($comanda));
            
            return redirect()->route('comandas.index')
                ->with('success', 'Preparación iniciada para el pedido #' . $comanda->numero_pedido);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al iniciar la preparación');
        }
    }
    










   public function marcarListo(Pedido $comanda)
    {
        if (!in_array($comanda->estado, ['pendiente', 'en_preparacion'])) {
            return redirect()->back()->with('error', 'No se puede marcar como listo este pedido');
        }
        
        DB::beginTransaction();
        try {
            // Descontar inventario
            foreach ($comanda->detalles as $detalle) {
                if ($detalle->plato) {
                    $detalle->plato->descontarInventario($detalle->cantidad);
                }
            }
            
            $comanda->estado = 'listo';
            $comanda->save();
            
            // Actualizar todos los detalles a "listo"
            $comanda->detalles()->update(['estado' => 'listo']);
            
            // GUARDAR EN CONSUMOS
            $this->guardarConsumo($comanda);
            
            // Limpiar caché
            Cache::forget('low_stock_count_direct');
            
            DB::commit();
            
            // Emitir evento para tiempo real
            event(new \App\Events\PedidoEstadoActualizado($comanda));
            
            return redirect()->route('comandas.index')
                ->with('success', 'Pedido #' . $comanda->numero_pedido . ' marcado como listo. Inventario actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al marcar como listo: ' . $e->getMessage());
        }
    }
    
    private function guardarConsumo(Pedido $pedido)
    {
        // Preparar detalles para guardar
        $detalles = [];
        foreach ($pedido->detalles as $detalle) {
            $detalles[] = [
                'plato_id' => $detalle->plato_id,
                'plato_nombre' => $detalle->plato->nombre,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'subtotal' => $detalle->subtotal,
                'notas' => $detalle->notas
            ];
        }
        
        Consumo::create([
            'numero_pedido' => $pedido->numero_pedido,
            'pedido_id' => $pedido->id,
            'usuario_id' => $pedido->usuario_id,
            'tipo_pedido' => $pedido->tipo_pedido,
            'estado' => 'completado',
            'subtotal' => $pedido->subtotal,
            'impuesto' => $pedido->impuesto,
            'descuento' => $pedido->descuento,
            'total' => $pedido->total,
            'detalles' => $detalles,
            'fecha_consumo' => now()
        ]);
    }



    
    public function actualizarDetalle(Request $request, DetallePedido $detalle)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_preparacion,listo,entregado,cancelado'
        ]);
        
        $detalle->estado = $request->estado;
        $detalle->save();
        
        // Verificar si todos los detalles están en el mismo estado para actualizar el pedido
        $pedido = $detalle->pedido;
        
        if ($request->estado === 'listo') {
            $todosListos = $pedido->detalles->every(fn($d) => $d->estado === 'listo');
            if ($todosListos && $pedido->estado !== 'listo') {
                $pedido->estado = 'listo';
                $pedido->save();
            }
        } elseif ($request->estado === 'en_preparacion') {
            if ($pedido->estado === 'pendiente') {
                $pedido->estado = 'en_preparacion';
                $pedido->save();
            }
        }
        
        // Emitir evento para tiempo real
        event(new \App\Events\PedidoEstadoActualizado($pedido));
        
        return response()->json([
            'success' => true,
            'estado' => $detalle->estado,
            'mensaje' => 'Estado actualizado correctamente'
        ]);
    }
    
    public function print(Pedido $comanda)
    {
        $comanda->load(['detalles.plato', 'mesa', 'usuario']);
        $tipos = Pedido::getTipos();
        return view('comandas.print', compact('comanda', 'tipos'));
    }
}