<?php
// app/Models/Pedido.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Factura;
use App\Events\PedidoEstadoCambiado;
use Illuminate\Support\Facades\Auth;

class Pedido extends Model
{
    use HasFactory;

    /**
     * Boot: emite `PedidoEstadoCambiado` cuando el campo `estado` cambia.
     * Esto cubre todos los caminos que actualizan estado (controllers,
     * cierres, cambios masivos), no solo actualizarEstado().
     */
    protected static function booted()
    {
        static::updated(function (Pedido $pedido) {
            if ($pedido->wasChanged('estado') && $pedido->usuario_id) {
                $anterior = (string) $pedido->getOriginal('estado');
                broadcast(new PedidoEstadoCambiado($pedido, $anterior));
            }
        });
    }

    protected $table = 'pedidos';

    protected $fillable = [
        'numero_pedido',
        'mesa_id',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_email',
        'direccion',  // Campo agregado
        'tipo_pedido',
        'estado',
        'cuenta_solicitada',
        'cuenta_solicitada_at',
        'subtotal',
        // 'impuesto' eliminado: la columna no existe en la tabla pedidos (IVA desactivado).
        'descuento',
        'total',
        'notas',
        'fecha_hora_estimada',
        'fecha_hora_entrega',
        'usuario_id',
        'latitud',
        'longitud'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_hora_estimada' => 'datetime',
        'fecha_hora_entrega' => 'datetime',
        'cuenta_solicitada' => 'boolean',
        'cuenta_solicitada_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PREPARACION = 'en_preparacion';
    const ESTADO_LISTO = 'listo';
    const ESTADO_ENTREGADO = 'entregado';
    const ESTADO_CANCELADO = 'cancelado';
    const ESTADO_FACTURADO = 'facturado';

    const TIPO_MESA = 'mesa';
    const TIPO_DELIVERY = 'delivery';
    const TIPO_PARA_LLEVAR = 'para_llevar';

    const ESTADO_MESA_CUENTA_SOLICITADA = 'cuenta_solicitada';

    public static function getEstados()
    {
        return [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_EN_PREPARACION => 'En Preparación',
            self::ESTADO_LISTO => 'Listo',
            self::ESTADO_ENTREGADO => 'Entregado',
            self::ESTADO_CANCELADO => 'Cancelado',
            self::ESTADO_FACTURADO => 'Facturado'
        ];
    }

    public static function getTipos()
    {
        return [
            self::TIPO_MESA => 'Mesa',
            self::TIPO_DELIVERY => 'Delivery',
            self::TIPO_PARA_LLEVAR => 'Para Llevar'
        ];
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function factura()
    {
        return $this->hasOne(Factura::class);
    }

    /**
     * Pedidos que deben verse en la cocina (kitchen display): en estados
     * pendiente/en_preparacion/listo. Regla de "pagar primero":
     *  - Pedidos hechos por el CLIENTE (autoservicio): solo entran a cocina
     *    cuando su factura YA está PAGADA (primero paga, luego se prepara).
     *  - Pedidos del staff (mesero/admin/cajero): entran a cocina enseguida.
     */
    public function scopeVisibleEnCocina($query)
    {
        return $query
            ->whereIn('estado', [self::ESTADO_PENDIENTE, self::ESTADO_EN_PREPARACION, self::ESTADO_LISTO])
            ->where(function ($q) {
                // No fue hecho por un cliente (lo hizo el staff) → va directo.
                $q->whereDoesntHave('usuario', function ($u) {
                        $u->where('role', 'cliente');
                    })
                  // …o ya está pagado (cualquier pedido del cliente ya pagado).
                  ->orWhereHas('factura', function ($f) {
                      $f->where('estado', Factura::ESTADO_PAGADA);
                  });
            });
    }

    /**
     * ¿Se le pueden seguir agregando productos a la cuenta? Solo si el pedido
     * no está facturado/cancelado Y su factura sigue PENDIENTE (sin pagar).
     * Una vez pagada (mesa pagada por QR, o para llevar), la cuenta se cierra:
     * no se puede agregar nada que no se haya pagado.
     */
    public function puedeAgregarProductos(): bool
    {
        if (in_array($this->estado, [self::ESTADO_FACTURADO, self::ESTADO_CANCELADO])) {
            return false;
        }
        $estadoFactura = optional($this->factura)->estado;
        return $estadoFactura === null || $estadoFactura === Factura::ESTADO_PENDIENTE;
    }

    public function calcularTotales(?float $distanciaKm = null)
    {
        $this->subtotal = $this->detalles->sum('subtotal');
        // IVA desactivado. total = subtotal - descuento + costo de envío (delivery).
        $this->total = $this->subtotal - $this->descuento + $this->costoEnvio($distanciaKm);
        $this->save();
    }

    /**
     * Costo de envío (solo delivery). Fórmula: envio_base + envio_por_km * km.
     * Si se pasa $km (distancia por calle del frontend, OSRM) se usa esa para que
     * el cobro coincida con la ruta que se muestra en el mapa. Si no, se estima
     * en línea recta (Haversine) desde lat/lng. NO se guarda en BD (sin migración).
     */
    public function costoEnvio(?float $km = null): float
    {
        if ($this->tipo_pedido !== self::TIPO_DELIVERY) {
            return 0.0;
        }

        if ($km === null) {
            if (!$this->latitud || !$this->longitud) {
                return 0.0;
            }
            $km = $this->distanciaKmHaversine(
                (float) config('restaurante.lat'),
                (float) config('restaurante.lng'),
                (float) $this->latitud,
                (float) $this->longitud
            );
        }

        // Redondear la distancia a 1 decimal (igual que se muestra al cliente)
        // para que "km mostrado × tarifa" cuadre exacto.
        $km = round($km, 1);

        $base  = (float) config('restaurante.envio_base', 0);
        $porKm = (float) config('restaurante.envio_por_km', 0);

        return round($base + $porKm * $km, 2);
    }

    private function distanciaKmHaversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function actualizarEstado($estado)
    {
        $this->estado = $estado;

        if ($estado == self::ESTADO_ENTREGADO) {
            $this->fecha_hora_entrega = now();
        }

        $this->save();

        if ($this->tipo_pedido == self::TIPO_MESA && $estado == self::ESTADO_ENTREGADO) {
            if ($this->mesa) {
                $this->mesa->update(['estado' => 'libre']);
            }
        }
    }

    /**
     * Recalcula el estado del pedido según el estado de sus ítems, SOLO si el
     * pedido sigue en cocina (no toca entregado/facturado/cancelado). Evita que
     * el badge quede en "Pendiente" cuando ya no hay ítems pendientes (p. ej.
     * tras borrar el único ítem pendiente o marcarlos todos entregado).
     * Regla: hay ítem pendiente -> Pendiente; si no, hay en preparación -> En
     * Preparación; si no (todos listo/entregado) -> Listo. Devuelve true si cambió.
     */
    public function recalcularEstadoCocina(): bool
    {
        if (!in_array($this->estado, [self::ESTADO_PENDIENTE, self::ESTADO_EN_PREPARACION, self::ESTADO_LISTO])) {
            return false;
        }

        $activos = $this->detalles->where('estado', '!=', DetallePedido::ESTADO_CANCELADO);
        if ($activos->isEmpty()) {
            return false; // sin ítems activos: no forzar un estado de cocina
        }

        if ($activos->contains('estado', DetallePedido::ESTADO_PENDIENTE)) {
            $nuevo = self::ESTADO_PENDIENTE;
        } elseif ($activos->contains('estado', DetallePedido::ESTADO_EN_PREPARACION)) {
            $nuevo = self::ESTADO_EN_PREPARACION;
        } else {
            // Todos listo/entregado. Si el pedido YA fue entregado antes (existe
            // su venta/consumo registrado), restaurar 'entregado' tras la edición:
            // no tiene sentido pedir re-confirmar la entrega ni re-registrar la
            // venta (p. ej. se agregó un ítem a un pedido ya entregado y se borró).
            // Si nunca se entregó, dejarlo en 'listo' para que el botón Entregado
            // sea quien registre la venta.
            $yaEntregado = Consumo::where('pedido_id', $this->id)->exists();
            $nuevo = $yaEntregado ? self::ESTADO_ENTREGADO : self::ESTADO_LISTO;
        }

        if ($this->estado !== $nuevo) {
            $this->estado = $nuevo;
            $this->save();
            return true;
        }

        return false;
    }

   // app/Models/Pedido.php

public function generarNumeroPedido()
{
    // Obtener el último número de pedido
    $ultimoPedido = self::orderBy('id', 'desc')->whereNotNull('numero_pedido')->first();

    if ($ultimoPedido && $ultimoPedido->numero_pedido) {
        // Extraer el número del último pedido (ej: PED-0001 -> 1)
        $numero = intval(substr($ultimoPedido->numero_pedido, -4)) + 1;
    } else {
        $numero = 1;
    }

    // Generar el nuevo número con formato PED-0001, PED-0002, etc.
        $nuevoNumero = 'PED-' . str_pad($numero, 4, '0', STR_PAD_LEFT);

    // Verificar que no exista ya ese número (por si acaso)
    while (self::where('numero_pedido', $nuevoNumero)->exists()) {
        $numero++;
        $nuevoNumero = 'PED-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    $this->numero_pedido = $nuevoNumero;
    $this->save();
}

    // app/Models/Pedido.php

public function generarOrUpdateFactura()
{
    $factura = Factura::firstOrNew(['pedido_id' => $this->id]);

    if (!$factura->exists) {
        $factura->metodo_pago = 'efectivo';
        $factura->estado = Factura::ESTADO_PENDIENTE;
        $factura->fecha_emision = now();
        $factura->usuario_id = $this->usuario_id ?? Auth::id();

        // Sincronizar el número de factura con el número de pedido (regla del
        // negocio: una factura por pedido y misma numeración).
        // Ej: PED-0007 → FACT-000007.
        $numeroSecuencia = (int) preg_replace('/\D/', '', $this->numero_pedido ?? '');
        if ($numeroSecuencia <= 0) {
            $numeroSecuencia = $this->id; // fallback al ID si aún no hay numero_pedido
        }
        $factura->numero_factura = 'FACT-' . str_pad($numeroSecuencia, 6, '0', STR_PAD_LEFT);
    }

    $factura->cliente_nombre = $this->cliente_nombre ?? 'Cliente';
    $factura->cliente_telefono = $this->cliente_telefono;
    $factura->subtotal = $this->subtotal;
    // IVA desactivado: la columna 'impuesto' no existe en facturas.
    $factura->descuento = $this->descuento;
    $factura->total = $this->total;
    //dd($factura->toArray()); // El código se detiene AQUÍ

    $factura->save();
    return $factura;
}

}
