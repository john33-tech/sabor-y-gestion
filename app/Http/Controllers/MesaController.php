<?php
// app/Http/Controllers/MesaController.php
namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MesaController extends Controller
{
    public function index()
    {
        $mesas = Mesa::orderBy('area')->orderBy('numero_mesa')->get();
        $areas = Mesa::select('area')->distinct()->whereNotNull('area')->get();
        return view('mesas.index', compact('mesas', 'areas'));
    }

    public function create()
    {
        return view('mesas.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_mesa' => 'required|string|max:10|unique:mesas',
            'capacidad' => 'required|integer|min:1|max:20',
            'area' => 'nullable|string|max:100',
            'estado' => 'required|in:libre,ocupado,reservado,fuera_servicio',
            'hora_reserva' => 'required_if:estado,reservado|nullable|date',
            'cliente_reserva' => 'required_if:estado,reservado|nullable|string|max:100',
            'telefono_reserva' => 'required_if:estado,reservado|nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Mesa::create($request->all());

        return redirect()->route('mesas.index')->with('success', 'Mesa creada exitosamente');
    }

    public function show(Mesa $mesa)
    {
        return view('mesas.show', compact('mesa'));
    }

    public function edit(Mesa $mesa)
    {
        return view('mesas.edit', compact('mesa'));
    }

    public function update(Request $request, Mesa $mesa)
    {
        $validator = Validator::make($request->all(), [
            'numero_mesa' => 'required|string|max:10|unique:mesas,numero_mesa,' . $mesa->id,
            'capacidad' => 'required|integer|min:1|max:20',
            'area' => 'nullable|string|max:100',
            'estado' => 'required|in:libre,ocupado,reservado,fuera_servicio',
            'hora_reserva' => 'required_if:estado,reservado|nullable|date',
            'cliente_reserva' => 'required_if:estado,reservado|nullable|string|max:100',
            'telefono_reserva' => 'required_if:estado,reservado|nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $mesa->update($request->all());

        return redirect()->route('mesas.index')->with('success', 'Mesa actualizada exitosamente');
    }

    public function destroy(Mesa $mesa)
    {
        $mesa->delete();
        return redirect()->route('mesas.index')->with('success', 'Mesa eliminada exitosamente');
    }

    public function cambiarEstado(Request $request, Mesa $mesa)
    {
        $request->validate([
            'estado' => 'required|in:libre,ocupado,reservado,fuera_servicio'
        ]);

        $mesa->estado = $request->estado;
        
        if ($request->estado !== 'reservado') {
            $mesa->hora_reserva = null;
            $mesa->cliente_reserva = null;
            $mesa->telefono_reserva = null;
        }

        $mesa->save();

        return redirect()->route('mesas.index')->with('success', 'Estado actualizado');
    }




    public function reservaMesa(Request $request, Mesa $mesa)
    {
        
        return redirect()->route('mesas.reserva')->with('success', 'Mesa reservada exitosamente');
    }
}