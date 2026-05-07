// resources/js/app.js
import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Registrar el plugin collapse
Alpine.plugin(collapse);

// Hacer Alpine disponible globalmente
window.Alpine = Alpine;

// Iniciar Alpine
Alpine.start();

console.log('Alpine.js inicializado correctamente');

// Configuración de notificaciones en tiempo real
document.addEventListener('DOMContentLoaded', () => {
    const userRole = document.querySelector('meta[name="user-role"]')?.getAttribute('content');
    console.log('Rol de usuario detectado:', userRole);
    
    if (window.Echo) {
        if (userRole === 'cocinero' || userRole === 'admin') {
            console.log('Intentando suscribirse al canal privado: pedidos.cocineros');
            
            window.Echo.private('pedidos.cocineros')
                .subscribed(() => {
                    console.log('✅ Suscripción exitosa al canal pedidos.cocineros');
                })
                .listen('.pedido.creado', (e) => {
                    console.log('🔔 ¡Nuevo pedido recibido!', e);
                    
                    // Notificación de escritorio
                    if (Notification.permission === "granted") {
                        new Notification("Nuevo Pedido", {
                            body: e.mensaje,
                            icon: "/logo.png"
                        });
                    } else if (Notification.permission !== "denied") {
                        Notification.requestPermission().then(permission => {
                            if (permission === "granted") {
                                new Notification("Nuevo Pedido", {
                                    body: e.mensaje,
                                    icon: "/logo.png"
                                });
                            }
                        });
                    }

                    // Disparar evento global para otros componentes
                    window.dispatchEvent(new CustomEvent('nuevo-pedido', { detail: e }));
                    
                    // Alerta visual simple
                    alert(e.mensaje);
                })
                .error((error) => {
                    console.error('❌ Error en la suscripción al canal:', error);
                });
        } else {
            console.log('El rol ' + userRole + ' no tiene acceso al canal de cocineros.');
        }
    } else {
        console.error('Laravel Echo no está inicializado.');
    }
});
