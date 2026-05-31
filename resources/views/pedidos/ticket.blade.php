<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $pedido->numero_pedido }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
            background: white;
        }
        
        .ticket {
            max-width: 300px;
            margin: 0 auto;
            border: 1px dashed #ccc;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .items-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }
        
        .items-table th,
        .items-table td {
            text-align: left;
            padding: 4px 0;
        }
        
        .items-table th {
            border-bottom: 1px dashed #000;
        }
        
        .items-table td:last-child {
            text-align: right;
        }
        
        .total-row {
            border-top: 1px dashed #000;
            margin-top: 10px;
            padding-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .mb-1 {
            margin-bottom: 5px;
        }
        
        .mt-2 {
            margin-top: 10px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        
        .btn-print {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1>{{ config('app.name', 'Restaurante') }}</h1>
            <p>NIT: 900.000.000-1</p>
            <p>Tel: 300 000 0000</p>
            <p>{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
        
        <div class="info">
            <div class="info-row">
                <span><strong>Pedido:</strong></span>
                <span>#{{ $pedido->numero_pedido }}</span>
            </div>
            <div class="info-row">
                <span><strong>Tipo:</strong></span>
                <span>{{ $tipos[$pedido->tipo_pedido] ?? $pedido->tipo_pedido }}</span>
            </div>
            @if($pedido->tipo_pedido == 'mesa')
            <div class="info-row">
                <span><strong>Mesa:</strong></span>
                <span>{{ $pedido->mesa->numero_mesa ?? 'N/A' }}</span>
            </div>
            @else
            <div class="info-row">
                <span><strong>Cliente:</strong></span>
                <span>{{ $pedido->cliente_nombre }}</span>
            </div>
            <div class="info-row">
                <span><strong>Teléfono:</strong></span>
                <span>{{ $pedido->cliente_telefono }}</span>
            </div>
            @endif
            @php
                $creador = $pedido->usuario;
                $esAutopedido = $creador && method_exists($creador, 'isCliente') && $creador->isCliente();
            @endphp
            <div class="info-row">
                <span><strong>{{ $esAutopedido ? 'Pedido por:' : 'Atendido por:' }}</strong></span>
                <span>{{ $creador->name ?? 'N/A' }}</span>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Cant</th>
                    <th>Descripción</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>{{ $detalle->plato->nombre }}</td>
                    <td>Bs {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
                @if($detalle->notas)
                <tr>
                    <td colspan="3" style="color: #666; font-size: 10px;">
                        * {{ $detalle->notas }}
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
        
        <div class="total-row">
            <div class="info-row">
                <span>Subtotal:</span>
                <span>Bs {{ number_format($pedido->subtotal, 2) }}</span>
            </div>
            @if($pedido->descuento > 0)
            <div class="info-row">
                <span>Descuento:</span>
                <span>− Bs {{ number_format($pedido->descuento, 2) }}</span>
            </div>
            @endif
            <div class="info-row font-bold mt-2">
                <span>TOTAL:</span>
                <span>Bs {{ number_format($pedido->total, 2) }}</span>
            </div>
        </div>
        
        @if($pedido->notas)
        <div class="footer">
            <strong>Notas:</strong>
            <p>{{ $pedido->notas }}</p>
        </div>
        @endif
        
        <div class="footer">
            <p><strong>Estado: {{ $estados[$pedido->estado] ?? $pedido->estado }}</strong></p>
            <p>¡Gracias por su preferencia!</p>
            <p class="mt-2">*** Este documento no es factura ***</p>
        </div>
    </div>
    
    <button onclick="window.print()" class="btn-print no-print">🖨️ Imprimir Ticket</button>
    
    <script>
        window.onload = function() {
            // Auto-imprimir al cargar la página
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>