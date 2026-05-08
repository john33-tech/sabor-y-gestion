<?php
// app/Http/Controllers/PedidoController.php
namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Plato;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['mesa', 'usuario', 'detalles.plato']);
        
        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        if ($request->filled('tipo_pedido')) {
            $query->where('tipo_pedido', $request->tipo_pedido);
        }
        
        if ($request->filled('mesa_id')) {
            $query->where('mesa_id', $request->mesa_id);
        }
        
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        
        $pedidos = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $estados = Pedido::getEstados();
        $tipos = Pedido::getTipos();
        $mesas = Mesa::orderBy('numero_mesa')->get();
        
        return view('pedidos.index', compact('pedidos', 'estados', 'tipos', 'mesas'));
    }
    
    public function create(Request $request)
    {
        $platos = Plato::where('disponible', true)
            ->with('categoria')
            ->orderBy('categoria_id')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria.nombre');
        
        $mesas = Mesa::where('estado', 'libre')
            ->orderBy('area')
            ->orderBy('numero_mesa')
            ->get();
        
        $numeroPedido = $this->generarNumeroPedidoTemporal();
        
        // Si se selecciona una mesa específica
        $mesaSeleccionada = null;
        if ($request->has('mesa_id')) {
            $mesaSeleccionada = Mesa::find($request->mesa_id);
        }
        
        return view('pedidos.create', compact('platos', 'mesas', 'numeroPedido', 'mesaSeleccionada'));
    }
    
 



    // app/Http/Controllers/PedidoController.php

