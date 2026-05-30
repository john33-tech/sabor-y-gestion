# HTTPS en la nube (AKS) con Let's Encrypt — sin comprar dominio

Lleva el despliegue `cloud-demo` (HTTP) a **HTTPS real** con certificado válido
y gratuito, usando **ingress-nginx + cert-manager + Let's Encrypt** y un hostname
gratis de **nip.io** (mapea cualquier IP a un dominio: `<IP>.nip.io`).

> Requiere tener ya desplegada la app (`kubectl apply -k k8s/overlays/cloud-demo`).
> Todo se corre desde **Azure Cloud Shell** (o cualquier shell con `kubectl`).

## Pasos

### 1. Instalar ingress-nginx y cert-manager

```bash
kubectl apply -f https://raw.githubusercontent.com/kubernetes/ingress-nginx/controller-v1.11.3/deploy/static/provider/cloud/deploy.yaml
kubectl apply -f https://github.com/cert-manager/cert-manager/releases/download/v1.15.3/cert-manager.yaml
kubectl -n cert-manager rollout status deploy/cert-manager-webhook
```

### 2. Obtener la IP pública del ingress y armar el hostname

```bash
IP=$(kubectl -n ingress-nginx get svc ingress-nginx-controller \
       -o jsonpath='{.status.loadBalancer.ingress[0].ip}')
HOST=${IP}.nip.io
echo "Tu hostname será: $HOST"
```

### 3. Crear el ClusterIssuer (editá el email en clusterissuer.yaml)

```bash
kubectl apply -f k8s/cloud-https/clusterissuer.yaml
```

### 4. Crear el Ingress HTTPS (reemplaza __HOST__ por tu hostname)

```bash
sed "s/__HOST__/${HOST}/g" k8s/cloud-https/ingress.template.yaml | kubectl apply -f -
```

### 5. Poner la app en modo producción con la URL https

Con TLS real, el `URL::forceScheme('https')` de `AppServiceProvider` ya es correcto:

```bash
kubectl -n sabor-gestion set env deployment/app APP_ENV=production APP_URL=https://$HOST
kubectl -n sabor-gestion rollout status deployment/app
```

### 6. Esperar el certificado (~1-2 min) y probar

```bash
kubectl -n sabor-gestion get certificate          # READY debe quedar en True
curl -sI https://$HOST/                            # HTTP 200, cert válido
```

Abrí `https://<IP>.nip.io` → candado verde 🔒. HTTP redirige a HTTPS solo.

## Notas

- **Por qué nip.io**: Let's Encrypt no emite certificados para una IP pelada;
  necesita un nombre DNS. `nip.io` te da uno gratis que resuelve a tu IP.
- **WebSockets (Reverb)**: viajan por `wss` sobre el mismo host; las anotaciones
  de timeout del Ingress mantienen viva la conexión.
- **Limpieza**: el Service `app` del overlay cloud-demo queda como LoadBalancer
  (IP HTTP redundante). Si querés dejar SOLO HTTPS, pasalo a ClusterIP:
  `kubectl -n sabor-gestion patch svc app -p '{"spec":{"type":"ClusterIP"}}'`
- **Staging primero (opcional)**: para no gastar el rate-limit de Let's Encrypt
  mientras probás, usá el server `https://acme-staging-v02.api.letsencrypt.org/directory`
  (cert no confiable, solo para validar el flujo) y luego cambiá a producción.
