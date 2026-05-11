<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Stub temporal — el módulo de Cierre de Caja del equipo todavía no está
 * implementado. Redirigimos a Reportes para que el usuario tenga datos
 * de ventas mientras tanto.
 */
class CierreCajaController extends Controller
{
    public function index()
    {
        // Si existiera 'reportes.index' lo usamos; sino caemos al dashboard.
        if (\Route::has('reportes.index')) {
            return redirect()->route('reportes.index')
                ->with('info', 'Cierre de Caja aún no implementado. Mostramos Reportes mientras tanto.');
        }

        return redirect()->route('dashboard.administrador')
            ->with('info', 'El módulo de Cierre de Caja aún no está implementado.');
    }

    public function create() { return $this->index(); }
    public function store(Request $request) { return $this->index(); }
    public function show($id) { return $this->index(); }
    public function edit($id) { return $this->index(); }
    public function update(Request $request, $id) { return $this->index(); }
    public function destroy($id) { return $this->index(); }
}
