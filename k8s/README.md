# Despliegue en Kubernetes — Sabor & Gestión

Manifiestos de Kubernetes para correr la app **en local** (Docker Desktop /
Minikube) **y en la nube** (GKE / EKS / AKS) con la **misma imagen**, usando
**Kustomize** (`base` + `overlays`).

## Arquitectura

```
                 ┌──────────────────────────────────────────────┐
   navegador ───►│  Ingress (nginx)  host: localhost / dominio   │
   (HTTP + WS)   └───────────────┬──────────────────────────────┘
                                 │  HTTP + WebSocket (wss)
                         ┌───────▼────────┐
                         │  Service app   │  :80 → :8080
                         └───────┬────────┘
                                 │
              ┌──────────────────▼───────────────────┐
              │  Deployment app (1 réplica)           │
              │  ┌─────────────────────────────────┐  │
              │  │ Apache + PHP 8.2 (Laravel)       │  │  :8080
              │  │ Reverb en background  ──proxy──► │  │  :8081 (interno)
              │  └─────────────────────────────────┘  │
              │  PVC sabor-storage → storage/app      │
              └──────────────────┬───────────────────┘
                                 │ DB_HOST=mysql:3306
                         ┌───────▼────────┐
                         │ Service mysql  │  (headless)
                         └───────┬────────┘
                         ┌───────▼────────────────┐
                         │ StatefulSet mysql       │
                         │ PVC mysql-data (2Gi)    │
                         └─────────────────────────┘
```

- **Reverb va en el mismo pod** que Apache (igual que en docker-compose/Railway).
  Apache proxea `/app/` y `/apps/` al Reverb interno en `:8081`. El cliente Echo
  (`resources/js/bootstrap.js`) usa el host de la página, así que **la misma
  imagen sirve en local (ws) y en la nube (wss)** sin reconstruir.
- **MySQL** es un `StatefulSet` con disco persistente propio.
- Las **migraciones, caches y `storage:link`** las hace `docker/entrypoint.sh`
  al arrancar el pod (espera a MySQL antes de migrar).

## Estructura

```
k8s/
├── base/                  # recursos comunes a todos los entornos
│   ├── namespace.yaml
│   ├── configmap.yaml     # env NO sensible (valores de dev)
│   ├── secret.yaml        # env sensible (valores de dev; cambiar en prod)
│   ├── mysql-service.yaml
│   ├── mysql-statefulset.yaml
│   ├── app-pvc.yaml
│   ├── app-deployment.yaml
│   ├── app-service.yaml
│   ├── ingress.yaml
│   └── kustomization.yaml
└── overlays/
    ├── local/             # Docker Desktop / Minikube  (usa base tal cual)
    │   └── kustomization.yaml
    └── cloud/             # GKE / EKS / AKS
        ├── kustomization.yaml
        ├── configmap-patch.yaml   # APP_ENV=production, dominio, ...
        └── ingress-patch.yaml     # host real + TLS (wss)
```

---

## 🖥️  Local (Docker Desktop)

> Requisito: en Docker Desktop → Settings → Kubernetes → **Enable Kubernetes**.

### 1. Construir la imagen

Docker Desktop comparte el daemon con el cluster, así que basta construirla
localmente (no hace falta registry):

```powershell
docker build -t sabor-gestion:latest .
```

### 2. Instalar el Ingress Controller (una sola vez)

```powershell
kubectl apply -f https://raw.githubusercontent.com/kubernetes/ingress-nginx/controller-v1.11.3/deploy/static/provider/cloud/deploy.yaml
kubectl wait --namespace ingress-nginx --for=condition=ready pod --selector=app.kubernetes.io/component=controller --timeout=180s
```

### 3. Desplegar

```powershell
kubectl apply -k k8s/overlays/local
```

### 4. Ver el estado

```powershell
kubectl -n sabor-gestion get pods,svc,ingress
kubectl -n sabor-gestion logs -f deploy/app          # ver el arranque/migraciones
```

Cuando el pod `app` esté `Running` y `READY 1/1`, abrí:

👉 **http://localhost**

> **Sin Ingress** (alternativa rápida): en vez de los pasos 2, podés usar
> port-forward:
> ```powershell
> kubectl -n sabor-gestion port-forward svc/app 8080:80
> ```
> y abrir http://localhost:8080

### Minikube (en vez de Docker Desktop)

```powershell
minikube start
minikube addons enable ingress
minikube image build -t sabor-gestion:latest .     # construye dentro de Minikube
kubectl apply -k k8s/overlays/local
minikube tunnel                                    # deja esta terminal abierta
# luego http://localhost
```

---

## ☁️  Nube (GKE / EKS / AKS / DigitalOcean)

### 1. La imagen la construye GitHub Actions y la publica en GHCR

