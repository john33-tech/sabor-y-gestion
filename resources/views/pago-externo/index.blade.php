<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pago QR — Sabor & Gestión</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 380px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #c2410c, #ea580c);
            color: white;
            text-align: center;
            padding: 30px 20px;
        }
        .header h1 { font-size: 22px; margin-bottom: 4px; font-weight: 700; }
        .header p { font-size: 13px; opacity: 0.9; }
        .body { padding: 30px 24px; }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .row:last-of-type { border-bottom: none; }
        .row .label { color: #6b7280; }
        .row .value { color: #111827; font-weight: 600; text-align: right; }
        .total-box {
            background: #fff7ed;
            border: 2px solid #fed7aa;
            border-radius: 12px;
            padding: 18px;
            margin: 20px 0;
            text-align: center;
        }
        .total-box .label {
            font-size: 12px;
            color: #9a3412;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .total-box .amount {
            font-size: 36px;
            color: #c2410c;
            font-weight: 800;
        }
        .btn-pay {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(16,185,129,0.3);
            transition: transform 0.1s, box-shadow 0.2s;
        }
        .btn-pay:active { transform: translateY(2px); box-shadow: 0 5px 10px rgba(16,185,129,0.3); }
        .btn-pay:disabled { background: #9ca3af; box-shadow: none; cursor: wait; }
        .btn-pay .spinner {
            display: inline-block;
            width: 16px; height: 16px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: -3px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .success {
            text-align: center;
            padding: 20px 0;
        }
        .success .check {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px; height: 80px;
            border-radius: 50%;
            background: #d1fae5;
            color: #059669;
            font-size: 40px;
            margin-bottom: 16px;
            animation: pop 0.5s ease;
        }
        @keyframes pop {
            0% { transform: scale(0); }
            70% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }
        .success h2 { color: #059669; font-size: 22px; margin-bottom: 8px; }
        .success p { color: #6b7280; font-size: 14px; }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-top: 12px;
            display: none;
        }
        .footer {
            text-align: center;
            padding: 16px;
            font-size: 11px;
            color: #9ca3af;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Sabor & Gestión</h1>
            <p>Pago QR · Bolivia</p>
        </div>

        <div class="body" id="bodyPago">
            <div class="row">
                <span class="label">Cliente</span>
                <span class="value">{{ $cliente ?? 'Consumidor' }}</span>
            </div>
            <div class="row">
                <span class="label">Pedido</span>
                <span class="value">#{{ $pedido }}</span>
            </div>
            @if(($descuento ?? 0) > 0)
            <div class="row">
                <span class="label">Descuento</span>
                <span class="value" style="color: #b91c1c;">− Bs {{ number_format($descuento, 2) }}</span>
            </div>
            @endif

            <div class="total-box">
                <div class="label">Total a pagar</div>
                <div class="amount">Bs {{ number_format($monto, 2) }}</div>
            </div>

            <button class="btn-pay" id="btnPay" onclick="pagar()">
                <span id="btnText">Pagar Bs {{ number_format($monto, 2) }}</span>
            </button>

            <div class="error" id="errorMsg"></div>
        </div>

        <div class="body" id="bodySuccess" style="display: none;">
            <div class="success">
                <div class="check">✓</div>
                <h2>¡Pago confirmado!</h2>
                <p>Bs {{ number_format($monto, 2) }} pagados con éxito.</p>
                <p style="margin-top: 8px;">El restaurante ya tiene tu pago registrado.</p>
            </div>
        </div>

        <div class="footer">
            Simulación de pasarela de pago QR — entorno académico
        </div>
    </div>

    @php
        $jsParams = ['emisor' => $emisor, 'pedido' => $pedido, 'monto' => (float) $monto];
    @endphp
    <script>
        const params = @json($jsParams);

        async function pagar() {
            const btn = document.getElementById('btnPay');
            const txt = document.getElementById('btnText');
            const err = document.getElementById('errorMsg');
            err.style.display = 'none';
            btn.disabled = true;
            txt.innerHTML = '<span class="spinner"></span>Procesando...';

            try {
                const res = await fetch('/api/confirmar-pago-qr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(params),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo procesar el pago');
                }
                // Mostrar pantalla de éxito
                document.getElementById('bodyPago').style.display = 'none';
                document.getElementById('bodySuccess').style.display = 'block';
            } catch (e) {
                err.textContent = e.message;
                err.style.display = 'block';
                btn.disabled = false;
                txt.textContent = 'Reintentar pago';
            }
        }
    </script>
</body>
</html>
