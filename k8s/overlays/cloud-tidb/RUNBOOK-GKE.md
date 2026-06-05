# Runbook: desplegar en Google Kubernetes Engine (GKE) — paso a paso

Mismo overlay portable que AKS (`k8s/overlays/cloud-tidb`): imagen GHCR + TiDB Cloud
+ LoadBalancer público. Solo cambia cómo se crea el cluster (`gcloud` en vez de `az`).

La imagen ya está pública en GHCR: `ghcr.io/john33-tech/sabor-gestion:e584144`
(GKE la baja sin credenciales).

---

## 0) Antes de empezar (en el navegador, una sola vez)

1. Entra a https://console.cloud.google.com con tu cuenta Google.
2. Activa la **prueba gratuita ($300, 90 días)** — pide tarjeta para verificar, **no cobra**.
3. Crea un **proyecto** (ej. `sabor-gke`). Anota su **Project ID**.
4. Abre **Cloud Shell**: el botón `>_` arriba a la derecha. Ya trae `gcloud` y `kubectl`.

Todo lo de abajo se ejecuta en **Cloud Shell**.

## 1) Variables

```bash
PROJECT=PON-AQUI-TU-PROJECT-ID      # el Project ID del paso 0
CLUSTER=sabor-gke
ZONE=us-central1-a
```

## 2) Seleccionar proyecto + habilitar APIs

```bash
gcloud config set project $PROJECT
gcloud services enable container.googleapis.com compute.googleapis.com
```

## 3) Crear el cluster (~5 min)

```bash
gcloud container clusters create $CLUSTER \
  --zone $ZONE \
  --num-nodes 1 \
  --machine-type e2-medium \
  --disk-size 30
```

## 4) Conectar kubectl

```bash
gcloud container clusters get-credentials $CLUSTER --zone $ZONE
kubectl get nodes        # 1 nodo Ready
```

## 5) Traer el repo

```bash
git clone https://github.com/john33-tech/sabor-y-gestion.git
cd sabor-y-gestion
```

## 6) Namespace + Secret real (el password de TiDB NO va en git)

```bash
kubectl create namespace sabor-gestion

kubectl -n sabor-gestion create secret generic sabor-secret \
  --from-literal=APP_KEY="base64:CBuXycAxuiTTs7NUbBZ7kSykrKsY1iqSfRrb6NJ2OKc=" \
  --from-literal=DB_PASSWORD="sMprjskKLQv15lZl" \
  --from-literal=REVERB_APP_SECRET="saborsecret" \
  --from-literal=RESEND_API_KEY=""
```

## 7) Desplegar

```bash
kubectl apply -k k8s/overlays/cloud-tidb
```

## 8) Obtener la IP pública del LoadBalancer (1-3 min)

```bash
kubectl -n sabor-gestion get svc app -w
# Ctrl+C cuando EXTERNAL-IP deje de ser <pending>
```

## 9) Poner esa IP en APP_URL y reaplicar

```bash
IP=$(kubectl -n sabor-gestion get svc app -o jsonpath='{.status.loadBalancer.ingress[0].ip}')
echo "IP pública: $IP"
sed -i "s|http://CAMBIAR-POR-IP-PUBLICA|http://$IP|" k8s/overlays/cloud-tidb/configmap-patch.yaml
kubectl apply -k k8s/overlays/cloud-tidb
kubectl -n sabor-gestion rollout restart deploy/app
kubectl -n sabor-gestion rollout status deploy/app --timeout=300s
```

## 10) ¡Listo! — accesible desde cualquier máquina

```bash
echo "http://$IP"
```

Login: `admin@saborgestion.com` / `password` (mismos datos del grupo: TiDB Cloud compartido).

---

## Problemas comunes

```bash
kubectl -n sabor-gestion get pods,svc
kubectl -n sabor-gestion logs deploy/app --tail=50

# Pod 0/1 un rato: normal (1ª carga abre SSL a TiDB; las probes dan 15s de margen).
# "Access denied" en logs: DB_PASSWORD mal -> recrear Secret (paso 6).
# "ImagePullBackOff": la imagen GHCR debe estar PÚBLICA
#   (GitHub > Packages > sabor-gestion > Package settings > Public).
```

## Apagar para no gastar crédito (al terminar)

```bash
gcloud container clusters delete $CLUSTER --zone $ZONE --quiet   # borra el cluster
# El LoadBalancer y el disco se borran con él. La BD (TiDB) y la imagen (GHCR) no se tocan.
```

> **Seguridad:** rotar el `DB_PASSWORD` de TiDB tras la entrega (viajó por chats).
> Tiempo real (cocina en vivo): si quieres que funcione, agrega `PUSHER_APP_SECRET`
> al Secret con la app secret real de Pusher; sin eso la app corre igual pero no emite eventos.
