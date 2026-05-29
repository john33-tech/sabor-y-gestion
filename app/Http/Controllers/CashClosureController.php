<?php

namespace App\Http\Controllers;

use App\Models\CashClosure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CashClosureController extends Controller
{
    public function __construct()
    {
        // Solo usuarios con rol cajero o administrador pueden acceder
        $this->middleware(['auth', 'role:cajero,admin']);
    }

    /**
     * Muestra el formulario de apertura de caja.
     */
    public function create()
    {
        // Verificar si ya existe una caja abierta
        $openClosure = CashClosure::where('status', 'open')->first();

        if ($openClosure) {
            return redirect()->route('cierres.show', $openClosure)
                ->with('error', "Ya hay una caja abierta por {$openClosure->user->name} desde el {$openClosure->opening_date->format('d/m/Y H:i')}. Debes cerrarla antes de abrir una nueva.");
        }

        return view('cierres.create');
    }

    /**
     * Guarda una nueva apertura de caja.
     */
    public function store(Request $request)
    {
        // Validación de los datos del formulario
        $validated = $request->validate([
            'initial_amount' => 'required|numeric|min:0',
            'observations'   => 'nullable|string|max:500',
        ]);

        // Verificar nuevamente que no exista una caja abierta (por si hubo concurrencia)
        $openClosure = CashClosure::where('status', 'open')->first();
        if ($openClosure) {
            return redirect()->route('cierres.show', $openClosure)
                ->with('error', "No se puede abrir una nueva caja porque ya hay una activa.");
        }

        // Crear el registro de apertura
        $closure = CashClosure::create([
            'user_id'        => Auth::id(),
            'initial_amount' => $validated['initial_amount'],
            'observations'   => $validated['observations'] ?? null,
            'opening_date'   => now(),
            'status'         => 'open',
            // Los demás campos se mantienen como NULL (se llenarán al cierre)
        ]);

        return redirect()->route('cierres.show', $closure)
            ->with('success', 'Caja abierta correctamente.');
    }

    /**
     * Muestra los detalles de una caja específica.
     */
    public function show(CashClosure $cierre)
    {
        // Verificar que el usuario tenga permisos para ver esta caja
        // (opcional: solo administradores o el propio usuario)
        $user = Auth::user();
        if (!in_array($user->role, ['administrador', 'admin']) && $user->id !== $cierre->user_id) {
            abort(403, 'No tienes permiso para ver esta caja.');
        }

        return view('cierres.show', compact('cierre'));
    }
}
