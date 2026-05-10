<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlatoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\CierreCajaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CategoriaController; 
use App\Http\Controllers\IngredienteController; 
use App\Http\Controllers\ReservaMesaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

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
    
    // Inventario
    Route::resource('inventario', InventarioController::class)->middleware('role:admin,cocinero');
    
    // Mesas
    Route::resource('mesas', MesaController::class)->middleware('role:admin,mesero');
    // Reserva de mesa, disponible solo para cliente
    Route::resource('reserva', ReservaMesaController::class)->middleware('role:cliente');
    Route::get('/reservas/{id}/edit', [ReservaMesaController::class, 'edit'])
    ->name('reserva.edit');
    Route::put('/reservas/{id}', [ReservaMesaController::class, 'update'])
    ->name('reserva.update');
    Route::delete('/reservas/{id}', [ReservaMesaController::class, 'destroy'])
    ->name('reserva.destroy');

    // Comandas (Cocina)
    Route::prefix('comandas')->name('comandas.')->middleware('role:admin,cocinero')->group(function () {
        Route::get('/', [ComandaController::class, 'index'])->name('index');
        Route::post('/{comanda}/iniciar-preparacion', [ComandaController::class, 'iniciarPreparacion'])->name('iniciar-preparacion');
        Route::post('/{comanda}/marcar-listo', [ComandaController::class, 'marcarListo'])->name('marcar-listo');
        Route::post('/detalle/{detalle}/actualizar', [ComandaController::class, 'actualizarDetalle'])->name('actualizar-detalle');
        Route::get('/{comanda}/print', [ComandaController::class, 'print'])->name('print');
    });
    
    // Delivery
    Route::resource('delivery', DeliveryController::class)->middleware('role:admin,cajero');
    
    // Facturas
    Route::resource('facturas', FacturaController::class)->middleware('role:admin,cajero');
    Route::post('/facturas/{factura}/pagar', [FacturaController::class, 'pagar'])->name('facturas.pagar')->middleware('role:admin,cajero');
    Route::post('/facturas/{factura}/anular', [FacturaController::class, 'anular'])->name('facturas.anular')->middleware('role:admin,cajero');
    
    // Pagos
    Route::resource('pagos', PagoController::class)->middleware('role:admin,cajero');
    
    // Cierre de Caja
    Route::resource('cierres', CierreCajaController::class)->middleware('role:admin,cajero');
    
    // Gestión de Categorías (Solo Admin) - CON todas las rutas CRUD
    Route::resource('categorias', CategoriaController::class)->middleware('role:admin');
    Route::post('/categorias/{categoria}/toggle-activo', [CategoriaController::class, 'toggleActivo'])
        ->name('categorias.toggle-activo')
        ->middleware('role:admin');
    
    // Gestión de Ingredientes (Admin y Cocinero) - CON todas las rutas CRUD
    Route::resource('ingredientes', IngredienteController::class)->middleware('role:admin,cocinero');

    // Usuarios
    Route::resource('usuarios', UsuarioController::class)->middleware('role:admin');



   //PEDIDOS
    Route::resource('pedidos', PedidoController::class)->middleware('role:admin,mesero,cajero');
    Route::post('/pedidos/{pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])
        ->name('pedidos.cambiar-estado')
        ->middleware('role:admin,mesero,cocinero');
    Route::post('/detalle-pedido/{detalle}/cambiar-estado', [PedidoController::class, 'cambiarEstadoDetalle'])
        ->name('pedidos.detalle.cambiar-estado')
        ->middleware('role:admin,cocinero');
    Route::get('/pedidos/{pedido}/imprimir', [PedidoController::class, 'imprimir'])
        ->name('pedidos.imprimir');
        Route::put('/pedidos/{pedido}', [PedidoController::class, 'update'])
    ->name('pedidos.update');
    //Mis pedidos, disponible solo para cliente
    Route::get('/misPedidos', [PedidoController::class, 'misPedidos'])
    ->name('misPedidos.index')
    ->middleware('role:cliente');
});

require __DIR__.'/auth.php';