public function store(Request $request)
{
    $request->validate([
        'tipo_pedido' => 'required|in:mesa,delivery,para_llevar',
        'mesa_id' => 'required_if:tipo_pedido,mesa|nullable|exists:mesas,id',
        'cliente_nombre' => 'required_if:tipo_pedido,delivery,para_llevar|nullable|string|max:255',
        'cliente_telefono' => 'required_if:tipo_pedido,delivery,para_llevar|nullable|string|max:20',
        'direccion' => 'nullable|string|max:500',
        'items' => 'required|array|min:1',
        'items.*.plato_id' => 'required|exists:platos,id',
        'items.*.cantidad' => 'required|integer|min:1',
        'items.*.notas' => 'nullable|string',
        'notas' => 'nullable|string',
        'descuento' => 'nullable|numeric|min:0',
        'latitud' => 'required_if:tipo_pedido,delivery|nullable|numeric',
        'longitud' => 'required_if:tipo_pedido,delivery|nullable|numeric',
    ]);
    
    DB::beginTransaction();
    
    try {
        // Crear el pedido SIN número de pedido aún
        $pedido = new Pedido();
        $pedido->tipo_pedido = $request->tipo_pedido;
        $pedido->mesa_id = $request->tipo_pedido == 'mesa' ? $request->mesa_id : null;
        $pedido->cliente_nombre = $request->cliente_nombre ?? ($request->tipo_pedido == 'mesa' ? 'Cliente Mesa ' . $request->mesa_id : null);
        $pedido->cliente_telefono = $request->cliente_telefono;
        $pedido->direccion = $request->tipo_pedido == 'delivery' ? $request->direccion : null;
        $pedido->latitud = $request->latitud;
        $pedido->longitud = $request->longitud;
        $pedido->estado = Pedido::ESTADO_PENDIENTE;
        $pedido->descuento = $request->descuento ?? 0;
        $pedido->notas = $request->notas;
        $pedido->usuario_id = Auth::id();
        $pedido->save(); // Guardar primero sin número de pedido
        
        // Crear los detalles del pedido
        foreach ($request->items as $item) {
            $plato = Plato::find($item['plato_id']);
            
            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'plato_id' => $item['plato_id'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $plato->precio,
                'subtotal' => $plato->precio * $item['cantidad'],
                'notas' => $item['notas'] ?? null,
                'estado' => DetallePedido::ESTADO_PENDIENTE
            ]);
        }
        
        // Calcular totales
        $pedido->calcularTotales();
        
        // Generar número de pedido (después de tener los totales)
        $pedido->generarNumeroPedido();
        
        // Actualizar estado de la mesa si es pedido de mesa
        if ($pedido->tipo_pedido == 'mesa') {
            $mesa = Mesa::find($request->mesa_id);
            if ($mesa) {
                $mesa->update(['estado' => 'ocupado']);
            }
        }
        
        DB::commit();
        
        return redirect()->route('pedidos.show', $pedido)
            ->with('success', 'Pedido #' . $pedido->numero_pedido . ' creado exitosamente');
            
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error al crear el pedido: ' . $e->getMessage())->withInput();
    }
}



    
    public function show(Pedido $pedido)
    {
        $pedido->load(['mesa', 'usuario', 'detalles.plato.categoria', 'factura']);
        
        $estados = Pedido::getEstados();
        $tipos = Pedido::getTipos();  // <-- Agregar esta línea
        $estadosDetalle = DetallePedido::getEstados();
        
        return view('pedidos.show', compact('pedido', 'estados', 'tipos', 'estadosDetalle'));
    }
    
    public function edit(Pedido $pedido)
    {
        if (!in_array($pedido->estado, [Pedido::ESTADO_PENDIENTE, Pedido::ESTADO_EN_PREPARACION])) {
            return redirect()->route('pedidos.index')
                ->with('error', 'No se puede editar un pedido en estado ' . $pedido->estado);
        }
        
        $platos = Plato::where('disponible', true)
            ->with('categoria')
            ->orderBy('categoria_id')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria.nombre');
        
        $mesas = Mesa::orderBy('numero_mesa')->get();
        
        $pedido->load('detalles.plato');
        
        return view('pedidos.edit', compact('pedido', 'platos', 'mesas'));
    }
    
    public function update(Request $request, Pedido $pedido)
    {
        if (!in_array($pedido->estado, [Pedido::ESTADO_PENDIENTE, Pedido::ESTADO_EN_PREPARACION])) {
            return redirect()->route('pedidos.index')
                ->with('error', 'No se puede modificar un pedido en estado ' . $pedido->estado);
        }
        
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.plato_id' => 'required|exists:platos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.notas' => 'nullable|string',
            'notas' => 'nullable|string',
            'descuento' => 'nullable|numeric|min:0'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Actualizar datos del pedido
            $pedido->descuento = $request->descuento ?? 0;
            $pedido->notas = $request->notas;
            $pedido->save();
            
            // Eliminar detalles existentes
            $pedido->detalles()->delete();
            
            // Crear nuevos detalles
            foreach ($request->items as $item) {
                $plato = Plato::find($item['plato_id']);
                
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'plato_id' => $item['plato_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $plato->precio,
                    'subtotal' => $plato->precio * $item['cantidad'],
                    'notas' => $item['notas'] ?? null,
                    'estado' => DetallePedido::ESTADO_PENDIENTE
                ]);
            }
            
            // Recalcular totales
            $pedido->calcularTotales();
            
            DB::commit();
            
            return redirect()->route('pedidos.show', $pedido)
                ->with('success', 'Pedido actualizado exitosamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el pedido: ' . $e->getMessage());
        }
    }
    
    public function destroy(Pedido $pedido)
    {
        if ($pedido->estado != Pedido::ESTADO_PENDIENTE) {
            return redirect()->route('pedidos.index')
                ->with('error', 'Solo se pueden eliminar pedidos pendientes');
        }
        
        DB::beginTransaction();
        
        try {
            // Liberar mesa si estaba ocupada
            if ($pedido->tipo_pedido == 'mesa' && $pedido->mesa) {
                $pedido->mesa->update(['estado' => 'libre']);
            }
            
            $pedido->detalles()->delete();
            $pedido->delete();
            
            DB::commit();
            
            return redirect()->route('pedidos.index')
                ->with('success', 'Pedido eliminado exitosamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el pedido');
        }
    }
    
   
    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(Pedido::getEstados()))
        ]);
        
        $estadoAnterior = $pedido->estado;
        $pedido->actualizarEstado($request->estado);
        
        // Si la petición es AJAX, retornar JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'estado' => $request->estado,
                'mensaje' => "Pedido #{$pedido->numero_pedido} actualizado"
            ]);
        }
        
        return redirect()->route('pedidos.show', $pedido)
            ->with('success', "Pedido #{$pedido->numero_pedido} cambiado de {$estadoAnterior} a {$request->estado}");
    }
    
    public function cambiarEstadoDetalle(Request $request, DetallePedido $detalle)
    {
        $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(DetallePedido::getEstados()))
        ]);
        
        $detalle->actualizarEstado($request->estado);
        
        return response()->json([
            'success' => true,
            'estado' => $request->estado,
            'mensaje' => 'Estado actualizado correctamente'
        ]);
    }


    public function misPedidos()
    {
        // Obtener solo los pedidos del usuario autenticado
        $pedidos = Pedido::with(['mesa', 'detalles.plato'])
            ->where('usuario_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $estados = Pedido::getEstados();
        $tipos = Pedido::getTipos();
        
        return view('pedidos.misPedidos', compact('pedidos', 'estados', 'tipos'));
    }

    public function imprimir(Pedido $pedido)
    {
        $pedido->load(['mesa', 'detalles.plato']);
        return view('pedidos.ticket', compact('pedido'));
    }
    
    private function generarNumeroPedidoTemporal()
    {
        $ultimo = Pedido::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->numero_pedido, -4)) + 1 : 1;
        return 'PED-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
}