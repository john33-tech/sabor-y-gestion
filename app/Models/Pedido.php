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
        'subtotal',
        'impuesto',
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
        'impuesto' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_hora_estimada' => 'datetime',
        'fecha_hora_entrega' => 'datetime',
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

    public function calcularTotales()
    {
        $this->subtotal = $this->detalles->sum('subtotal');
        $this->impuesto = 0; // IVA desactivado: no se cobra (total = subtotal - descuento)
        $this->total = $this->subtotal - $this->descuento;
        $this->save();
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
    $factura->impuesto = $this->impuesto;
    $factura->descuento = $this->descuento;
    $factura->total = $this->total;
    //dd($factura->toArray()); // El código se detiene AQUÍ

    $factura->save();
    return $factura;
}

}
