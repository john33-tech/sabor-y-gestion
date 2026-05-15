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
use Illuminate\Support\Facades\Cache;

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
            ->with(['categoria', 'ingredientes.inventario'])
            ->orderBy('categoria_id')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria.nombre');

        // Procesar cada plato para agregar información de stock
        $platosConStock = [];
        foreach ($platos as $categoria => $platosCategoria) {
            $platosProcesados = [];
            foreach ($platosCategoria as $plato) {
                // Verificar stock para 1 unidad
                $tieneStock = true;
                $stockInsuficiente = [];

                // Obtener detalles de ingredientes con stock insuficiente
                if (!$tieneStock) {
                    foreach ($plato->ingredientes as $ingrediente) {
                        $inventario = $ingrediente->inventario;
                        $cantidadNecesaria = $ingrediente->pivot->cantidad;

                        if (!$inventario || $inventario->cantidad_actual < $cantidadNecesaria) {
                            $stockInsuficiente[] = [
                                'nombre' => $ingrediente->nombre,
                                'disponible' => $inventario?->cantidad_actual ?? 0,
                                'necesario' => $cantidadNecesaria,
                                'unidad' => $ingrediente->unidad_medida
                            ];
                        }
                    }
                }
                // Verificar cada ingrediente del plato
                foreach ($plato->ingredientes as $ingrediente) {
                    $inventario = $ingrediente->inventario;
                    $cantidadNecesaria = $ingrediente->pivot->cantidad;

                    // Si no tiene inventario registrado o el stock es insuficiente
                    if (!$inventario) {
                        $tieneStock = false;
                        $stockInsuficiente[] = [
                            'nombre' => $ingrediente->nombre,
                            'disponible' => 0,
                            'necesario' => $cantidadNecesaria,
                            'unidad' => $ingrediente->unidad_medida,
                            'motivo' => 'Sin inventario registrado'
                        ];
                    } elseif ($inventario->cantidad_actual < $cantidadNecesaria) {
                        $tieneStock = false;
                        $stockInsuficiente[] = [
                            'nombre' => $ingrediente->nombre,
                            'disponible' => $inventario->cantidad_actual,
                            'necesario' => $cantidadNecesaria,
                            'unidad' => $ingrediente->unidad_medida,
                            'motivo' => 'Stock insuficiente'
                        ];
                    }
                }

                // Crear un objeto con los datos necesarios
                $platosProcesados[] = (object)[
                    'id' => $plato->id,
                    'nombre' => $plato->nombre,
                    'precio' => $plato->precio,
                    'descripcion' => $plato->descripcion,
                    'categoria_id' => $plato->categoria_id,
                    'tiene_stock' => $tieneStock,
                    'stock_insuficiente' => $stockInsuficiente,
                    'ingredientes' => $plato->ingredientes
                ];

                // Si el plato no tiene ingredientes registrados, también sin stock
                if ($plato->ingredientes->count() === 0) {
                    $tieneStock = false;
                    $stockInsuficiente[] = [
                        'nombre' => 'Sin ingredientes',
                        'disponible' => 0,
                        'necesario' => 1,
                        'unidad' => 'N/A',
                        'motivo' => 'El plato no tiene ingredientes registrados'
                    ];
                }

                // Agregar propiedades al plato
                $plato->tiene_stock = $tieneStock;
                $plato->stock_insuficiente = $stockInsuficiente;
                $platosProcesados[] = $plato;
            }
            $platosConStock[$categoria] = $platosProcesados;
        }

        $mesas = Mesa::where('estado', 'libre')
            ->orderBy('area')
            ->orderBy('numero_mesa')
            ->get();

        $numeroPedido = $this->generarNumeroPedidoTemporal();

        $mesaSeleccionada = null;
        if ($request->has('mesa_id')) {
            $mesaSeleccionada = Mesa::find($request->mesa_id);
        }

        return view('pedidos.create', compact('platosConStock', 'mesas', 'numeroPedido', 'mesaSeleccionada'));
            }



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

        // Verificar stock antes de proceder
        $stockCheck = $this->verificarStockItems($request->items);
        if (!$stockCheck['success']) {
            return back()->with('error', $stockCheck['mensaje'])->withInput();
        }

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

            // Generar factura automática
            $pedido->generarOrUpdateFactura();

            // Emitir evento para tiempo real (Notificar a cocineros)
            event(new \App\Events\PedidoCreado($pedido));

            // Actualizar estado de la mesa si es pedido de mesa
            if ($pedido->tipo_pedido == 'mesa') {
                $mesa = Mesa::find($request->mesa_id);
                if ($mesa) {
                    $mesa->update(['estado' => 'ocupado']);
                }
            }

            DB::commit();

            // Si es cliente
            if (Auth::user()->role === 'cliente') {

                return redirect()->route('pedidos.misPedidos')
                    ->with('success', '✅ Pedido #' . $pedido->numero_pedido . ' creado exitosamente');
            }

            // Admin / mesero
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
        if (!in_array($pedido->estado, [
            Pedido::ESTADO_PENDIENTE,
            Pedido::ESTADO_EN_PREPARACION
        ])) {
            return redirect()->route('pedidos.index')
                ->with('error', 'No se puede modificar un pedido en estado ' . $pedido->estado);
        }

        $request->validate([
            'tipo_pedido'              => 'required|in:mesa,delivery,para_llevar',
            'mesa_id'                  => 'required_if:tipo_pedido,mesa|nullable|exists:mesas,id',
            'cliente_nombre'           => 'required_if:tipo_pedido,delivery,para_llevar|nullable|string|max:255',
            'cliente_telefono'         => 'required_if:tipo_pedido,delivery,para_llevar|nullable|string|max:20',
            'direccion'                => 'required_if:tipo_pedido,delivery|nullable|string|max:500',
            'latitud'                  => 'required_if:tipo_pedido,delivery|nullable|numeric',
            'longitud'                 => 'required_if:tipo_pedido,delivery|nullable|numeric',
            'items'                    => 'required|array|min:1',
            'items.*.plato_id'         => 'required|exists:platos,id',
            'items.*.cantidad'         => 'required|integer|min:1',
            'items.*.notas'            => 'nullable|string',
            'notas'                    => 'nullable|string',
            'descuento'                => 'nullable|numeric|min:0',
        ]);

        // Verificar stock antes de proceder
        $stockCheck = $this->verificarStockItems($request->items);
        if (!$stockCheck['success']) {
            return back()->with('error', $stockCheck['mensaje']);
        }

        DB::beginTransaction();

        try {
            // ── MESA: liberar la anterior ──────────────────────────────────────
            if ($pedido->tipo_pedido === 'mesa' && $pedido->mesa_id) {
                $mesaAnterior = Mesa::find($pedido->mesa_id);
                if ($mesaAnterior) {
                    $mesaAnterior->update(['estado' => 'libre']);
                }
            }

            // ── INVENTARIO: revertir si el pedido ya lo había descontado ───────
            if (in_array($pedido->estado, [Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])) {
                $detallesAntiguos = $pedido->detalles()->with('plato.ingredientes')->get();
                foreach ($detallesAntiguos as $detalle) {
                    if ($detalle->plato) {
                        $detalle->plato->revertirInventario($detalle->cantidad);
                    }
                }
            }

            // ── ACTUALIZAR DATOS DEL PEDIDO ────────────────────────────────────
            $pedido->tipo_pedido = $request->tipo_pedido;

            $pedido->mesa_id = $request->tipo_pedido === 'mesa'
                ? $request->mesa_id
                : null;

            $pedido->cliente_nombre = $request->cliente_nombre
                ?? ($request->tipo_pedido === 'mesa'
                    ? 'Cliente Mesa ' . $request->mesa_id
                    : null);

            $pedido->cliente_telefono = $request->cliente_telefono;

            $pedido->direccion = $request->tipo_pedido === 'delivery'
                ? $request->direccion
                : null;
            $pedido->latitud = $request->tipo_pedido === 'delivery'
                ? $request->latitud
                : null;
            $pedido->longitud = $request->tipo_pedido === 'delivery'
                ? $request->longitud
                : null;

            $pedido->descuento = $request->descuento ?? 0;
            $pedido->notas     = $request->notas;
            $pedido->save();

            // ── MESA: ocupar la nueva ──────────────────────────────────────────
            if ($pedido->tipo_pedido === 'mesa' && $request->mesa_id) {
                $mesaNueva = Mesa::find($request->mesa_id);
                if ($mesaNueva) {
                    $mesaNueva->update(['estado' => 'ocupado']);
                }
            }

            // ── DETALLES: eliminar anteriores y crear nuevos ───────────────────
            $pedido->detalles()->delete();

            $nuevosItems = [];
            foreach ($request->items as $item) {
                $plato = Plato::find($item['plato_id']);

                $nuevosItems[] = [
                    'pedido_id'      => $pedido->id,
                    'plato_id'       => $item['plato_id'],
                    'cantidad'       => $item['cantidad'],
                    'precio_unitario'=> $plato->precio,
                    'subtotal'       => $plato->precio * $item['cantidad'],
                    'notas'          => $item['notas'] ?? null,
                    'estado'         => DetallePedido::ESTADO_PENDIENTE,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            DetallePedido::insert($nuevosItems);

            // ── INVENTARIO: descontar el nuevo si aplica ───────────────────────
            if (in_array($pedido->estado, [Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])) {
                foreach ($request->items as $item) {
                    $plato = Plato::find($item['plato_id']);
                    if ($plato) {
                        $plato->descontarInventario($item['cantidad']);
                    }
                }
            }

            // ── TOTALES, FACTURA Y CACHÉ ───────────────────────────────────────
            $pedido->calcularTotales();
            $pedido->generarOrUpdateFactura();

            Cache::forget('low_stock_count_direct');

            DB::commit();

            // ── COMANDA ────────────────────────────────────────────────────────
            $this->actualizarComanda($pedido);

            return redirect()->route('pedidos.show', $pedido)
                ->with('success', 'Pedido actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Error al actualizar el pedido: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Método auxiliar para actualizar la comanda
    private function actualizarComanda(Pedido $pedido)
    {
        // Si el pedido está en comanda (pendiente o en preparación)
        if (in_array($pedido->estado, ['pendiente', 'en_preparacion', 'listo'])) {
            // Opcional: Notificar a cocina que el pedido fue actualizado
            //event(new \App\Events\PedidoActualizado($pedido));
        }
    }
    // Método para verificar stock antes de actualizar o guardar
    private function verificarStockItems($items)
    {
        $ingredientesNecesarios = [];

        foreach ($items as $item) {
            $plato = Plato::with('ingredientes.inventario')->find($item['plato_id']);
            if (!$plato) {
                return ['success' => false, 'mensaje' => 'Un plato seleccionado no existe.'];
            }
            if ($plato->ingredientes->count() === 0) {
                return ['success' => false, 'mensaje' => 'El plato ' . $plato->nombre . ' no tiene ingredientes registrados y no puede prepararse.'];
            }

            foreach ($plato->ingredientes as $ingrediente) {
                $id = $ingrediente->id;
                $cantidadNecesaria = $ingrediente->pivot->cantidad * $item['cantidad'];

                if (!isset($ingredientesNecesarios[$id])) {
                    $ingredientesNecesarios[$id] = [
                        'nombre' => $ingrediente->nombre,
                        'necesario' => 0,
                        'disponible' => $ingrediente->inventario ? $ingrediente->inventario->cantidad_actual : 0,
                        'unidad' => $ingrediente->unidad_medida
                    ];
                }

                $ingredientesNecesarios[$id]['necesario'] += $cantidadNecesaria;
            }
        }

        foreach ($ingredientesNecesarios as $req) {
            if ($req['disponible'] < $req['necesario']) {
                return [
                    'success' => false,
                    'mensaje' => 'Stock insuficiente de ' . $req['nombre'] . '. Se requiere ' . number_format($req['necesario'], 2) . ' ' . $req['unidad'] . ' pero solo hay ' . number_format($req['disponible'], 2) . ' ' . $req['unidad'] . ' disponibles.'
                ];
            }
        }

        return ['success' => true];
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

    DB::beginTransaction();

    try {
        // Si se marca como "listo" o "entregado", descontar inventario y guardar consumo
        if (in_array($request->estado, [Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])
            && !in_array($estadoAnterior, [Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])) {

            foreach ($pedido->detalles as $detalle) {
                if ($detalle->plato) {
                    $detalle->plato->descontarInventario($detalle->cantidad);
                }
            }

            // Guardar consumo
            $this->guardarConsumo($pedido);
        }

        // Si se cancela un pedido que ya había descontado inventario, revertir
        if ($request->estado == Pedido::ESTADO_CANCELADO
            && in_array($estadoAnterior, [Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])) {

            foreach ($pedido->detalles as $detalle) {
                if ($detalle->plato) {
                    $detalle->plato->revertirInventario($detalle->cantidad);
                }
            }
        }

        $pedido->actualizarEstado($request->estado);

        // Emitir evento para actualizar notificaciones en tiempo real
        event(new \App\Events\PedidoEstadoActualizado($pedido));

        // Limpiar caché
        Cache::forget('low_stock_count_direct');

        DB::commit();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'estado' => $request->estado,
                'mensaje' => "Pedido #{$pedido->numero_pedido} actualizado"
            ]);
        }

        return redirect()->route('pedidos.show', $pedido)
            ->with('success', "Pedido #{$pedido->numero_pedido} cambiado de {$estadoAnterior} a {$request->estado}");

    } catch (\Exception $e) {
        DB::rollBack();

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()
            ->with('error', 'Error al cambiar estado: ' . $e->getMessage());
    }
}

private function guardarConsumo(Pedido $pedido)
{
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

    // Verificar si ya existe un consumo para este pedido
    $consumoExistente = Consumo::where('pedido_id', $pedido->id)->first();

    if (!$consumoExistente) {
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
}


    public function cambiarEstadoDetalle(Request $request, DetallePedido $detalle)
    {
        $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(DetallePedido::getEstados()))
        ]);

        DB::beginTransaction();

        try {
            $estadoAnterior = $detalle->estado;

            // Si se marca como listo, descontar inventario
            if ($request->estado == DetallePedido::ESTADO_LISTO
                && $estadoAnterior != DetallePedido::ESTADO_LISTO) {

                if ($detalle->plato) {
                    $detalle->plato->descontarInventario($detalle->cantidad);
                }
            }

            // Si se revierte de listo a otro estado, revertir inventario
            if ($estadoAnterior == DetallePedido::ESTADO_LISTO
                && $request->estado != DetallePedido::ESTADO_LISTO) {

                if ($detalle->plato) {
                    $detalle->plato->revertirInventario($detalle->cantidad);
                }
            }

            $detalle->actualizarEstado($request->estado);

            // Verificar si todos los detalles están listos para actualizar el pedido
            $pedido = $detalle->pedido;
            $todosListos = $pedido->detalles->every(fn($d) => $d->estado === DetallePedido::ESTADO_LISTO);

            if ($todosListos && !in_array($pedido->estado, [Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])) {
                $pedido->estado = Pedido::ESTADO_LISTO;
                $pedido->save();
            }

            // Limpiar caché
            Cache::forget('low_stock_count_direct');

            DB::commit();

            return response()->json([
                'success' => true,
                'estado' => $request->estado,
                'mensaje' => 'Estado actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'mensaje' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

   public function pedidoCliente()
{
    $platos = Plato::where('disponible', true)
        ->with(['categoria', 'ingredientes.inventario'])
        ->orderBy('categoria_id')
        ->orderBy('nombre')
        ->get()
        ->groupBy('categoria.nombre');

    // Procesar stock igual que create()
    $platosConStock = [];

    foreach ($platos as $categoria => $platosCategoria) {

        $platosProcesados = [];

        foreach ($platosCategoria as $plato) {

            $tieneStock = true;
            $stockInsuficiente = [];

            foreach ($plato->ingredientes as $ingrediente) {

                $inventario = $ingrediente->inventario;
                $cantidadNecesaria = $ingrediente->pivot->cantidad;

                if (!$inventario) {

                    $tieneStock = false;

                    $stockInsuficiente[] = [
                        'nombre' => $ingrediente->nombre,
                        'motivo' => 'Sin inventario registrado'
                    ];

                } elseif ($inventario->cantidad_actual < $cantidadNecesaria) {

                    $tieneStock = false;

                    $stockInsuficiente[] = [
                        'nombre' => $ingrediente->nombre,
                        'disponible' => $inventario->cantidad_actual,
                        'necesario' => $cantidadNecesaria,
                        'unidad' => $ingrediente->unidad_medida,
                        'motivo' => 'Stock insuficiente'
                    ];
                }
            }

            $plato->tiene_stock = $tieneStock;
            $plato->stock_insuficiente = $stockInsuficiente;

            $platosProcesados[] = $plato;
        }

        $platosConStock[$categoria] = $platosProcesados;
    }

    $numeroPedido = $this->generarNumeroPedidoTemporal();

    $usuario = Auth::user();

    return view('pedidos.cliente', compact(
        'platosConStock',
        'numeroPedido',
        'usuario'
    ));
}
// ==========================
// CLIENTE - VER PEDIDO
// ==========================
public function showCliente(Pedido $pedido)
{
    if ($pedido->usuario_id != Auth::id()) {
        abort(403);
    }

    $pedido->load(['detalles.plato']);

    return view('pedidos.showCliente', compact('pedido'));
}

// ==========================
// CLIENTE - EDITAR PEDIDO
// ==========================
public function editCliente(Pedido $pedido)
{
    if ($pedido->usuario_id != Auth::id()) {
        abort(403);
    }

    if ($pedido->estado != Pedido::ESTADO_PENDIENTE) {
        return redirect()->back()
            ->with('error', 'Solo puedes editar pedidos pendientes');
    }

    $platos = Plato::where('disponible', true)
        ->with('categoria')
        ->orderBy('nombre')
        ->get()
        ->groupBy('categoria.nombre');

    return view('pedidos.editCliente', compact('pedido', 'platos'));
}

public function updateCliente(Request $request, Pedido $pedido)
{
    // Verificar propietario
    if ($pedido->usuario_id != Auth::id()) {
        abort(403);
    }

    // Solo pendientes
    if ($pedido->estado != Pedido::ESTADO_PENDIENTE) {

        return redirect()
            ->route('pedidos.misPedidos')
            ->with('error', 'Solo puedes editar pedidos pendientes');
    }

    $request->validate([

        'tipo_pedido' => 'required|in:delivery,para_llevar',

        'cliente_nombre' => 'required|string|max:255',

        'cliente_telefono' => 'required|string|max:20',

        'direccion' => 'nullable|string|max:500',
        'latitud' => 'required_if:tipo_pedido,delivery|nullable|numeric',
        'longitud' => 'required_if:tipo_pedido,delivery|nullable|numeric',

        'items' => 'required|array|min:1',

        'items.*.plato_id' => 'required|exists:platos,id',

        'items.*.cantidad' => 'required|integer|min:1',

        'items.*.notas' => 'nullable|string',
    ]);

    // Verificar stock antes de proceder
    $stockCheck = $this->verificarStockItems($request->items);
    if (!$stockCheck['success']) {
        return back()->with('error', $stockCheck['mensaje'])->withInput();
    }

    DB::beginTransaction();

    try {

        // Actualizar datos
        $pedido->cliente_nombre = $request->cliente_nombre;

        $pedido->cliente_telefono = $request->cliente_telefono;

        $pedido->direccion = $request->direccion;
        $pedido->latitud = $request->latitud;
        $pedido->longitud = $request->longitud;

        $pedido->notas = $request->notas;

        $pedido->save();

        // Eliminar detalles anteriores
        $pedido->detalles()->delete();

        // Crear nuevos detalles
        foreach ($request->items as $item) {

            $plato = Plato::find($item['plato_id']);

            DetallePedido::create([

                'pedido_id' => $pedido->id,

                'plato_id' => $plato->id,

                'cantidad' => $item['cantidad'],

                'precio_unitario' => $plato->precio,

                'subtotal' => $plato->precio * $item['cantidad'],

                'notas' => $item['notas'] ?? null,

                'estado' => DetallePedido::ESTADO_PENDIENTE
            ]);
        }

        // RECALCULAR
        $pedido->calcularTotales();

        DB::commit();

        return redirect()
            ->route('pedidos.show.cliente', $pedido)
            ->with('success', 'Pedido actualizado correctamente');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->with('error', 'Error al actualizar pedido')
            ->withInput();
    }
}
// ==========================
// CLIENTE - CANCELAR PEDIDO
// ==========================
public function destroyCliente(Pedido $pedido)
{
    // Verificar propietario
    if ($pedido->usuario_id != Auth::id()) {
        abort(403);
    }

    // Solo pedidos pendientes
    if ($pedido->estado != Pedido::ESTADO_PENDIENTE) {

        return redirect()
            ->route('pedidos.misPedidos')
            ->with('error', 'Solo puedes cancelar pedidos pendientes');
    }

    DB::beginTransaction();

    try {

        // Liberar mesa si existe
        if ($pedido->tipo_pedido == 'mesa' && $pedido->mesa) {

            $pedido->mesa->update([
                'estado' => 'libre'
            ]);
        }

        // Eliminar detalles
        $pedido->detalles()->delete();

        // Eliminar pedido
        $pedido->delete();

        DB::commit();

        return redirect()
            ->route('pedidos.misPedidos')
            ->with('success', 'Pedido cancelado correctamente');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()
            ->route('pedidos.misPedidos')
            ->with('error', 'Error al cancelar pedido');
    }
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
