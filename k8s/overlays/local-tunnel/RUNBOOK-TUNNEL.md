# Runbook: defensa por TUNEL sobre dominio propio (cluster local + TiDB)

Publica el cluster LOCAL (Docker Desktop) en internet con HTTPS sobre
**sabor-y-gestion.com**, conectado a TiDB Cloud. Reemplaza el flujo viejo de
comandos imperativos sueltos: ahora todo el estado vive en el overlay
`local-tunnel` (idempotente) + un Secret que creas una sola vez.

## 1) Secret real (una vez; NO va en git)

```bash
kubectl -n sabor-gestion create secret generic sabor-secret \
  --from-literal=APP_KEY="base64:uLRBO9uRStJgt4vGjuZRkecnU86wGnDz9fi+mOLogFE=" \
  --from-literal=DB_PASSWORD="<PASSWORD-REAL-TIDB>" \
  --from-literal=PUSHER_APP_SECRET="5a2c869670c35d92d93b" \
  --from-literal=RESEND_API_KEY="" \
  --dry-run=client -o yaml | kubectl apply -f -
```

## 2) Aplicar el overlay

```bash
kubectl apply -k k8s/overlays/local-tunnel
kubectl -n sabor-gestion rollout restart deploy/app
kubectl -n sabor-gestion rollout status deploy/app --timeout=180s
```

Verificar TiDB (solo lectura):

```bash
kubectl -n sabor-gestion exec deploy/app -- php artisan tinker \
  --execute='echo "users=".DB::table("users")->count()." platos=".DB::table("platos")->count().PHP_EOL;'
```

---

## 3) Cloudflare Tunnel con el dominio (HTTPS limpio, sin pagina de aviso)

Da una URL **fija** `https://sabor-y-gestion.com` con TLS de Cloudflare, sin la
pantalla de aviso de loca.lt, y funciona detras del NAT de casa (el tunel sale
hacia afuera; no hace falta IP fija ni abrir puertos).

### 3a) Pasar el dominio a Cloudflare (manual, en el navegador) — UNA VEZ
1. Crea cuenta gratis en https://dash.cloudflare.com → **Add a site** →
   `sabor-y-gestion.com` → plan **Free**.
2. Cloudflare te da **2 nameservers** (ej. `xena.ns.cloudflare.com`).
3. En **Porkbun** → el dominio → **NS / Authoritative Nameservers** → reemplaza
   los de Porkbun por los 2 de Cloudflare → guardar.
4. Espera la activacion (mail de Cloudflare; 5 min a unas horas). El registro A
   `4.157.216.0` que estaba en Porkbun deja de aplicar (Cloudflare maneja el DNS).

### 3b) Crear y correr el tunel (en esta maquina, Windows)
```powershell
$cf = "C:\Users\Anagua\cloudflared.exe"

# Login: abre el navegador, elige sabor-y-gestion.com (guarda ~/.cloudflared/cert.pem)
& $cf tunnel login

# Crear el tunel con nombre (guarda credenciales <UUID>.json en ~/.cloudflared)
& $cf tunnel create sabor-gestion

# Crear el registro DNS (CNAME al tunel) para la raiz y www
& $cf tunnel route dns sabor-gestion sabor-y-gestion.com
& $cf tunnel route dns sabor-gestion www.sabor-y-gestion.com
```

Crea `C:\Users\Anagua\.cloudflared\config.yml`:
```yaml
tunnel: sabor-gestion
credentials-file: C:\Users\Anagua\.cloudflared\<UUID>.json
ingress:
  - hostname: sabor-y-gestion.com
    service: http://localhost:80
  - hostname: www.sabor-y-gestion.com
    service: http://localhost:80
  - service: http_status:404
```

Correr (queda en primer plano; no cerrar durante la defensa):
```powershell
& $cf tunnel run sabor-gestion
```

Abrir **https://sabor-y-gestion.com** → candado 🔒, sin pagina de aviso.

> El cluster ya esta listo: el Ingress es catch-all (responde al Host
> sabor-y-gestion.com) y APP_ENV=production sirve https. No hay que tocar nada mas.

### (Opcional) correr el tunel como servicio de Windows
```powershell
& $cf service install
```

## Notas
- Si cambias de maquina o reinstalas, repite 3b (login + create + route).
- Backup sin pasar el dominio a Cloudflare: `cloudflared tunnel --url http://localhost:80`
  da una URL `*.trycloudflare.com` efimera (sin aviso, pero nombre random).
- `iniciar-defensa.sh` (LocalTunnel `sabor-y-gestion.loca.lt`) queda como ultimo
  recurso (tiene pagina de aviso).
