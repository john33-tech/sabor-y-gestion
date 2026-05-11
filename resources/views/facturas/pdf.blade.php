<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; }
        .header { border-bottom: 2px solid #4F46E5; padding-bottom: 10px; margin-bottom: 20px; }
        .title { color: #4F46E5; font-size: 24px; font-weight: bold; }
        .details-table { width: 100%; margin-bottom: 20px; }
        .details-table td { width: 50%; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f9fafb; font-weight: bold; color: #555; }
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 5px 8px; text-align: right; }
        .totals-table .label { font-weight: bold; color: #555; width: 80%; }
        .totals-table .value { width: 20%; }
        .totals-table .total-row { font-size: 18px; font-weight: bold; color: #111; border-top: 2px solid #ddd; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td><h1 class="title">SaborGestion</h1></td>
                <td style="text-align: right;">
                    <strong>Factura N°:</strong> {{ $factura->numero_factura }}<br>
                    <strong>Fecha:</strong> {{ $factura->fecha_emision ? $factura->fecha_emision->format('d/m/Y H:i') : $factura->created_at->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="details-table">
        <tr>
            <td>
                <h3>Datos del Cliente</h3>
                <strong>Nombre:</strong> {{ $factura->cliente_nombre }}<br>
                <strong>NIT/CI:</strong> {{ $factura->cliente_nit ?? 'S/N' }}<br>
                <strong>Teléfono:</strong> {{ $factura->cliente_telefono ?? 'N/A' }}
            </td>
            <td>
                <h3>Detalles del Pedido</h3>
                <strong>Pedido N°:</strong> {{ $factura->pedido ? $factura->pedido->numero_pedido : 'N/A' }}<br>
                <strong>Método de Pago:</strong> <span style="text-transform: capitalize;">{{ $factura->metodo_pago }}</span><br>
                <strong>Estado:</strong> Pagada
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th style="text-align: center;">Cant.</th>
                <th style="text-align: right;">P. Unitario</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @if($factura->pedido && $factura->pedido->detalles)
                @foreach($factura->pedido->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->plato ? $detalle->plato->nombre : 'Item' }}</td>
                    <td style="text-align: center;">{{ $detalle->cantidad }}</td>
                    <td style="text-align: right;">Bs. {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td style="text-align: right;">Bs. {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center;">Consumo general</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="value">Bs. {{ number_format($factura->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Impuesto (13%):</td>
            <td class="value">Bs. {{ number_format($factura->impuesto, 2) }}</td>
        </tr>
        @if($factura->descuento > 0)
        <tr>
            <td class="label" style="color: red;">Descuento:</td>
            <td class="value" style="color: red;">-Bs. {{ number_format($factura->descuento, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td class="label">Total Final:</td>
            <td class="value">Bs. {{ number_format($factura->total, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        Gracias por su preferencia.<br>
        Esta es una representación impresa de un documento fiscal digital.
    </div>
</body>
</html>
