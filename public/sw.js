/**
 * Service Worker de Sabor & Gestión (PWA).
 *
 * Estrategia SEGURA pensada para una app dinámica con login:
 *  - Navegaciones (HTML): NETWORK-FIRST. Siempre se pide a la red para no
 *    servir páginas cacheadas viejas ni datos de otra sesión. Si no hay red,
 *    se cae a /offline.html. (Nunca cacheamos respuestas HTML autenticadas.)
 *  - Estáticos same-origin (íconos, build de Vite, logo, manifest):
 *    STALE-WHILE-REVALIDATE para que la app abra al instante y se actualice
 *    en segundo plano.
 *  - Sólo se cachea GET y same-origin. POST / API / otros orígenes pasan
 *    directo a la red.
 *
 * Subir CACHE_VERSION invalida el caché viejo en el siguiente deploy.
 */
const CACHE_VERSION = 'sabor-v2';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const OFFLINE_URL = '/offline.html';

// App shell mínimo que se precachea en la instalación.
const PRECACHE = [
    OFFLINE_URL,
    '/manifest.json',
    '/icon-192.png',
    '/icon-512.png',
    '/logo.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => !k.startsWith(CACHE_VERSION)).map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// Permite que la página fuerce la activación de un SW nuevo.
self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') self.skipWaiting();
});

function esEstaticoCacheable(url) {
    return (
        url.pathname.startsWith('/icon-') ||
        url.pathname === '/apple-touch-icon.png' ||
        url.pathname.startsWith('/build/') ||
        url.pathname === '/logo.png' ||
        url.pathname === '/favicon.ico' ||
        url.pathname === '/manifest.json'
    );
}

self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Sólo GET. POST/PUT/DELETE (crear pedido, etc.) van directo a la red.
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Sólo same-origin. CDNs (Leaflet, Font Awesome, tiles de OSM, Pusher) → red.
    if (url.origin !== self.location.origin) return;

    // Navegaciones (páginas HTML): network-first con fallback offline.
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Estáticos: stale-while-revalidate.
    if (esEstaticoCacheable(url)) {
        event.respondWith(
            caches.open(STATIC_CACHE).then((cache) =>
                cache.match(req).then((cached) => {
                    const network = fetch(req).then((resp) => {
                        if (resp && resp.status === 200) cache.put(req, resp.clone());
                        return resp;
                    }).catch(() => cached);
                    return cached || network;
                })
            )
        );
    }
});
