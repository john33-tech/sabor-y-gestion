// resources/js/app.js
import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

console.log('Alpine.js inicializado correctamente');

/**
 * Toast bonito con animación slide-in / slide-out + auto-dismiss.
 * Reemplaza el alert() horrible que rompía la UX.
 */
function mostrarToastNuevoPedido(numeroPedido, mensaje) {
    // Contenedor (lo crea una vez y reusa).
    let stack = document.getElementById('toast-stack-pedidos');
    if (!stack) {
        stack = document.createElement('div');
        stack.id = 'toast-stack-pedidos';
        stack.style.cssText = `
            position: fixed; top: 90px; right: 20px; z-index: 9999;
            display: flex; flex-direction: column; gap: 12px;
            pointer-events: none;
        `;
        document.body.appendChild(stack);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white; padding: 16px 20px; border-radius: 12px;
        box-shadow: 0 10px 30px rgba(249, 115, 22, 0.4);
        min-width: 320px; max-width: 400px;
        display: flex; align-items: center; gap: 14px;
        transform: translateX(120%); opacity: 0;
        transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s;
        pointer-events: auto; cursor: pointer;
        font-family: inherit;
    `;
    toast.innerHTML = `
        <div style="
            width: 48px; height: 48px; border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; animation: pulseRing 1.5s infinite;
        ">
            <i class="fas fa-bell" style="font-size: 22px;"></i>
        </div>
        <div style="flex:1; min-width:0;">
            <div style="font-weight: 700; font-size: 15px; margin-bottom: 2px;">
                🔔 ¡Nuevo pedido recibido!
            </div>
            <div style="font-size: 13px; opacity: 0.95;">
                ${numeroPedido ? '<strong>' + numeroPedido + '</strong> — ' : ''}${mensaje || 'Hay un pedido esperando preparación'}
            </div>
        </div>
        <button style="
            background: rgba(255,255,255,0.2); border: none; color: white;
            width: 28px; height: 28px; border-radius: 50%;
            font-size: 16px; cursor: pointer; flex-shrink: 0;
        " title="Cerrar">×</button>
    `;

    stack.appendChild(toast);

    // Slide-in
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    });

    // Auto-dismiss a los 5s
    const dismiss = () => {
        toast.style.transform = 'translateX(120%)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    };
    const timer = setTimeout(dismiss, 5000);

    // Click en cualquier parte del toast lo cierra
    toast.addEventListener('click', () => {
        clearTimeout(timer);
        dismiss();
    });

    // Inyectar keyframes una sola vez
    if (!document.getElementById('toast-pedido-styles')) {
        const styles = document.createElement('style');
        styles.id = 'toast-pedido-styles';
        styles.textContent = `
            @keyframes pulseRing {
                0%   { box-shadow: 0 0 0 0 rgba(255,255,255,0.5); }
                70%  { box-shadow: 0 0 0 12px rgba(255,255,255,0); }
                100% { box-shadow: 0 0 0 0 rgba(255,255,255,0); }
            }
            @keyframes highlightNew {
                0%   { box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.8); transform: scale(1.02); }
                100% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0);   transform: scale(1); }
            }
            .pedido-card-nuevo {
                animation: highlightNew 2s ease-out;
            }
        `;
        document.head.appendChild(styles);
    }
}

/**
 * Toast para cambios de estado del pedido del cliente.
 * Usa el mismo stack que mostrarToastNuevoPedido pero con paleta verde.
 */
function mostrarToastEstadoPedido(numeroPedido, estado, mensaje) {
    let stack = document.getElementById('toast-stack-pedidos');
    if (!stack) {
        stack = document.createElement('div');
        stack.id = 'toast-stack-pedidos';
        stack.style.cssText = `
            position: fixed; top: 90px; right: 20px; z-index: 9999;
            display: flex; flex-direction: column; gap: 12px;
            pointer-events: none;
        `;
        document.body.appendChild(stack);
    }

    const colorPorEstado = {
        en_preparacion: { from: '#3b82f6', to: '#1d4ed8', icon: 'fa-fire',      titulo: '👨‍🍳 ¡Pedido en cocina!' },
        listo:          { from: '#10b981', to: '#059669', icon: 'fa-bell',      titulo: '🔔 ¡Tu pedido está listo!' },
        entregado:      { from: '#0ea5e9', to: '#0369a1', icon: 'fa-check-double', titulo: '✅ ¡Pedido entregado!' },
        cancelado:      { from: '#ef4444', to: '#b91c1c', icon: 'fa-ban',       titulo: 'Pedido cancelado' },
        facturado:      { from: '#8b5cf6', to: '#6d28d9', icon: 'fa-file-invoice-dollar', titulo: 'Pedido facturado' },
    };
    const colores = colorPorEstado[estado] || { from: '#6b7280', to: '#374151', icon: 'fa-info-circle', titulo: 'Pedido actualizado' };

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: linear-gradient(135deg, ${colores.from}, ${colores.to});
        color: white; padding: 16px 20px; border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        min-width: 320px; max-width: 400px;
        display: flex; align-items: center; gap: 14px;
        transform: translateX(120%); opacity: 0;
        transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s;
        pointer-events: auto; cursor: pointer;
        font-family: inherit;
    `;
    toast.innerHTML = `
        <div style="
            width: 48px; height: 48px; border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        ">
            <i class="fas ${colores.icon}" style="font-size: 22px;"></i>
        </div>
        <div style="flex:1; min-width:0;">
            <div style="font-weight: 700; font-size: 15px; margin-bottom: 2px;">
                ${colores.titulo}
            </div>
            <div style="font-size: 13px; opacity: 0.95;">
                ${numeroPedido ? '<strong>' + numeroPedido + '</strong>' : ''}${mensaje ? ' — ' + mensaje : ''}
            </div>
        </div>
        <button style="
            background: rgba(255,255,255,0.2); border: none; color: white;
            width: 28px; height: 28px; border-radius: 50%;
            font-size: 16px; cursor: pointer; flex-shrink: 0;
        " title="Cerrar">×</button>
    `;

    stack.appendChild(toast);
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    });

    const dismiss = () => {
        toast.style.transform = 'translateX(120%)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    };
    const timer = setTimeout(dismiss, 6000);
    toast.addEventListener('click', () => { clearTimeout(timer); dismiss(); });
}

