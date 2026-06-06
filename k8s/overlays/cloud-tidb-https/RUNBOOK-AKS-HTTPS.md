# Runbook: sabor-y-gestion.com con HTTPS en AKS (Ingress + cert-manager)

Publica la app en **https://sabor-y-gestion.com** con certificado Let's Encrypt
automatico, conectada a TiDB Cloud. Ejecuta TODO en **Azure Cloud Shell** (boton
`>_` en https://portal.azure.com, elige **Bash**): ya trae `az`, `kubectl` y `helm`.

> ⚠️ **Cambio de arquitectura vs. el demo por IP (`cloud-tidb`):** antes la app se
> exponia como Service `LoadBalancer` (IP publica directa, HTTP). Ahora la expone
> **ingress-nginx**, que tiene **su propia IP publica nueva**. Por eso el paso 6
> **repunta el DNS** a esa IP nueva (la `4.157.216.0` que pusiste era la del LB
> viejo del Service `app`).

---

## 0) Variables y conexion al cluster

```bash
RG=sabor-rg
AKS=sabor-aks
EMAIL=anagua.18kahuana@gmail.com   # para Let's Encrypt (avisos de expiracion)

# Si el cluster esta apagado para ahorrar credito, prendelo:
az aks start -g $RG -n $AKS        # (ignora el error si ya esta corriendo)

az aks get-credentials -g $RG -n $AKS --overwrite-existing
kubectl get nodes                  # 1 nodo Ready
```

## 1) Instalar ingress-nginx (una vez)

```bash
helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx
helm repo update
helm install ingress-nginx ingress-nginx/ingress-nginx \
  --namespace ingress-nginx --create-namespace \
  --set controller.service.externalTrafficPolicy=Local
```

## 2) Instalar cert-manager (una vez)

```bash
helm repo add jetstack https://charts.jetstack.io
helm repo update
helm install cert-manager jetstack/cert-manager \
  --namespace cert-manager --create-namespace \
  --set crds.enabled=true
kubectl -n cert-manager rollout status deploy/cert-manager --timeout=180s
```

## 3) ClusterIssuer de Let's Encrypt (una vez)

```bash
kubectl apply -f - <<EOF
apiVersion: cert-manager.io/v1
kind: ClusterIssuer
metadata:
  name: letsencrypt-prod
spec:
  acme:
    server: https://acme-v02.api.letsencrypt.org/directory
    email: ${EMAIL}
    privateKeySecretRef:
      name: letsencrypt-prod-account-key
    solvers:
      - http01:
          ingress:
            class: nginx
EOF
```

## 4) IP publica del ingress (1-3 min)

```bash
kubectl -n ingress-nginx get svc ingress-nginx-controller -w
# Ctrl+C cuando EXTERNAL-IP deje de ser <pending>. Anota esa IP -> INGRESS_IP
```

## 5) Secret real (si la namespace es nueva o lo borraste)

> Si el namespace `sabor-gestion` ya existe de tu deploy anterior, el Secret
> probablemente sigue ahi: salta este paso. Si no, recrealo con tus valores
> REALES (los mismos de RUNBOOK-AKS.md; NO los de git):

```bash
kubectl create namespace sabor-gestion 2>/dev/null

kubectl -n sabor-gestion create secret generic sabor-secret \
  --from-literal=APP_KEY="base64:CBuXycAxuiTTs7NUbBZ7kSykrKsY1iqSfRrb6NJ2OKc=" \
  --from-literal=DB_PASSWORD="<PASSWORD-REAL-TIDB>" \
  --from-literal=REVERB_APP_SECRET="saborsecret" \
  --from-literal=RESEND_API_KEY="" \
  --dry-run=client -o yaml | kubectl apply -f -
```

> Si el broadcasting server-side por Pusher falla (eventos no llegan), agrega
> tambien `--from-literal=PUSHER_APP_SECRET="<secret-de-pusher>"`.

## 6) ⚠️ Repuntar el DNS a la IP del ingress

En Porkbun -> DNS Records, **edita el registro A** de la raiz:

```
A   @ (vacio)   ->   <INGRESS_IP del paso 4>     TTL 600
```

Verifica antes de seguir (la nueva IP debe responder):

```bash
nslookup sabor-y-gestion.com 8.8.8.8
```

> cert-manager usa un challenge HTTP-01: **no emite el certificado hasta que el
> dominio resuelva a la IP del ingress**. Si aplicas antes de repuntar, el cert
> queda en estado pendiente hasta que el DNS propague (luego se emite solo).

## 7) Desplegar el overlay HTTPS

```bash
# (en Cloud Shell, dentro del repo clonado)
git pull
kubectl apply -k k8s/overlays/cloud-tidb-https
kubectl -n sabor-gestion rollout restart deploy/app
kubectl -n sabor-gestion rollout status deploy/app --timeout=300s
```

## 8) Verificar el certificado y el acceso

```bash
kubectl -n sabor-gestion get certificate           # READY debe pasar a True (1-3 min)
kubectl -n sabor-gestion describe certificate sabor-tls   # si tarda, mira Events
curl -sSI https://sabor-y-gestion.com | head -5
```

Abri **https://sabor-y-gestion.com** -> candado 🔒.

---

## Problemas comunes

```bash
# Cert no pasa a Ready: revisa el challenge
kubectl -n sabor-gestion get challenge
kubectl -n sabor-gestion describe challenge
# Causa #1: el DNS aun no apunta a la IP del ingress (paso 6).

# 404 / default backend: el Host no coincide -> confirma el Ingress
kubectl -n sabor-gestion get ingress sabor-ingress -o wide

# App 0/1 un rato: normal (1a carga abre SSL a TiDB; startupProbe 15s).
kubectl -n sabor-gestion logs deploy/app --tail=50
```

## Notas / pendientes a vigilar

- **APP_ENV=production + HTTPS:** Laravel detras del ingress recibe el trafico por
  HTTP con cabecera `X-Forwarded-Proto: https`. Si ves mixed-content (assets por
  http) o redirecciones raras, revisa `TrustProxies` (que confie en el proxy del
  ingress, `'*'`).
- **Assets de Vite / Reverb-Pusher en el front:** si las VITE_* se compilaron en la
  imagen con la IP/host viejo, puede que haya que rebuildear la imagen con el host
  nuevo. Verifica en el navegador (consola) que el JS apunte a sabor-y-gestion.com.
- **Tag de imagen:** el overlay usa `ghcr.io/john33-tech/sabor-gestion:e584144`
  (igual que cloud-tidb). Si tienes una imagen mas nueva, edita `newTag` en
  `kustomization.yaml`.

## Apagar para no gastar credito

```bash
az aks stop -g $RG -n $AKS
```
