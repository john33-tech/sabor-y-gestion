<?php
// app/Http/Controllers/ComandaController.php
namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ComandaController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['detalles.plato', 'mesa', 'usuario'])
            ->whereIn('estado', ['pendiente', 'en_preparacion', 'listo']);
        
        // Filtrar por estado si se especifica
        if ($request->filled('estado') && $request->estado != 'todos') {
            $query->where('estado', $request->estado);
        }
        
        $comandas = $query->orderBy('created_at', 'asc')->paginate(12);
        
        $tipos = Pedido::getTipos();
        
        // Estadísticas
        $stats = [
            'total' => Pedido::whereIn('estado', ['pendiente', 'en_preparacion', 'listo'])->count(),
            'pendientes' => Pedido::where('estado', 'pendiente')->count(),
            'en_preparacion' => Pedido::where('estado', 'en_preparacion')->count(),
            'listos' => Pedido::where('estado', 'listo')->count()
        ];
        
        // Si es petición AJAX, devolver solo el HTML de las tarjetas
        if ($request->ajax()) {
            $html = view('comandas.partials.comanda-cards', compact('comandas', 'tipos'))->render();
            $pagination = $comandas->links()->render();
            
            return response()->json([
                'html' => $html,
                'pagination' => $pagination
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
            // 👇 Descontar inventario para cada plato en el pedido
            foreach ($comanda->detalles as $detalle) {
                if ($detalle->plato) {
                    $detalle->plato->descontarInventario();
                }
            }
            
            $comanda->estado = 'listo';
            $comanda->save();
            
            // Actualizar todos los detalles a "listo"
            $comanda->detalles()->update(['estado' => 'listo']);
            
            // 👇 LIMPIAR EL CACHÉ DEL CONTADOR DE STOCK BAJO
            Cache::forget('low_stock_count_direct');
            
            DB::commit();
            
            return redirect()->route('comandas.index')
                ->with('success', 'Pedido #' . $comanda->numero_pedido . ' marcado como listo. Inventario actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al marcar como listo: ' . $e->getMessage());
        }
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