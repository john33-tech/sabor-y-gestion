import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Configuración Echo: usa el MISMO host de la página (Apache proxea /app/ a Reverb).
// En local docker compose: localhost:8080 (Apache + Reverb interno).
// En Railway producción: dominio.up.railway.app:443 (HTTPS + Apache proxy).
const isHttps = (typeof window !== 'undefined' && window.location.protocol === 'https:');
const defaultHost = (typeof window !== 'undefined') ? window.location.hostname : 'localhost';
const defaultPort = (typeof window !== 'undefined' && window.location.port)
    ? Number(window.location.port)
    : (isHttps ? 443 : 80);

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_REVERB_APP_KEY ?? import.meta.env.VITE_PUSHER_APP_KEY ?? 'saborkey',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_REVERB_HOST ?? import.meta.env.VITE_PUSHER_HOST ?? defaultHost,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? import.meta.env.VITE_PUSHER_PORT ?? defaultPort),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? import.meta.env.VITE_PUSHER_PORT ?? defaultPort),
    forceTLS: isHttps,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

console.log(`🔌 Echo configurado → ${isHttps ? 'wss' : 'ws'}://${window.Echo.options.wsHost}:${window.Echo.options.wsPort} (key: ${window.Echo.options.key})`);
