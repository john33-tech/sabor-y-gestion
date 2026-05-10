<?php

use App\Http\Controllers\PagoQrController;
use Illuminate\Support\Facades\Route;

// Webhook para confirmación de pago QR (sin autenticación, llamado por sistema externo)
Route::post('/confirmar-pago-qr', [PagoQrController::class, 'confirmar']);