/**
 * Toast verde para avisar que una cuenta se cobró/cerró (mesa pagada).
 */
function mostrarToastPago(titulo, pago) {
    let stack = document.getElementById('toast-stack-pedidos');
    if (!stack) {
        stack = document.createElement('div');
        stack.id = 'toast-stack-pedidos';
        stack.style.cssText = `position: fixed; top: 90px; right: 20px; z-index: 9999; display:flex; flex-direction:column; gap:12px; pointer-events:none;`;
        document.body.appendChild(stack);
    }
    const toast = document.createElement('div');
    toast.style.cssText = `background: linear-gradient(135deg,#10b981,#059669); color:white; padding:14px 18px; border-radius:12px; box-shadow:0 10px 30px rgba(16,185,129,0.4); min-width:300px; max-width:400px; display:flex; align-items:center; gap:12px; transform:translateX(120%); opacity:0; transition:transform .5s cubic-bezier(.34,1.56,.64,1), opacity .3s; pointer-events:auto; cursor:pointer; font-family:inherit;`;
    const detalle = [pago && pago.total ? ('Bs ' + pago.total) : null, pago ? pago.metodo : null].filter(Boolean).join(' · ');
    toast.innerHTML = `
        <div style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-cash-register" style="font-size:18px;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-weight:700;font-size:14px;">💰 ${titulo} cobrada</div>
            <div style="font-size:12px;opacity:.95;">${detalle}</div>
        </div>`;
    toast.onclick = () => toast.remove();
    stack.appendChild(toast);
    requestAnimationFrame(() => { toast.style.transform = 'translateX(0)'; toast.style.opacity = '1'; });
    setTimeout(() => { toast.style.transform = 'translateX(120%)'; toast.style.opacity = '0'; setTimeout(() => toast.remove(), 500); }, 5000);
}

/**
 * Reproduce un beep simple sin necesidad de archivos de audio.
 * Usa Web Audio API — funciona en cualquier navegador moderno.
 */
function beep(frecuencia = 880, duracionMs = 200) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = frecuencia;
        gain.gain.setValueAtTime(0.1, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duracionMs / 1000);
        osc.start();
        osc.stop(ctx.currentTime + duracionMs / 1000);
    } catch (e) {
        // Si el navegador bloquea audio antes del primer click del usuario, silenciar.
    }
}

