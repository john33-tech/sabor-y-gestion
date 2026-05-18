<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaMesaController extends Controller
{
    /**
     * Indica si el usuario actual puede gestionar reservas a nombre de otros
     * clientes (admin o mesero). Los clientes solo gestionan las propias.
     */
    private function esPersonal(): bool
    {
        $user = Auth::user();
        return $user && ($user->isAdmin() || $user->isMesero());
    }

    public function index()
    {
        $query = Reserva::with(['mesa', 'usuario'])
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora_reserva', 'desc');

        if (!$this->esPersonal()) {
            $query->where('usuario_id', Auth::id());
        }

        $reservas = $query->paginate(10);
        $esPersonal = $this->esPersonal();

        return view('reservas.index', compact('reservas', 'esPersonal'));
    }

    public function create()
    {
        $mesas = Mesa::where('estado', 'libre')
            ->orderBy('area')
            ->orderBy('numero_mesa')
            ->get();

        $esPersonal = $this->esPersonal();
        $clientes = $esPersonal
            ? User::where('role', 'cliente')->orderBy('name')->get()
            : collect();

        return view('reservas.create', compact('mesas', 'clientes', 'esPersonal'));
    }

    public function store(Request $request)
    {
        $esPersonal = $this->esPersonal();

        $rules = [
            'mesa_id' => 'required|exists:mesas,id',
            'fecha_reserva' => 'required|date|after_or_equal:today',
            'hora_reserva' => 'required|string',
            'personas' => 'required|integer|min:1|max:20',
            'notas' => 'nullable|string|max:500',
        ];

        if ($esPersonal) {
            $rules['usuario_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        $mesa = Mesa::find($request->mesa_id);

        if ($mesa->estado != 'libre') {
            return back()->with('error', 'La mesa ya no está disponible.');
        }

        // Si es personal, validar que el usuario elegido sea efectivamente cliente
        if ($esPersonal) {
            $cliente = User::find($request->usuario_id);
            if (!$cliente || $cliente->role !== 'cliente') {
                return back()->with('error', 'El usuario seleccionado no es un cliente válido.');
            }
            $usuarioId = $cliente->id;
        } else {
            $usuarioId = Auth::id();
        }

        $reserva = new Reserva();
        $reserva->usuario_id = $usuarioId;
        $reserva->mesa_id = $request->mesa_id;
        $reserva->fecha_reserva = $request->fecha_reserva;
        $reserva->hora_reserva = $request->hora_reserva;
        $reserva->personas = $request->personas;
        $reserva->notas = $request->notas;
        $reserva->estado = 'pendiente';
        $reserva->save();

        $mesa->estado = 'reservado';
        $mesa->save();

        return redirect()
            ->route('reserva.index')
            ->with('success', 'Reserva registrada exitosamente.');
    }

    public function edit($id)
    {
        $reserva = $this->encontrarReserva($id);

        $mesas = Mesa::where('estado', 'libre')
            ->orWhere('id', $reserva->mesa_id)
            ->orderBy('area')
            ->orderBy('numero_mesa')
            ->get();

        $esPersonal = $this->esPersonal();
        $clientes = $esPersonal
            ? User::where('role', 'cliente')->orderBy('name')->get()
            : collect();

        return view('reservas.edit', compact('reserva', 'mesas', 'clientes', 'esPersonal'));
    }

    public function update(Request $request, $id)
    {
        $esPersonal = $this->esPersonal();

        $rules = [
            'mesa_id' => 'required|exists:mesas,id',
            'fecha_reserva' => 'required|date',
            'hora_reserva' => 'required',
            'personas' => 'required|integer|min:1|max:20',
            'notas' => 'nullable|string|max:500',
        ];

        if ($esPersonal) {
            $rules['usuario_id'] = 'required|exists:users,id';
            $rules['estado'] = 'nullable|in:pendiente,confirmada,cancelada,completada';
        }

        $request->validate($rules);

        $reserva = $this->encontrarReserva($id);

        // Liberar mesa anterior
        $mesaAnterior = Mesa::find($reserva->mesa_id);
        if ($mesaAnterior) {
            $mesaAnterior->estado = 'libre';
            $mesaAnterior->save();
        }

        $mesaNueva = Mesa::find($request->mesa_id);
        if ($mesaNueva->estado != 'libre' && $mesaNueva->id != $reserva->mesa_id) {
            return back()->with('error', 'La mesa ya no está disponible.');
        }
        $mesaNueva->estado = 'reservado';
        $mesaNueva->save();

        if ($esPersonal) {
            $cliente = User::find($request->usuario_id);
            if (!$cliente || $cliente->role !== 'cliente') {
                return back()->with('error', 'El usuario seleccionado no es un cliente válido.');
            }
            $reserva->usuario_id = $cliente->id;

            if ($request->filled('estado')) {
                $reserva->estado = $request->estado;
            }
        }

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
        $reserva = $this->encontrarReserva($id);

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

    /**
     * Resuelve la reserva respetando el alcance del usuario:
     * - Personal (admin/mesero): cualquier reserva.
     * - Cliente: únicamente las suyas.
     */
    private function encontrarReserva($id): Reserva
    {
        $query = Reserva::query();
        if (!$this->esPersonal()) {
            $query->where('usuario_id', Auth::id());
        }
        return $query->findOrFail($id);
    }
}
