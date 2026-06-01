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

// Configuración Echo. Dos modos según las variables de build (Vite):
//
//  A) PUSHER CLOUD: si hay VITE_PUSHER_APP_KEY + VITE_PUSHER_APP_CLUSTER (y NO
//     se fuerza un host propio), Echo se conecta DIRECTO a Pusher cloud
//     (servidor gestionado, siempre por wss). Es lo usado en AKS/producción.
//
//  B) REVERB self-host: si hay VITE_REVERB_* (host propio), se conecta al MISMO
//     host de la página (Apache proxea /app/ al Reverb interno). Para local.
const isHttps = (typeof window !== 'undefined' && window.location.protocol === 'https:');
const defaultHost = (typeof window !== 'undefined') ? window.location.hostname : 'localhost';
const defaultPort = (typeof window !== 'undefined' && window.location.port)
    ? Number(window.location.port)
    : (isHttps ? 443 : 80);

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST;
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;

// Modo Pusher cloud: hay key de Pusher y NO se configuró un Reverb propio.
const usePusherCloud = !!pusherKey && !reverbKey && !reverbHost;

let echoConfig;
if (usePusherCloud) {
    // Pusher cloud gestiona el host (wss://ws-<cluster>.pusher.com). No fijamos wsHost.
    echoConfig = {
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster ?? 'mt1',
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
    };
} else {
    // Reverb self-host: mismo host que la página (proxy Apache).
    echoConfig = {
        broadcaster: 'pusher',
        key: reverbKey ?? pusherKey ?? 'saborkey',
        cluster: pusherCluster ?? 'mt1',
        wsHost: reverbHost ?? import.meta.env.VITE_PUSHER_HOST ?? defaultHost,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? import.meta.env.VITE_PUSHER_PORT ?? defaultPort),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? import.meta.env.VITE_PUSHER_PORT ?? defaultPort),
        forceTLS: isHttps,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
    };
}

window.Echo = new Echo(echoConfig);

console.log(`🔌 Echo → modo=${usePusherCloud ? 'PUSHER-CLOUD' : 'REVERB/self'} key=${echoConfig.key} cluster=${echoConfig.cluster}`);