// Configuración de notificaciones en tiempo real
document.addEventListener('DOMContentLoaded', () => {
    const userRole = document.querySelector('meta[name="user-role"]')?.getAttribute('content');
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
    console.log('Rol de usuario detectado:', userRole, 'ID:', userId);

    if (!window.Echo) {
        console.error('Laravel Echo no está inicializado.');
        return;
    }

    // ----- Canal de cocineros (toast al recibir nuevo pedido) -----
    if (userRole === 'cocinero' || userRole === 'admin') {
        console.log('Suscribiéndose al canal pedidos.cocineros');
        window.Echo.channel('pedidos.cocineros')
            .subscribed(() => console.log('✅ Suscrito a pedidos.cocineros'))
            .listen('.pedido.creado', (e) => {
                console.log('🔔 ¡Nuevo pedido recibido!', e);
                mostrarToastNuevoPedido(e.numero_pedido, e.mensaje);
                if (typeof Notification !== 'undefined') {
                    if (Notification.permission === 'granted') {
                        new Notification('🔔 Nuevo pedido', {
                            body: e.mensaje || 'Hay un pedido esperando preparación',
                            icon: '/favicon.ico',
                            tag: 'pedido-' + (e.numero_pedido || Date.now()),
                        });
                    } else if (Notification.permission !== 'denied') {
                        Notification.requestPermission();
                    }
                }
                window.dispatchEvent(new CustomEvent('nuevo-pedido', { detail: e }));
            })
            .listen('.pedido.estado.cambiado', (e) => {
                // Si el pedido salió del kitchen display (cancelado/entregado/facturado),
                // refrescamos la vista para que el card desaparezca en vivo.
                const estadosEnCocina = ['pendiente', 'en_preparacion', 'listo'];
                if (!estadosEnCocina.includes(e.estado)) {
                    console.log('🗑️ Comandas: pedido salió de cocina', e);
                    window.dispatchEvent(new CustomEvent('refresh-comandas', { detail: e }));
                }
            })
            .listen('.pedido.eliminado', (e) => {
                console.log('🗑️ Comandas: pedido eliminado', e);
                window.dispatchEvent(new CustomEvent('refresh-comandas', { detail: e }));
            })
            .error((err) => console.error('❌ Error en pedidos.cocineros:', err));
    }

    // ----- Canal global de meseros (toast cuando un pedido pasa a "listo") -----
    if (['mesero', 'cajero', 'admin'].includes(userRole)) {
        console.log('Suscribiéndose al canal pedidos.meseros');
        window.Echo.channel('pedidos.meseros')
            .subscribed(() => console.log('✅ Suscrito a pedidos.meseros'))
            .listen('.pedido.estado.cambiado', (e) => {
                if (e.estado !== 'listo') return; // solo nos interesa "listo"
                console.log('🍽️ Pedido listo para entregar', e);
                mostrarToastEstadoPedido(e.numero_pedido, 'listo', 'Pedido listo, retirar de cocina');
                beep(1100, 250);
                if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
                    new Notification('🔔 Pedido listo para entregar', {
                        body: e.numero_pedido ? `${e.numero_pedido} esperando ser entregado` : '',
                        icon: '/favicon.ico',
                        tag: 'mesero-listo-' + e.pedido_id,
                    });
                }
            })
            .listen('.cuenta.pagada', (e) => {
                console.log('💰 Cuenta pagada/cerrada', e);
                const titulo = e.mesa ? ('Mesa ' + e.mesa) : (e.numero_factura || 'Cuenta');
                mostrarToastPago(titulo, e);
                beep(900, 200);
                // Si estoy en la pantalla de caja (Cierre de Cuenta o Cierre de
                // Caja), refresco para reflejar el pago/cierre sin acción manual.
                const ruta = window.location.pathname;
                if (ruta.startsWith('/cierres') || ruta.startsWith('/caja')) {
                    setTimeout(() => window.location.reload(), 1500);
                }
                window.dispatchEvent(new CustomEvent('cuenta-pagada', { detail: e }));
            })
            .error((err) => console.error('❌ Error en pedidos.meseros:', err));
    }

    // ----- Canal del cliente (toast cuando su pedido cambia de estado) -----
    if (userRole === 'cliente' && userId) {
        const canalCliente = 'cliente.' + userId + '.pedidos';
        console.log('Suscribiéndose al canal', canalCliente);
        window.Echo.channel(canalCliente)
            .subscribed(() => console.log('✅ Suscrito a', canalCliente))
            .listen('.pedido.estado.cambiado', (e) => {
                console.log('📦 Cambio de estado del pedido', e);

                const labels = {
                    en_preparacion: 'Tu pedido está en cocina',
                    listo:          'Tu pedido está listo para retirar',
                    entregado:      'Tu pedido fue entregado',
                    cancelado:      'Tu pedido fue cancelado',
                    facturado:      'Tu pedido fue facturado',
                };
                mostrarToastEstadoPedido(e.numero_pedido, e.estado, labels[e.estado] || '');
                beep(e.estado === 'listo' ? 1100 : 700, 220);

                if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
                    new Notification(labels[e.estado] || 'Tu pedido se actualizó', {
                        body: e.numero_pedido ? `Pedido ${e.numero_pedido}` : '',
                        icon: '/favicon.ico',
                        tag: 'estado-pedido-' + e.pedido_id,
                    });
                }

                // Evento global para que la página actual refresque el timeline sin recargar
                window.dispatchEvent(new CustomEvent('pedido-estado-cambiado', { detail: e }));
            })
            .error((err) => console.error('❌ Error en ' + canalCliente + ':', err));
    }
});
