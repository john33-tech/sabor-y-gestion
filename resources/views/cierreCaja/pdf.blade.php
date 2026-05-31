<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Cierre de Caja #{{ $cierre->id }}</title>
    <style>
        /* Estilos profesionales y adaptados para impresión */
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            margin: 20px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #C2410C;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #C2410C;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        .title {
            font-size: 20px;
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h3 {
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-left: 5px solid #C2410C;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .amount-table td, .amount-table th {
            text-align: right;
        }
        .amount-table td:first-child, .amount-table th:first-child {
            text-align: left;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .signature {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
        }
        .badge-closed {
            background-color: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'Mi Restaurante') }}</h1>
        <p>RUC: {{ config('app.ruc', '12345678901') }} | Tel: {{ config('app.phone', '(01) 123-4567') }}</p>
        <p>Dirección: {{ config('app.address', 'Av. Principal 123, Lima - Perú') }}</p>
    </div>

    <div class="title">
        COMPROBANTE DE CIERRE DE CAJA
        <span class="badge-closed">CERRADO</span>
    </div>

    <div class="section">
        <h3>Información General</h3>
        <table style="width: auto; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 150px;"><strong>N° Cierre:</strong></td>
                <td style="border: none;">{{ $cierre->id }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Usuario que abrió:</strong></td>
                <td style="border: none;">{{ $cierre->user->name }} ({{ ucfirst($cierre->user->role) }})</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Fecha de apertura:</strong></td>
                <td style="border: none;">{{ $cierre->opening_date->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Fecha de cierre:</strong></td>
                <td style="border: none;">{{ $cierre->closing_date->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Resumen de Montos</h3>
        <table class="amount-table">
            <thead>
                <tr><th>Concepto</th><th>Monto (S/)</th></tr>
            </thead>
            <tbody>
                <tr><td>Monto inicial (apertura)</td><td>{{ number_format($cierre->initial_amount, 2) }}</td></tr>
                <tr><td>Total ventas del turno</td><td>{{ number_format($totals['total_sales'], 2) }}</td></tr>
                <tr><td style="padding-left:20px;">- Ventas en efectivo</td><td>{{ number_format($totals['total_cash'], 2) }}</td></tr>
                <tr><td style="padding-left:20px;">- Ventas con tarjeta</td><td>{{ number_format($totals['total_card'], 2) }}</td></tr>
                <tr><td style="padding-left:20px;">- Ventas con QR</td><td>{{ number_format($totals['total_qr'], 2) }}</td></tr>
                <tr><td><strong>Monto esperado en caja</strong></td><td><strong>{{ number_format($cierre->initial_amount + $totals['total_sales'], 2) }}</strong></td></tr>
                <tr><td>Monto final real registrado</td><td>{{ number_format($cierre->final_amount, 2) }}</td></tr>
                <tr><td><strong>Diferencia</strong></td><td><strong>{{ number_format($cierre->difference, 2) }}</strong></td></tr>
            </tbody>
        </table>
        @if($cierre->difference != 0)
            <p style="color: #d9534f; font-size: 12px;">* La diferencia indica un faltante (negativo) o sobrante (positivo) de dinero.</p>
        @endif
    </div>

    @if($cierre->observations)
    <div class="section">
        <h3>Observaciones</h3>
        <p>{{ $cierre->observations }}</p>
    </div>
    @endif

    <div class="section">
        <h3>Pedidos incluidos en este cierre</h3>
        @if($orders->count())
        <table>
            <thead>
                <tr><th>ID Pedido</th><th>Mesa / Cliente</th><th>Total (S/)</th><th>Método pago</th><th>Fecha</th></tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>
                        @if($order->tipo_pedido == 'mesa' && $order->mesa)
                            Mesa {{ $order->mesa->numero_mesa }}
                        @else
                            {{ $order->cliente_nombre ?? 'Cliente' }}
                        @endif
                    </td>
                    <td>{{ number_format($order->total, 2) }}</td>
                    <td>{{ ucfirst($order->factura->metodo_pago ?? 'N/A') }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p>No hay pedidos registrados en este período.</p>
        @endif
    </div>

    <div class="footer">
        Documento generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}<br>
        Este comprobante es válido como registro interno del restaurante.
    </div>

    <div class="signature">
        _________________________<br>
        Firma del responsable
    </div>
</body>
</html>
