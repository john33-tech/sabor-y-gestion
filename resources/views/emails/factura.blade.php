<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tu factura — {{ $factura->numero_factura }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family: Arial, Helvetica, sans-serif; color:#1f2937;">
    @php
        $tipoLabel = [
            'mesa' => 'En el restaurante',
            'delivery' => 'Delivery',
            'para_llevar' => 'Para llevar',
        ][optional($factura->pedido)->tipo_pedido] ?? ucfirst(optional($factura->pedido)->tipo_pedido ?? '—');
        $metodoLabel = [
            'efectivo' => 'Efectivo',
            'tarjeta' => 'Tarjeta',
            'qr' => 'Pago QR',
            'transferencia' => 'Transferencia',
        ][$factura->metodo_pago] ?? ucfirst($factura->metodo_pago ?? '—');
        $pagada = $factura->estado === 'pagada';
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 0;">
        <tr><td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden;">

                {{-- HEADER --}}
                <tr>
                    <td style="background:#C2410C; padding:22px 32px;">
                        <table width="100%" cellpadding="0" cellspacing="0"><tr>
                            <td style="color:#ffffff; font-size:22px; font-weight:bold;">🍴 Sabor &amp; Gestión</td>
                            <td align="right" style="color:#ffe8d6; font-size:12px;">Factura electrónica</td>
                        </tr></table>
                    </td>
                </tr>

                {{-- BANNER ESTADO --}}
                @if($pagada)
                <tr>
                    <td style="background:#ecfdf5; padding:14px 32px; border-bottom:1px solid #d1fae5; color:#065f46; font-size:14px;">
                        ✅ <strong>Pago confirmado.</strong> ¡Gracias por tu compra, {{ $factura->cliente_nombre ?? 'cliente' }}!
                    </td>
                </tr>
                @endif

                {{-- CUERPO --}}
                <tr><td style="padding:28px 32px;">
                    <p style="margin:0 0 18px; font-size:15px; line-height:1.5;">
                        Tu factura <strong>{{ $factura->numero_factura }}</strong> está lista. La adjuntamos en <strong>PDF</strong> a este correo.
                    </p>

                    {{-- RESUMEN --}}
                    <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; margin:0 0 20px;">
                        <tr><td style="padding:14px 18px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
                                <tr><td style="color:#78716c; padding:3px 0;">N° de factura</td><td align="right" style="font-weight:bold;">{{ $factura->numero_factura }}</td></tr>
                                <tr><td style="color:#78716c; padding:3px 0;">Fecha</td><td align="right">{{ ($factura->fecha_emision ?? $factura->created_at)->format('d/m/Y H:i') }}</td></tr>
                                @if($factura->pedido)
                                <tr><td style="color:#78716c; padding:3px 0;">Pedido</td><td align="right">{{ $factura->pedido->numero_pedido }} · {{ $tipoLabel }}</td></tr>
                                @endif
                                <tr><td style="color:#78716c; padding:3px 0;">Método de pago</td><td align="right">{{ $metodoLabel }}</td></tr>
                            </table>
                        </td></tr>
                    </table>

                    {{-- ITEMS --}}
                    @if($factura->pedido && $factura->pedido->detalles->count())
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin:0 0 16px; font-size:13px;">
                        <tr style="background:#C2410C; color:#ffffff;">
                            <td style="padding:9px 10px; text-align:left;">Producto</td>
                            <td style="padding:9px 10px; text-align:center; width:50px;">Cant.</td>
                            <td style="padding:9px 10px; text-align:right; width:95px;">Subtotal</td>
                        </tr>
                        @foreach($factura->pedido->detalles as $d)
                        <tr style="border-bottom:1px solid #eeeeee; background:{{ $loop->even ? '#fafaf9' : '#ffffff' }};">
                            <td style="padding:8px 10px;">{{ $d->plato->nombre ?? 'Item' }}</td>
                            <td style="padding:8px 10px; text-align:center;">{{ $d->cantidad }}</td>
                            <td style="padding:8px 10px; text-align:right;">Bs {{ number_format($d->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </table>
                    @endif

                    {{-- TOTALES --}}
                    <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; margin:0 0 10px;">
                        <tr><td align="right" style="color:#78716c; padding:2px 10px;">Subtotal</td><td align="right" style="width:120px; padding:2px 0;">Bs {{ number_format($factura->subtotal, 2) }}</td></tr>
                        @if($factura->descuento > 0)
                        <tr><td align="right" style="color:#b91c1c; padding:2px 10px;">Descuento</td><td align="right" style="color:#b91c1c; padding:2px 0;">− Bs {{ number_format($factura->descuento, 2) }}</td></tr>
                        @endif
                    </table>
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr><td style="background:#C2410C; border-radius:8px; padding:13px 16px;">
                            <table width="100%" cellpadding="0" cellspacing="0"><tr>
                                <td style="color:#ffffff; font-size:15px; font-weight:bold;">TOTAL PAGADO</td>
                                <td align="right" style="color:#ffffff; font-size:20px; font-weight:bold;">Bs {{ number_format($factura->total, 2) }}</td>
                            </tr></table>
                        </td></tr>
                    </table>

                    <p style="margin:20px 0 0; font-size:13px; color:#57534e;">📎 Adjuntamos tu factura completa en <strong>PDF</strong> para que la descargues o imprimas.</p>
                </td></tr>

                {{-- FOOTER --}}
                <tr><td style="background:#fafaf9; padding:20px 32px; border-top:1px solid #eeeeee; text-align:center; font-size:12px; color:#78716c; line-height:1.6;">
                    <strong style="color:#C2410C;">Sabor &amp; Gestión</strong><br>
                    {{ config('restaurante.direccion', 'Cochabamba, Bolivia') }}<br>
                    <span style="color:#a8a29e;">Este es un correo automático, por favor no respondas a esta dirección.</span>
                </td></tr>

            </table>
        </td></tr>
    </table>
</body>
</html>