No subas 1.1 GB desde tu PC. El workflow `.github/workflows/docker-build.yml`
construye la imagen en los runners de GitHub y la publica en GHCR al hacer
**push a `main`** (o manualmente desde la pestaña **Actions**):

```
ghcr.io/john33-tech/sabor-gestion:latest
```

> Hacé el paquete **público**: GitHub → tu perfil → Packages → `sabor-gestion`
> → Package settings → Change visibility → Public. Así el cluster lo baja sin
> credenciales. (Si lo dejás privado, necesitás un `imagePullSecret` de tipo
> docker-registry en el namespace.)

### 2. Editar el overlay cloud

- `overlays/cloud/kustomization.yaml` → ya apunta a `ghcr.io/john33-tech/sabor-gestion`.
- `overlays/cloud/configmap-patch.yaml` → tu dominio en `APP_URL`.
- `overlays/cloud/ingress-patch.yaml` → tu dominio en `host` y `tls`.

### 3. Secretos reales (¡no uses los de dev!)

Generá un `APP_KEY` nuevo y creá el Secret directamente en el cluster
(así no queda en git):

```powershell
# APP_KEY nuevo (corré localmente si tenés PHP, o dentro de un pod):
php artisan key:generate --show

kubectl create namespace sabor-gestion

kubectl -n sabor-gestion create secret generic sabor-secret `
  --from-literal=APP_KEY="base64:....." `
  --from-literal=DB_PASSWORD="PASS_FUERTE" `
  --from-literal=MYSQL_PASSWORD="PASS_FUERTE" `
  --from-literal=MYSQL_ROOT_PASSWORD="ROOT_PASS_FUERTE" `
  --from-literal=REVERB_APP_SECRET="SECRETO_REVERB" `
  --from-literal=RESEND_API_KEY="re_xxx"
```

> Como el Secret ya existe, `kubectl apply -k` no lo pisa (Kustomize lo deja
> tal cual si el contenido coincide; si querés que el del overlay no aplique,
> quitá `secret.yaml` de `base/kustomization.yaml` para cloud o usá un overlay
> que no lo incluya).

### 4. Ingress + TLS

- Instalá un Ingress Controller (`ingress-nginx`) y, para HTTPS automático,
  `cert-manager` con un `ClusterIssuer` llamado `letsencrypt-prod`.
- Apuntá el DNS de tu dominio a la IP del LoadBalancer del Ingress:
  ```powershell
  kubectl -n ingress-nginx get svc ingress-nginx-controller   # mirá EXTERNAL-IP
  ```

### 5. Desplegar

```powershell
kubectl apply -k k8s/overlays/cloud
kubectl -n sabor-gestion get pods,ingress
```

Abrí `https://tu-dominio` — los WebSockets viajan por `wss` automáticamente.

---

## Operación útil

```powershell
# Logs del entrypoint (migraciones, Reverb, Apache)
kubectl -n sabor-gestion logs -f deploy/app

# Entrar al contenedor (Tinker, artisan, etc.)
kubectl -n sabor-gestion exec -it deploy/app -- bash
#   php artisan migrate:status
#   php artisan db:seed --force

# Reiniciar la app (re-corre migraciones/caches)
kubectl -n sabor-gestion rollout restart deploy/app

# Ver qué generaría Kustomize sin aplicar
kubectl kustomize k8s/overlays/local

# Borrar todo el entorno
kubectl delete -k k8s/overlays/local
#   (los PVC de datos quedan; borralos a mano si querés empezar limpio)
kubectl -n sabor-gestion delete pvc --all
```

## Notas / decisiones de diseño

- **1 réplica de `app`**: el PVC de storage es `ReadWriteOnce` y las migraciones
  corren en el `entrypoint`. Para escalar horizontalmente harían falta:
  storage `ReadWriteMany` (NFS/EFS/Filestore) o mover uploads a S3, y separar
  las migraciones a un `Job`/`initContainer`.
- **Reverb separado** (mejora futura): se podría mover a su propio Deployment +
  Service para escalar WebSockets aparte; hoy no aporta en un proyecto de 1
  réplica y agregaría sticky sessions en el Ingress.
- **Mail en `log`** por defecto (no envía de verdad). Para mandar facturas
  reales (punto 7.1), poné `MAIL_MAILER=resend` en el ConfigMap y un
  `RESEND_API_KEY` válido en el Secret.
- **Secret de dev commiteado** a propósito: son los mismos valores que ya están
  en `docker-compose.yml`. En la nube se reemplazan por secretos reales (paso 3).
```

> Para la defensa de DevOps: este `k8s/` demuestra Namespace, ConfigMap, Secret,
> StatefulSet con almacenamiento persistente, Deployment con probes y límites de
> recursos, Services, Ingress con WebSockets, y gestión multi-entorno con
> Kustomize (base + overlays). Es el siguiente nivel sobre el deploy en Railway.
