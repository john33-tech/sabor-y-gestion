<?php
// app/Http/Controllers/PedidoController.php
namespace App\Http\Controllers;

use App\Events\PedidoEliminado;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Plato;
use App\Models\Mesa;
use App\Models\Consumo;
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
                $tieneStock = true;
                $stockInsuficiente = [];

                // Sin ingredientes registrados -> no se puede preparar.
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

                // Verificar stock de cada ingrediente del plato.
                foreach ($plato->ingredientes as $ingrediente) {
                    $inventario = $ingrediente->inventario;
                    $cantidadNecesaria = $ingrediente->pivot->cantidad;

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

                // UN SOLO append por plato (antes se agregaba 2 veces -> menú duplicado).
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
            'cliente_email' => 'nullable|email|max:255',
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
            $pedido->cliente_email = $request->cliente_email;
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


public function storeCliente(Request $request)
{
    $request->validate([
        'tipo_pedido' => 'required|in:delivery,para_llevar',
        'cliente_nombre' => 'required|string|max:255',
        'cliente_telefono' => 'required|string|max:20',
        'direccion' => 'required_if:tipo_pedido,delivery|nullable|string|max:500',
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
        // Crear el pedido
        $pedido = new Pedido();
        $pedido->tipo_pedido = $request->tipo_pedido;
        $pedido->cliente_nombre = $request->cliente_nombre;
        $pedido->cliente_telefono = $request->cliente_telefono;
        $pedido->direccion = $request->tipo_pedido == 'delivery' ? $request->direccion : null;
        $pedido->latitud = $request->latitud;
        $pedido->longitud = $request->longitud;
        $pedido->estado = Pedido::ESTADO_PENDIENTE;
        $pedido->descuento = $request->descuento ?? 0;
        $pedido->notas = $request->notas;
        $pedido->usuario_id = Auth::id();
        $pedido->save();

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

        DB::commit();

        return redirect()->route('pedidos.misPedidos')
            ->with('success', '✅ Pedido #' . $pedido->numero_pedido . ' creado exitosamente');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error al crear el pedido: ' . $e->getMessage())->withInput();
    }
}








    public function show(Pedido $pedido)
    {
        $pedido->load(['mesa', 'usuario', 'detalles.plato.categoria', 'factura']);

        $estados = Pedido::getEstados();
        $tipos = Pedido::getTipos();
        $estadosDetalle = DetallePedido::getEstados();

        // Para D4: mientras la cuenta esté ABIERTA (no pagada ni cerrada),
        // cargar platos disponibles para que el panel "Agregar productos" se
        // pueda renderizar — aunque el pedido ya esté listo/entregado.
        $platosDisponibles = collect();
        if ($pedido->puedeAgregarProductos()) {
            $platosDisponibles = Plato::where('disponible', true)
                ->with('categoria')
                ->orderBy('categoria_id')
                ->orderBy('nombre')
                ->get()
                ->groupBy('categoria.nombre');
        }

        return view('pedidos.show', compact('pedido', 'estados', 'tipos', 'estadosDetalle', 'platosDisponibles'));
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
            'cliente_email'            => 'nullable|email|max:255',
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

            // ── INVENTARIO: revertir solo lo de los detalles NO entregados ─────
            // (los entregados ya se consumieron; su inventario no se revierte).
            if (in_array($pedido->estado, [Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])) {
                $detallesAntiguos = $pedido->detalles()
                    ->where('estado', '!=', DetallePedido::ESTADO_ENTREGADO)
                    ->with('plato.ingredientes')->get();
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
            $pedido->cliente_email = $request->cliente_email;

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

            // ── DETALLES: PRESERVAR los entregados, reemplazar el resto ────────
            // Los items ya entregados (servidos) no se tocan: no se borran ni se
            // pueden quitar desde la edición. Solo se reemplazan los no entregados.
            $platosEntregados = $pedido->detalles()
                ->where('estado', DetallePedido::ESTADO_ENTREGADO)
                ->pluck('plato_id')->all();

            // Borrar únicamente los detalles NO entregados.
            $pedido->detalles()
                ->where('estado', '!=', DetallePedido::ESTADO_ENTREGADO)
                ->delete();

            $nuevosItems = [];
            foreach ($request->items as $item) {
                // No re-insertar un plato que ya está entregado (ya existe su fila).
                if (in_array($item['plato_id'], $platosEntregados)) {
                    continue;
                }
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

            if (!empty($nuevosItems)) {
                DetallePedido::insert($nuevosItems);
            }

            // ── INVENTARIO: descontar solo lo NUEVO (no los ya entregados) ─────
            if (in_array($pedido->estado, [Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])) {
                foreach ($request->items as $item) {
                    if (in_array($item['plato_id'], $platosEntregados)) {
                        continue; // ya entregado: su inventario no se vuelve a descontar
                    }
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
        // Recalcular el estado del pedido según sus ítems (frescos): si tras
        // editar/borrar productos ya no queda ninguno pendiente, el badge no
        // debe seguir diciendo "Pendiente".
        $pedido->load('detalles');
        $pedido->recalcularEstadoCocina();

        // Si el pedido está en comanda (pendiente/en preparación/listo),
        // avisamos a cocina que fue editado para que el kitchen display se
        // refresque en vivo (p. ej. al quitar un producto equivocado).
        if (in_array($pedido->estado, ['pendiente', 'en_preparacion', 'listo'])) {
            try {
                event(new \App\Events\PedidoActualizado($pedido->fresh()));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo notificar a cocina (update): ' . $e->getMessage());
            }
        }
    }
    // Método para verificar stock antes de actualizar o guardar
    private function verificarStockItems($items)
    {
        // La validación "sin receta = no vendible" (tu regla) ya está en el bucle
        // de abajo (ingredientes->count() === 0). Aquí solo inicializamos el acumulador.
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

            // Capturar datos antes del delete para el broadcast
            $pedidoId = $pedido->id;
            $numeroPedido = $pedido->numero_pedido;

            $pedido->detalles()->delete();
            $pedido->delete();

            DB::commit();

            // Notificar a cocina/meseros para que el card desaparezca en vivo
            broadcast(new PedidoEliminado($pedidoId, $numeroPedido));

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

    // Cliente: solo dos transiciones permitidas sobre su propio pedido.
    //   1) Cancelar mientras esté pendiente.
    //   2) Confirmar recepción (listo → entregado) si es delivery o para_llevar.
    if (Auth::user()->isCliente()) {
        if ($pedido->usuario_id !== Auth::id()) {
            abort(403, 'No puedes modificar pedidos de otros usuarios.');
        }

        $puedeCancelar = $request->estado === Pedido::ESTADO_CANCELADO
            && $pedido->estado === Pedido::ESTADO_PENDIENTE;

        $puedeConfirmarRecepcion = $request->estado === Pedido::ESTADO_ENTREGADO
            && $pedido->estado === Pedido::ESTADO_LISTO
            && in_array($pedido->tipo_pedido, [Pedido::TIPO_DELIVERY, Pedido::TIPO_PARA_LLEVAR]);

        if (!$puedeCancelar && !$puedeConfirmarRecepcion) {
            $msg = 'No tienes permiso para esta transición de estado.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'mensaje' => $msg], 403);
            }
            return back()->with('error', $msg);
        }
    }

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

/**
 * Fase 4 (spec): el mesero/cliente CONFIRMA la entrega y SOLICITA LA CUENTA.
 * Marca TODOS los pedidos abiertos de la mesa como "cuenta solicitada" y pasa
 * la mesa al estado "cuenta_solicitada" para que aparezca habilitada en Caja.
 */
public function solicitarCuenta(Request $request, Pedido $pedido)
{
    if ($pedido->tipo_pedido !== Pedido::TIPO_MESA || !$pedido->mesa_id) {
        return back()->with('error', 'Solo se puede solicitar la cuenta de un pedido de mesa.');
    }

    DB::beginTransaction();
    try {
        // Todos los pedidos vivos de esta mesa entran al cobro.
        $pedidos = Pedido::where('mesa_id', $pedido->mesa_id)
            ->where('tipo_pedido', Pedido::TIPO_MESA)
            ->whereNotIn('estado', [Pedido::ESTADO_CANCELADO, Pedido::ESTADO_FACTURADO])
            ->get();

        foreach ($pedidos as $p) {
            $p->cuenta_solicitada = true;
            $p->cuenta_solicitada_at = now();
            // Al pedir la cuenta el plato ya se entregó.
            if (in_array($p->estado, [Pedido::ESTADO_LISTO])) {
                $p->estado = Pedido::ESTADO_ENTREGADO;
                $p->fecha_hora_entrega = now();
            }
            $p->save();
        }

        // La mesa pasa a "cuenta_solicitada" (visible en Caja).
        if ($pedido->mesa) {
            $pedido->mesa->update(['estado' => Pedido::ESTADO_MESA_CUENTA_SOLICITADA]);
        }

        DB::commit();

        // Avisar a Caja en vivo que hay una cuenta lista para cobrar.
        try {
            broadcast(new \App\Events\CuentaPagada([
                'mesa'   => $pedido->mesa?->numero_mesa,
                'total'  => number_format($pedidos->sum('total'), 2),
                'origen' => 'solicitud',
            ]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo notificar solicitud de cuenta: ' . $e->getMessage());
        }

        $msg = 'Cuenta solicitada para la Mesa ' . ($pedido->mesa?->numero_mesa ?? '') . '. Ya está habilitada en Caja.';
        if ($request->ajax()) {
            return response()->json(['success' => true, 'mensaje' => $msg]);
        }
        return back()->with('success', $msg);
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error al solicitar la cuenta: ' . $e->getMessage());
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

            // Recalcular el estado del pedido según sus ítems (cubre listo y
            // entregado, no solo "todos listos"; y baja a pendiente si se revierte).
            $pedido = $detalle->pedido;
            $pedido->load('detalles');
            $pedido->recalcularEstadoCocina();

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

    $pedido->load(['detalles.plato', 'factura']);

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

        // RECALCULAR pedido + SINCRONIZAR la factura. Sin esto, el "Total a
        // pagar" (que sale de la factura) quedaba con el monto viejo aunque el
        // detalle del pedido cambiara al editar.
        $pedido->calcularTotales();
        $pedido->generarOrUpdateFactura();

        DB::commit();

        return redirect()
            ->route('pedidos.showCliente', $pedido)
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

    /**
     * Obtener pedidos pendientes de pago para el cliente.
     */
    public function misPedidosPendientes()
    {
        $pedidos = Pedido::where('usuario_id', Auth::id())
            ->whereNotIn('estado', [Pedido::ESTADO_FACTURADO, Pedido::ESTADO_CANCELADO])
            ->where(function ($q) {
                // Solo los que NO están pagados: sin factura todavía, o con
                // factura en estado pendiente. Excluye los ya pagados (p. ej.
                // un pedido entregado cuya factura quedó 'pagada').
                $q->whereDoesntHave('factura')
                  ->orWhereHas('factura', function ($f) {
                      $f->where('estado', \App\Models\Factura::ESTADO_PENDIENTE);
                  });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pedidos);
    }

    /**
     * Generar QR para un pedido específico del cliente.
     */
    public function generarQrPedido(Pedido $pedido)
    {
        // Verificar que el pedido pertenezca al usuario
        if ($pedido->usuario_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Asegurar que existe factura
        $factura = $pedido->generarOrUpdateFactura();

        // Reutilizar lógica de FacturaController
        return app(FacturaController::class)->generarQr($factura);
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

    /**
     * Agrega productos a un pedido abierto (D4 del spec).
     *
     * Si el plato ya existe en el detalle (constraint unique pedido_id+plato_id),
     * suma la cantidad al detalle existente. Sino crea uno nuevo. Recalcula
     * totales y factura. Solo permitido en estados pendiente/en_preparacion.
     */
    public function agregarItems(Request $request, Pedido $pedido)
    {
        // Cliente solo puede modificar su propio pedido
        if (Auth::user()->isCliente() && $pedido->usuario_id !== Auth::id()) {
            abort(403, 'No puedes modificar pedidos de otros usuarios.');
        }

        // Solo se puede agregar mientras la cuenta NO esté PAGADA ni cerrada.
        // (Una mesa pagada por QR, o un para-llevar ya pagado, queda cerrada.)
        $pedido->loadMissing('factura');
        if (!$pedido->puedeAgregarProductos()) {
            return redirect()->route('pedidos.show', $pedido)
                ->with('error', 'No se pueden agregar productos: la cuenta ya está pagada o cerrada.');
        }

        // Estado antes de agregar: si ya estaba listo/entregado, el producto
        // nuevo debe prepararse, así que el pedido se reabre a cocina (abajo).
        $estadoPrevio = $pedido->estado;

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.plato_id' => 'required|exists:platos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.notas' => 'nullable|string|max:500',
        ]);

        $stockCheck = $this->verificarStockItems($request->items);
        if (!$stockCheck['success']) {
            return redirect()->route('pedidos.show', $pedido)
                ->with('error', $stockCheck['mensaje']);
        }

        DB::beginTransaction();

        try {
            $agregados = 0;
            $sumados = 0;

            foreach ($request->items as $item) {
                $plato = Plato::find($item['plato_id']);
                $cantidad = (int) $item['cantidad'];
                $notasItem = $item['notas'] ?? null;

                $existente = $pedido->detalles()->where('plato_id', $plato->id)->first();

                if ($existente) {
                    $existente->cantidad += $cantidad;
                    $existente->subtotal = $existente->cantidad * $existente->precio_unitario;
                    if ($notasItem) {
                        $existente->notas = trim(($existente->notas ? $existente->notas . ' | ' : '') . $notasItem);
                    }
                    $existente->estado = DetallePedido::ESTADO_PENDIENTE;
                    $existente->save();
                    $sumados++;
                } else {
                    DetallePedido::create([
                        'pedido_id' => $pedido->id,
                        'plato_id' => $plato->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $plato->precio,
                        'subtotal' => $plato->precio * $cantidad,
                        'notas' => $notasItem,
                        'estado' => DetallePedido::ESTADO_PENDIENTE,
                    ]);
                    $agregados++;
                }
            }

            $pedido->load('detalles');
            $pedido->calcularTotales();
            $pedido->generarOrUpdateFactura();

            Cache::forget('low_stock_count_direct');

            DB::commit();

            // Si la cuenta ya estaba lista/entregada, el producto nuevo debe
            // prepararse: reabrimos el pedido a cocina (misma factura; la cuenta
            // sigue abierta hasta que el cajero cobre).
            $volvioACocina = false;
            if (in_array($estadoPrevio, [Pedido::ESTADO_LISTO, Pedido::ESTADO_ENTREGADO])) {
                $pedido->estado = Pedido::ESTADO_PENDIENTE;
                $pedido->save(); // dispara PedidoEstadoCambiado (hook del modelo)
                $volvioACocina = true;
            }

            // Avisar a cocina SIEMPRE que se agregan productos, para que el
            // kitchen display muestre el item nuevo en vivo: ya sea reapareciendo
            // (si estaba entregado/listo) o refrescando el card (si seguía en
            // pendiente/en_preparacion). Se emite PedidoCreado, que es el evento
            // al que la cocina reacciona recargando las comandas.
            try {
                event(new \App\Events\PedidoCreado($pedido->fresh()));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo notificar a cocina (agregarItems): ' . $e->getMessage());
            }

            $mensaje = [];
            if ($agregados > 0) {
                $mensaje[] = $agregados . ' producto' . ($agregados === 1 ? '' : 's') . ' nuevo' . ($agregados === 1 ? '' : 's');
            }
            if ($sumados > 0) {
                $mensaje[] = $sumados . ' ' . ($sumados === 1 ? 'producto sumado' : 'productos sumados');
            }
            if ($volvioACocina) {
                $mensaje[] = 'enviado de nuevo a cocina';
            }

            return redirect()->route('pedidos.show', $pedido)
                ->with('success', 'Pedido actualizado: ' . implode(', ', $mensaje) . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pedidos.show', $pedido)
                ->with('error', 'Error al agregar productos: ' . $e->getMessage());
        }
    }
}
