<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        @page { margin: 24px 28px; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
        }

        /* ============== HEADER ============== */
        .header {
            border-bottom: 3px solid #C2410C;
            padding-bottom: 12px;
            margin-bottom: 14px;
            position: relative;
        }
        .header-table { width: 100%; }
        .brand {
            font-size: 24px;
            font-weight: bold;
            color: #C2410C;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .brand-sub {
            color: #78716c;
            font-size: 10px;
            font-style: italic;
            margin: 0;
        }
        .emisor {
            font-size: 9.5px;
            color: #57534e;
            line-height: 1.5;
        }
        .emisor strong { color: #1f2937; }
        .doc-box {
            display: inline-block;
            background: #C2410C;
            color: white;
            padding: 8px 14px;
            border-radius: 4px;
            text-align: center;
            min-width: 160px;
        }
        .doc-box .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.85;
            display: block;
        }
        .doc-box .numero {
            font-size: 14px;
            font-weight: bold;
            display: block;
            margin-top: 2px;
        }
        .doc-fecha {
            font-size: 9px;
            color: #57534e;
            margin-top: 6px;
            text-align: right;
        }

        /* ============== ESTADO ============== */
        .badge-estado {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .badge-pagada   { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-pendiente{ background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .badge-anulada  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* ============== BLOQUES INFO ============== */
        .info-grid { width: 100%; margin-bottom: 14px; }
        .info-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0 4px;
        }
        .info-card {
            background: #fffaf6;
            border-left: 3px solid #C2410C;
            padding: 8px 12px;
            border-radius: 0 4px 4px 0;
        }
        .info-card h4 {
            margin: 0 0 6px 0;
            font-size: 10px;
            color: #C2410C;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-card .row { margin-bottom: 2px; }
        .info-card .row strong { color: #1f2937; display: inline-block; min-width: 78px; }

        /* ============== ITEMS ============== */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .items-table thead th {
            background: #C2410C;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #e7e5e4;
        }
        .items-table tbody tr:nth-child(even) td { background: #fafaf9; }
        .items-table .col-num     { width: 30px;  text-align: center; color: #78716c; }
        .items-table .col-desc    { width: auto; }
        .items-table .col-cant    { width: 50px;  text-align: center; }
        .items-table .col-pu      { width: 80px;  text-align: right; }
        .items-table .col-sub     { width: 90px;  text-align: right; font-weight: bold; }
        .item-nota {
            font-size: 9px;
            color: #78716c;
            font-style: italic;
            margin-top: 2px;
        }

        /* ============== TOTALES ============== */
        .totals-wrap { width: 100%; margin-bottom: 14px; }
        .totals-wrap td.left { width: 60%; padding: 4px 8px; vertical-align: top; }
        .totals-wrap td.right { width: 40%; vertical-align: top; }
        .nota-pedido {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 4px;
            padding: 8px 10px;
            font-size: 10px;
        }
        .nota-pedido strong { color: #92400e; }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 10px;
            font-size: 11px;
        }
        .totals-table td.l { text-align: right; color: #57534e; }
        .totals-table td.r { text-align: right; width: 90px; font-weight: bold; }
        .totals-table tr.descuento td { color: #b91c1c; }
        .totals-table tr.total td {
            background: #C2410C;
            color: white;
            font-size: 14px;
            font-weight: bold;
            padding: 8px 10px;
        }

        /* ============== PAGO ============== */
        .pago-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 10px;
            color: #166534;
        }
        .pago-box.pendiente {
            background: #fffbeb;
            border-color: #fcd34d;
            color: #92400e;
        }

        /* ============== FOOTER ============== */
        .footer {
            border-top: 1px solid #e7e5e4;
            padding-top: 10px;
            margin-top: 14px;
            font-size: 9px;
            color: #78716c;
            text-align: center;
        }
        .footer .marca { color: #C2410C; font-weight: bold; }

        /* Marca de agua diagonal "PAGADA" */
        .watermark {
            position: fixed;
            top: 45%;
            left: 25%;
            font-size: 90px;
            color: rgba(16, 185, 129, 0.10);
            transform: rotate(-30deg);
            font-weight: bold;
            letter-spacing: 8px;
            z-index: 0;
        }
    </style>
</head>
<body>

    @if($factura->estado === 'pagada')
        <div class="watermark">PAGADA</div>
    @endif

    {{-- ============ HEADER ============ --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    <p class="brand">Sabor &amp; Gestión</p>
                    <p class="brand-sub">Sistema integral de gestión gastronómica</p>
                    <div class="emisor" style="margin-top: 8px;">
                        <strong>NIT:</strong> 1234567890 &nbsp;·&nbsp;
                        <strong>Tel:</strong> +591 4 4444444<br>
                        <strong>Dirección:</strong> Av. Heroínas #321, Cochabamba, Bolivia<br>
                        <strong>Email:</strong> contacto@saborgestion.com
                    </div>
                </td>
                <td style="width: 40%; text-align: right;">
                    <div class="doc-box">
                        <span class="label">Factura</span>
                        <span class="numero">{{ $factura->numero_factura }}</span>
                    </div>
                    <div class="doc-fecha">
                        Emitida: {{ ($factura->fecha_emision ?? $factura->created_at)->format('d/m/Y H:i') }}
                    </div>
                    <div style="margin-top: 6px;">
                        @php
                            $estado = $factura->estado ?? 'pendiente';
                            $estadoClase = 'badge-' . $estado;
                        @endphp
                        <span class="badge-estado {{ $estadoClase }}">{{ strtoupper($estado) }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ============ DATOS CLIENTE + PEDIDO ============ --}}
    <table class="info-grid">
        <tr>
            <td>
                <div class="info-card">
                    <h4>DATOS DEL CLIENTE</h4>
                    <div class="row"><strong>Nombre:</strong> {{ $factura->cliente_nombre ?? 'Consumidor final' }}</div>
                    <div class="row"><strong>NIT/CI:</strong> {{ $factura->cliente_nit ?? 'S/N' }}</div>
                    <div class="row"><strong>Teléfono:</strong> {{ $factura->cliente_telefono ?? '—' }}</div>
                    @if($factura->pedido?->usuario?->email)
                        <div class="row"><strong>Email:</strong> {{ $factura->pedido->usuario->email }}</div>
                    @endif
                </div>
            </td>
            <td>
                <div class="info-card">
                    <h4>DETALLE DEL PEDIDO</h4>
                    @if($factura->pedido)
                        <div class="row"><strong>N° pedido:</strong> {{ $factura->pedido->numero_pedido }}</div>
                        @php
                            $tipoLabel = [
                                'mesa' => 'En el restaurante',
                                'delivery' => 'Delivery',
                                'para_llevar' => 'Para llevar',
                            ][$factura->pedido->tipo_pedido] ?? ucfirst($factura->pedido->tipo_pedido);
                        @endphp
                        <div class="row"><strong>Tipo:</strong> {{ $tipoLabel }}</div>
                        @if($factura->pedido->mesa)
                            <div class="row"><strong>Mesa:</strong> {{ $factura->pedido->mesa->numero_mesa }} ({{ $factura->pedido->mesa->area ?? 'General' }})</div>
                        @endif
                        @if($factura->pedido->direccion)
                            <div class="row"><strong>Dirección:</strong> {{ $factura->pedido->direccion }}</div>
                        @endif
                        @if($factura->pedido->usuario)
                            <div class="row"><strong>Atendido por:</strong> {{ $factura->pedido->usuario->name }}</div>
                        @endif
                        <div class="row"><strong>Fecha pedido:</strong> {{ $factura->pedido->created_at->format('d/m/Y H:i') }}</div>
                    @else
                        <div class="row">Sin pedido asociado.</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- ============ DETALLE DE ITEMS ============ --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-desc">Descripción</th>
                <th class="col-cant">Cant.</th>
                <th class="col-pu">P. Unit.</th>
                <th class="col-sub">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @if($factura->pedido && $factura->pedido->detalles->count())
                @foreach($factura->pedido->detalles as $i => $detalle)
                    <tr>
                        <td class="col-num">{{ $i + 1 }}</td>
                        <td class="col-desc">
                            <strong>{{ $detalle->plato->nombre ?? 'Item' }}</strong>
                            @if($detalle->plato?->categoria)
                                <span style="color: #78716c; font-size: 9px;">— {{ $detalle->plato->categoria->nombre }}</span>
                            @endif
                            @if($detalle->notas)
                                <div class="item-nota">Nota: {{ $detalle->notas }}</div>
                            @endif
                        </td>
                        <td class="col-cant">{{ $detalle->cantidad }}</td>
                        <td class="col-pu">Bs {{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td class="col-sub">Bs {{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" style="text-align: center; color: #78716c;">Sin items detallados.</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- ============ NOTAS + TOTALES ============ --}}
    <table class="totals-wrap">
        <tr>
            <td class="left">
                @if($factura->pedido?->notas)
                    <div class="nota-pedido">
                        <strong>Nota del pedido:</strong><br>
                        {{ $factura->pedido->notas }}
                    </div>
                @endif
            </td>
            <td class="right">
                <table class="totals-table">
                    <tr>
                        <td class="l">Subtotal:</td>
                        <td class="r">Bs {{ number_format($factura->subtotal, 2) }}</td>
                    </tr>
                    @if($factura->descuento > 0)
                        <tr class="descuento">
                            <td class="l">Descuento:</td>
                            <td class="r">− Bs {{ number_format($factura->descuento, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="total">
                        <td class="l">TOTAL A PAGAR:</td>
                        <td class="r">Bs {{ number_format($factura->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ============ INFO DEL PAGO ============ --}}
    @if($factura->estado === 'pagada')
        @php
            $metodoLabel = [
                'efectivo' => 'Efectivo',
                'tarjeta' => 'Tarjeta',
                'qr' => 'Pago QR',
                'transferencia' => 'Transferencia bancaria',
            ][$factura->metodo_pago] ?? ucfirst($factura->metodo_pago ?? '—');
        @endphp
        <div class="pago-box">
            <strong>PAGO RECIBIDO</strong> &nbsp;·&nbsp; Método: {{ $metodoLabel }} &nbsp;·&nbsp;
            Fecha: {{ $factura->updated_at->format('d/m/Y H:i') }}
        </div>
    @else
        <div class="pago-box pendiente">
            <strong>PAGO PENDIENTE</strong> &nbsp;·&nbsp; Total adeudado: Bs {{ number_format($factura->total, 2) }}
        </div>
    @endif

    {{-- ============ FOOTER ============ --}}
    <div class="footer">
        <p>
            <span class="marca">Sabor &amp; Gestión</span> agradece su preferencia.<br>
            Este documento es una representación impresa de un comprobante digital emitido electrónicamente.<br>
            Para cualquier consulta sobre esta factura, conserve el número <strong>{{ $factura->numero_factura }}</strong>.
        </p>
        <p style="margin-top: 8px; font-size: 8px; color: #a8a29e;">
            Documento generado el {{ now()->format('d/m/Y H:i') }} ·
            Autorización fiscal: 7901234001 · CUF: {{ substr(md5($factura->numero_factura.$factura->total), 0, 32) }}
        </p>
    </div>

</body>
</html>
