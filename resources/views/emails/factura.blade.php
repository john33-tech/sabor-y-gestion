<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Su Factura de SaborGestion</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #4F46E5;">¡Gracias por su compra, {{ $factura->cliente_nombre }}!</h2>
        
        <p>Adjunto a este correo encontrará su factura electrónica <strong>{{ $factura->numero_factura }}</strong>.</p>
        
        <div style="background-color: #f9fafb; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0;"><strong>Total pagado:</strong> Bs. {{ number_format($factura->total, 2) }}</p>
            <p style="margin: 0;"><strong>Método de pago:</strong> {{ ucfirst($factura->metodo_pago) }}</p>
        </div>
        
        <p>Si tiene alguna pregunta sobre esta factura, no dude en contactarnos.</p>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #777; text-align: center;">Este es un correo generado automáticamente. Por favor no responda directamente.</p>
    </div>
</body>
</html>
