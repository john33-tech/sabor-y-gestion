<?php

namespace App\Http\Controllers;

use App\Models\CashClosure;
use App\Models\Factura;
use App\Models\Pedido;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashClosureController extends Controller
{
    /**
     * Muestra el formulario de apertura de caja.
     */
    public function create()
    {
        $openClosure = CashClosure::where('status', 'Open')->first();

        if ($openClosure) {
            return redirect()->route('caja.show', $openClosure)
                ->with('error', "Ya hay una caja abierta por {$openClosure->user->name} desde el {$openClosure->opening_date->format('d/m/Y H:i')}. Debes cerrarla antes de abrir una nueva.");
        }

        return view('cierreCaja.create');
    }

    /**
     * Guarda una nueva apertura de caja.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'initial_amount' => 'required|numeric|min:0',
            'observations'   => 'nullable|string|max:500',
        ]);

        $openClosure = CashClosure::where('status', 'Open')->first();
        if ($openClosure) {
            return redirect()->route('caja.show', $openClosure)
                ->with('error', "No se puede abrir una nueva caja porque ya hay una activa.");
        }

        $closure = CashClosure::create([
            'user_id'        => Auth::id(),
            'initial_amount' => $validated['initial_amount'],
            'observations'   => $validated['observations'] ?? null,
            'opening_date'   => now(),
            'status'         => 'Open',
        ]);

        return redirect()->route('caja.show', $closure)
            ->with('success', 'Caja abierta correctamente.');
    }

    public function index(Request $request)
    {
        // Role check
        if (!in_array(Auth::user()->role, ['admin', 'cajero'])) {
            abort(403, 'No tienes permiso para ver el historial de cierres.');
        }

        $query = CashClosure::with('user');

        // Date range filter on opening_date
        if ($request->filled('from_date')) {
            $query->whereDate('opening_date', '>=', Carbon::parse($request->from_date));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('opening_date', '<=', Carbon::parse($request->to_date));
        }

        // Status filter
        if ($request->filled('status') && in_array($request->status, ['Open', 'Closed'])) {
            $query->where('status', $request->status);
        }

        // User name search
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%');
            });
        }

        $closures = $query->orderBy('opening_date', 'desc')
            ->paginate($request->get('per_page', 15))
            ->withQueryString();

        $users = User::whereIn('role', ['admin', 'cajero'])->orderBy('name')->get();

        return view('cierreCaja.index', compact('closures', 'users'));
    }


    /**
     * Display the specified cash closure with details and orders.
     */
    public function show(CashClosure $cierre)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'cajero']) && $user->id !== $cierre->user_id) {
            abort(403, 'No tienes permiso para ver esta caja.');
        }

        $orders = null;
        $chartData = null;

        if ($cierre->status === 'Closed') {
            // Paginate orders (10 per page, separate pagination parameter)
            $orders = $cierre->paidOrders()
                ->with(['mesa', 'usuario'])
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'orders_page');

            // Prepare chart data (sales by hour)
            $salesByHour = Factura::where('estado', 'pagada')
                ->whereBetween('updated_at', [$cierre->opening_date, $cierre->closing_date])
                ->selectRaw('HOUR(updated_at) as hour, SUM(total) as total')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $chartData = [
                'labels' => $salesByHour->pluck('hour')->map(fn($h) => $h . ':00'),
                'values' => $salesByHour->pluck('total'),
            ];
        }

        return view('cierreCaja.show', compact('cierre', 'orders', 'chartData'));
    }

    /**
     * Muestra el formulario de cierre de caja con los cálculos automáticos.
     */
    public function edit(CashClosure $cierre)
    {
        if ($cierre->status !== 'Open') {
            return redirect()->route('caja.show', $cierre)
                ->with('error', 'Esta caja ya está cerrada.');
        }

        // Verificar permisos: solo el usuario que abrió o admin/cajero
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'cajero']) && $user->id !== $cierre->user_id) {
            abort(403, 'No tienes permiso para cerrar esta caja.');
        }

        // Calcular los totales hasta el momento actual
        $totals = $this->calculateTotals($cierre);

        // Verificar si hay pedidos pendientes
        $hasPendingOrders = $this->hasPendingOrders($cierre);

        return view('cierreCaja.edit', compact('cierre', 'totals', 'hasPendingOrders'));
    }

    /**
     * Procesa el cierre de la caja.
     */
    public function update(Request $request, CashClosure $cierre)
    {
        if ($cierre->status !== 'Open') {
            return redirect()->route('caja.show', $cierre)
                ->with('error', 'La caja ya está cerrada.');
        }

        $validated = $request->validate([
            'final_amount' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:500',
        ]);

        // Verificar nuevamente pedidos pendientes (por si cambió durante la edición)
        if ($this->hasPendingOrders($cierre)) {
            return back()->with('error', 'No se puede cerrar la caja porque hay pedidos pendientes (estado diferente a entregado, cancelado o facturado).');
        }

        // Usar transacción para asegurar consistencia
        DB::beginTransaction();
        try {
            $closingDate = now();
            $totals = $this->calculateTotals($cierre, $closingDate);

            $expectedAmount = $cierre->initial_amount + $totals['total_sales'];
            $difference = $validated['final_amount'] - $expectedAmount;

            $cierre->update([
                'final_amount'  => $validated['final_amount'],
                'observations'  => $validated['observations'] ?? $cierre->observations,
                'closing_date'  => $closingDate,
                'status'        => 'Closed',
                'total_sales'   => $totals['total_sales'],
                'total_cash'    => $totals['total_cash'],
                'total_card'    => $totals['total_card'],
                'total_qr'      => $totals['total_qr'],
                'difference'    => $difference,
            ]);

            DB::commit();

            return redirect()->route('caja.show', $cierre)
                ->with('success', 'Caja cerrada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al cerrar la caja: ' . $e->getMessage());
        }
    }

    /**
     * Calcula los totales de ventas para el período de la caja.
     *
     * @param CashClosure $closure
     * @param Carbon|null $closingDate Fecha de cierre (por defecto ahora)
     * @return array
     */
    private function calculateTotals(CashClosure $closure, ?Carbon $closingDate = null)
    {
        $closingDate = $closingDate ?: now();
        $openingDate = $closure->opening_date;

        // Sumar desde facturas pagadas en el período
        $totals = Factura::where('estado', 'pagada')
            ->whereBetween('updated_at', [$openingDate, $closingDate])
            ->selectRaw('
                SUM(total) as total_sales,
                SUM(CASE WHEN metodo_pago = "efectivo" THEN total ELSE 0 END) as total_cash,
                SUM(CASE WHEN metodo_pago = "tarjeta" THEN total ELSE 0 END) as total_card,
                SUM(CASE WHEN metodo_pago = "qr" THEN total ELSE 0 END) as total_qr
            ')
            ->first();

        return [
            'total_sales' => (float) ($totals->total_sales ?? 0),
            'total_cash'  => (float) ($totals->total_cash ?? 0),
            'total_card'  => (float) ($totals->total_card ?? 0),
            'total_qr'    => (float) ($totals->total_qr ?? 0),
        ];
    }

    /**
     * Verifica si existen pedidos pendientes (no entregados, cancelados o facturados)
     * dentro del período de la caja.
     *
     * @param CashClosure $closure
     * @return bool
     */
    private function hasPendingOrders(CashClosure $closure)
    {
        $openingDate = $closure->opening_date;
        $now = now();

        return Pedido::whereBetween('created_at', [$openingDate, $now])
            ->whereNotIn('estado', ['entregado', 'cancelado', 'facturado'])
            ->exists();
    }
}
