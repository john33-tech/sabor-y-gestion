<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Los canales pedidos.cocineros y emisor-{emisor} ahora son públicos.
// No requieren autenticación en channels.php.
