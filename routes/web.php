<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlatoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ComandaController;
// redirigen a otros módulos (Facturas, Pedidos, Reportes). El equipo todavía
// no implementó estas pantallas. TODO equipo: reemplazar los stubs.
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\CierrePedidoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\IngredienteController;
use App\Http\Controllers\ReservaMesaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\CashClosureController;


Route::get('/', function () {
    return view('home');
})->name('home');

// Página pública del simulador de pago QR. El QR de cada factura apunta acá
// para que el cliente escanee con su celular, presione "Pagar" y nuestro
// propio backend confirme la factura (sin depender de una app externa).
Route::get('/pago-externo', [\App\Http\Controllers\PagoQrController::class, 'pagoExterno'])
    ->name('pago-externo');

// Ruta para mostrar la página principal con el botón de login
Route::get('/inicio', function () {
    return view('home');
})->name('inicio');

Route::middleware(['auth'])->group(function () {
    // Dashboards
    Route::get('/dashboard/administrador', [DashboardController::class, 'administrador'])->name('dashboard.administrador');
    Route::get('/dashboard/mesero', [DashboardController::class, 'mesero'])->name('dashboard.mesero');
    Route::get('/dashboard/cocinero', [DashboardController::class, 'cocinero'])->name('dashboard.cocinero');
    Route::get('/dashboard/cajero', [DashboardController::class, 'cajero'])->name('dashboard.cajero');
    Route::get('/dashboard/cliente', [DashboardController::class, 'cliente'])->name('dashboard.cliente');

    // Gestión de Platos (Admin y Cocinero)
    Route::resource('platos', PlatoController::class)->middleware('role:admin,cocinero');
    Route::post('/platos/{plato}/toggle-disponible', [PlatoController::class, 'toggleDisponible'])
        ->name('platos.toggle-disponible')
        ->middleware('role:admin,cocinero');

    // Mesas
    Route::resource('mesas', MesaController::class)->middleware('role:admin,mesero');
    // Reserva de mesa: cliente (autoservicio) o personal (admin/mesero a nombre de un cliente)
    Route::resource('reserva', ReservaMesaController::class)->middleware('role:cliente,mesero,admin');


    // Comandas (Cocina)
    Route::prefix('comandas')->name('comandas.')->middleware('role:admin,cocinero')->group(function () {
        Route::get('/', [ComandaController::class, 'index'])->name('index');
        Route::post('/{comanda}/iniciar-preparacion', [ComandaController::class, 'iniciarPreparacion'])->name('iniciar-preparacion');
        Route::post('/{comanda}/marcar-listo', [ComandaController::class, 'marcarListo'])->name('marcar-listo');
        Route::post('/detalle/{detalle}/actualizar', [ComandaController::class, 'actualizarDetalle'])->name('actualizar-detalle');
        Route::get('/{comanda}/print', [ComandaController::class, 'print'])->name('print');
    });

    // Delivery — STUB: DeliveryController redirige a Pedidos.
    Route::resource('delivery', DeliveryController::class)->middleware('role:admin,cajero');

    Route::resource('facturas', FacturaController::class)->middleware('role:admin,cajero');
    Route::post('/facturas/{factura}/pagar', [FacturaController::class, 'pagar'])->name('facturas.pagar')->middleware('role:admin,cajero,cliente');
    Route::post('/facturas/{factura}/anular', [FacturaController::class, 'anular'])->name('facturas.anular')->middleware('role:admin,cajero');
    // Generar QR y enviar factura: el cliente puede invocar sobre su PROPIA factura
    // (validado por ownership en el controller).
    Route::get('/facturas/{factura}/generar-qr', [FacturaController::class, 'generarQr'])->name('facturas.generar-qr')->middleware('role:admin,cajero,cliente');
    Route::post('/facturas/{factura}/enviar-correo', [FacturaController::class, 'enviarPorCorreo'])->name('facturas.enviar-correo')->middleware('role:admin,cajero,cliente');
    Route::get('/facturas/{factura}/pdf', [FacturaController::class, 'descargarPdf'])->name('facturas.pdf')->middleware('role:admin,cajero,cliente');

    // Pagos — STUB: redirige a Facturas. PagoQrController (webhook) sigue separado.
    Route::resource('pagos', PagoController::class)->middleware('role:admin,cajero');

    // Cierre de Cuenta por mesa (punto #6): lista mesas con cuenta abierta,
    // permite ver la comanda consolidada y cobrar para liberar la mesa.
    Route::prefix('cierres')->name('cierres.')->middleware('role:admin,cajero,mesero')->group(function () {
        Route::get('/', [CierrePedidoController::class, 'index'])->name('index');
        Route::get('/mesa/{cierre}', [CierrePedidoController::class, 'show'])->name('show');
        Route::post('/mesa/{cierre}/cerrar', [CierrePedidoController::class, 'cerrar'])->name('cerrar');
    });

    // Gestión de Categorías (Solo Admin) - CON todas las rutas CRUD
    Route::resource('categorias', CategoriaController::class)->middleware('role:admin');
    Route::post('/categorias/{categoria}/toggle-activo', [CategoriaController::class, 'toggleActivo'])
        ->name('categorias.toggle-activo')
        ->middleware('role:admin');

    // Gestión de Ingredientes (Admin y Cocinero) - CON todas las rutas CRUD
    Route::resource('ingredientes', IngredienteController::class)->middleware('role:admin,cocinero');

    //inventario
    Route::resource('inventario', InventarioController::class)->middleware('role:admin,cocinero');

    // Usuarios
    Route::resource('usuarios', UsuarioController::class)->middleware('role:admin');



   //PEDIDOS
    Route::resource('pedidos', PedidoController::class)->middleware('role:admin,mesero,cajero,cliente');
    Route::post('/pedidos/{pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])
        ->name('pedidos.cambiar-estado')
        ->middleware('role:admin,mesero,cocinero,cliente');
    Route::post('/detalle-pedido/{detalle}/cambiar-estado', [PedidoController::class, 'cambiarEstadoDetalle'])
        ->name('pedidos.detalle.cambiar-estado')
        ->middleware('role:admin,cocinero,cliente');
    // D4: agregar productos a un pedido abierto sin re-armar todo el detalle.
    // Cliente solo puede agregar a su propio pedido (validado en controller).
    Route::post('/pedidos/{pedido}/agregar-items', [PedidoController::class, 'agregarItems'])
        ->name('pedidos.agregar-items')
        ->middleware('role:admin,mesero,cajero,cliente');
    Route::get('/pedidos/{pedido}/imprimir', [PedidoController::class, 'imprimir'])
        ->name('pedidos.imprimir');
    // FIX: Route::resource('pedidos',...) ya genera 'pedidos.update'. Esta
    // línea duplicaba el nombre y rompía route:cache en producción
    // ("Another route has already been assigned name [pedidos.update]").
    // Route::put('/pedidos/{pedido}', [PedidoController::class, 'update'])
    //     ->name('pedidos.update');
    Route::get('/misPedidos', [PedidoController::class, 'misPedidos'])
    ->name('pedidos.misPedidos')
    ->middleware('auth');



    // Reportes de Consumos
    Route::prefix('reportes')->name('reportes.')->middleware('role:admin,cocinero')->group(function () {
        Route::get('/consumos', [App\Http\Controllers\ReporteConsumoController::class, 'index'])->name('consumos');
        Route::get('/consumos/export', [App\Http\Controllers\ReporteConsumoController::class, 'export'])->name('consumos.export');
    });


});
//Ruta para la notificacion de pedidos
use App\Models\Pedido;

Route::get('/notificaciones/pedidos', function () {

    $cantidad = Pedido::where('estado', 'pendiente')->count();

    return response()->json([
        'cantidad' => $cantidad
    ]);

})->middleware('auth');

Route::middleware(['auth', 'role:cajero,administrador'])->prefix('cierres')->name('cierres.')->group(function () {
    Route::get('/create', [CashClosureController::class, 'create'])->name('create');
    Route::post('/', [CashClosureController::class, 'store'])->name('store');
    Route::get('/{cierre}', [CashClosureController::class, 'show'])->name('show');
});

//Rutas de las apis utilizadas
Route::prefix('api')->group(base_path('routes/api.php'));


require __DIR__.'/auth.php';
