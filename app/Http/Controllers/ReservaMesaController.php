<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaMesaController extends Controller
{
    public function index()
    {
        // Mostrar reservas del cliente logueado
        $reservas = Reserva::with('mesa')
            ->where('usuario_id', Auth::id())
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora_reserva', 'desc')
            ->paginate(10);
            
        return view('reservas.index', compact('reservas'));
    }

    public function create()
{
    // SOLO mesas libres
    $mesas = Mesa::where('estado', 'libre')
        ->orderBy('area')
        ->orderBy('numero_mesa')
        ->get();

    return view('reservas.create', compact('mesas'));
}

    public function store(Request $request)
{
    $request->validate([
        'mesa_id' => 'required|exists:mesas,id',
        'fecha_reserva' => 'required|date|after_or_equal:today',
        'hora_reserva' => 'required|string',
        'personas' => 'required|integer|min:1|max:20',
        'notas' => 'nullable|string|max:500'
    ]);

    // Verificar si la mesa sigue libre
    $mesa = Mesa::find($request->mesa_id);

    if ($mesa->estado != 'libre') {
        return back()->with('error', 'La mesa ya no está disponible.');
    }

    // Guardar reserva
    $reserva = new Reserva();
    $reserva->usuario_id = Auth::id();
    $reserva->mesa_id = $request->mesa_id;
    $reserva->fecha_reserva = $request->fecha_reserva;
    $reserva->hora_reserva = $request->hora_reserva;
    $reserva->personas = $request->personas;
    $reserva->notas = $request->notas;
    $reserva->estado = 'pendiente';
    $reserva->save();

    // Cambiar estado de la mesa
        $mesa->estado = 'reservado';
        $mesa->save();

    return redirect()
        ->route('reserva.index')
        ->with('success', 'Reserva solicitada exitosamente.');
}
public function edit($id)
{
    $reserva = Reserva::where('usuario_id', Auth::id())
        ->findOrFail($id);

    $mesas = Mesa::where('estado', 'libre')
        ->orWhere('id', $reserva->mesa_id)
        ->get();

    return view('reservas.edit', compact('reserva', 'mesas'));
}
public function update(Request $request, $id)
{
    $request->validate([
        'mesa_id' => 'required|exists:mesas,id',
        'fecha_reserva' => 'required|date',
        'hora_reserva' => 'required',
        'personas' => 'required|integer|min:1|max:20',
        'notas' => 'nullable|string|max:500'
    ]);

    $reserva = Reserva::where('usuario_id', Auth::id())
        ->findOrFail($id);

    // Liberar mesa anterior
    $mesaAnterior = Mesa::find($reserva->mesa_id);

    if ($mesaAnterior) {
        $mesaAnterior->estado = 'libre';
        $mesaAnterior->save();
    }

    // Reservar nueva mesa
    $mesaNueva = Mesa::find($request->mesa_id);

    if ($mesaNueva->estado != 'libre' && $mesaNueva->id != $reserva->mesa_id) {
        return back()->with('error', 'La mesa ya no está disponible.');
    }

    $mesaNueva->estado = 'reservado';
    $mesaNueva->save();

    // Actualizar reserva
    $reserva->mesa_id = $request->mesa_id;
    $reserva->fecha_reserva = $request->fecha_reserva;
    $reserva->hora_reserva = $request->hora_reserva;
    $reserva->personas = $request->personas;
    $reserva->notas = $request->notas;

    $reserva->save();

    return redirect()
        ->route('reserva.index')
        ->with('success', 'Reserva actualizada correctamente.');
}
public function destroy($id)
{
    $reserva = Reserva::where('usuario_id', Auth::id())
        ->findOrFail($id);

    // Liberar mesa
    $mesa = Mesa::find($reserva->mesa_id);

    if ($mesa) {
        $mesa->estado = 'libre';
        $mesa->save();
    }

    $reserva->delete();

    return redirect()
        ->route('reserva.index')
        ->with('success', 'Reserva eliminada correctamente.');
}
}
