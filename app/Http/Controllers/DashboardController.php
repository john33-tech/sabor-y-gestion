<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function administrador()
    {
        $this->authorizeRole('admin');
        
        // Obtenemos una colección vacía para evitar el error de variable indefinida
        // En el futuro, esto se podrá reemplazar con una consulta real a la base de datos
        $productosDestacados = collect();
        
        return view('dashboard.administrador.index', compact('productosDestacados'));
    }
    
    public function mesero()
    {
        $this->authorizeRole('mesero');
        return view('dashboard.mesero.index');
    }
    
    public function cocinero()
    {
        $this->authorizeRole('cocinero');

        // Solo hoy y solo pendientes para el dashboard del cocinero
        $query = \App\Models\Pedido::with(['detalles.plato', 'mesa', 'usuario'])
            ->whereDate('created_at', now()->today())
            ->where('estado', \App\Models\Pedido::ESTADO_PENDIENTE);
        
        $comandas = $query->orderBy('created_at', 'asc')->paginate(12);
        
        $tipos = \App\Models\Pedido::getTipos();
        
        // Estadísticas solo de HOY
        $stats = [
            'total' => \App\Models\Pedido::whereDate('created_at', now()->today())
                ->whereIn('estado', ['pendiente', 'en_preparacion', 'listo'])->count(),
            'pendientes' => \App\Models\Pedido::whereDate('created_at', now()->today())
                ->where('estado', 'pendiente')->count(),
            'en_preparacion' => \App\Models\Pedido::whereDate('created_at', now()->today())
                ->where('estado', 'en_preparacion')->count(),
            'listos' => \App\Models\Pedido::whereDate('created_at', now()->today())
                ->where('estado', 'listos')->count()
        ];

        return view('dashboard.cocinero.index', compact('comandas', 'tipos', 'stats'));
    }
    
    public function cajero()
    {
        $this->authorizeRole('cajero');
        return view('dashboard.cajero.index');
    }
    
    public function cliente()
    {
        $this->authorizeRole('cliente');
        return view('dashboard.cliente.index');
    }

    private function authorizeRole($role)
    {
        if (Auth::user()->role !== $role && Auth::user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
    }
}