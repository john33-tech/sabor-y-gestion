{{-- Meta tags + registro del Service Worker de la PWA. Se incluye en el <head> de los layouts. --}}
<meta name="theme-color" content="#f97316">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Sabor & Gestión">
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">

<script>
    // --- Registro del Service Worker ---
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then((reg) => console.log('✅ Service Worker registrado:', reg.scope))
                .catch((err) => console.warn('SW no registrado:', err));
        });
    }

    // --- Botón flotante "Instalar app" (Android/Chrome/Edge) ---
    let deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        mostrarBotonInstalar();
    });

    function mostrarBotonInstalar() {
        if (document.getElementById('pwa-install-btn')) return;
        const btn = document.createElement('button');
        btn.id = 'pwa-install-btn';
        btn.type = 'button';
        btn.innerHTML = '<i class="fas fa-download" style="margin-right:8px"></i>Instalar app';
        btn.style.cssText = [
            'position:fixed', 'bottom:20px', 'right:20px', 'z-index:9998',
            'background:linear-gradient(135deg,#f97316,#ea580c)', 'color:#fff',
            'border:none', 'border-radius:9999px', 'padding:12px 20px',
            'font-size:14px', 'font-weight:700', 'cursor:pointer',
            'box-shadow:0 10px 25px rgba(249,115,22,.45)', 'font-family:inherit',
            'display:flex', 'align-items:center'
        ].join(';');
        btn.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log('Instalación PWA:', outcome);
            deferredPrompt = null;
            btn.remove();
        });
        document.body.appendChild(btn);
    }

    window.addEventListener('appinstalled', () => {
        console.log('🎉 PWA instalada');
        document.getElementById('pwa-install-btn')?.remove();
    });
</script>
