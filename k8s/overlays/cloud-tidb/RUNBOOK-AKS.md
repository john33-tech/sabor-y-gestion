# Runbook: desplegar en Azure AKS (paso a paso)

Ejecuta TODO esto en **Azure Cloud Shell** (botón `>_` arriba en https://portal.azure.com,
elige **Bash**). Ya trae `az` y `kubectl`, no instalas nada.

La imagen ya está publicada y pública en GHCR:
`ghcr.io/john33-tech/sabor-gestion:latest` — AKS la baja sin credenciales.

---

## 1) Variables

```bash
RG=sabor-rg
AKS=sabor-aks
LOC=eastus
```

## 2) Crear grupo de recursos + cluster AKS (~5-10 min)

```bash
az group create -n $RG -l $LOC

az aks create -g $RG -n $AKS \
  --node-count 1 \
  --node-vm-size Standard_B2s \
  --generate-ssh-keys \
  --no-wait
```

> Si `Standard_B2s` da error de cuota/SKU, prueba `Standard_B2als_v2`,
> `Standard_DS2_v2` o `Standard_DC2s_v3` (en Azure for Students a veces solo se
> permiten ciertos tamaños).

```bash
az aks wait -g $RG -n $AKS --created --interval 30 --timeout 1200
```

## 3) Conectar kubectl

```bash
az aks get-credentials -g $RG -n $AKS --overwrite-existing
kubectl get nodes        # 1 nodo Ready
```

## 4) Traer el repo

```bash
git clone https://github.com/john33-tech/sabor-y-gestion.git
cd sabor-y-gestion
```

## 5) Namespace + Secret real (password de TiDB NO va en git)

```bash
kubectl create namespace sabor-gestion

kubectl -n sabor-gestion create secret generic sabor-secret \
  --from-literal=APP_KEY="base64:CBuXycAxuiTTs7NUbBZ7kSykrKsY1iqSfRrb6NJ2OKc=" \
  --from-literal=DB_PASSWORD="sMprjskKLQv15lZl" \
  --from-literal=REVERB_APP_SECRET="saborsecret" \
  --from-literal=RESEND_API_KEY=""
```

## 6) Desplegar

```bash
kubectl apply -k k8s/overlays/cloud-tidb
```

## 7) Obtener la IP pública (1-3 min)

```bash
kubectl -n sabor-gestion get svc app -w
# Ctrl+C cuando EXTERNAL-IP deje de ser <pending>
```

## 8) Poner esa IP en APP_URL y reaplicar

```bash
IP=$(kubectl -n sabor-gestion get svc app -o jsonpath='{.status.loadBalancer.ingress[0].ip}')
echo "IP pública: $IP"
sed -i "s|http://CAMBIAR-POR-IP-PUBLICA|http://$IP|" k8s/overlays/cloud-tidb/configmap-patch.yaml
kubectl apply -k k8s/overlays/cloud-tidb
kubectl -n sabor-gestion rollout restart deploy/app
kubectl -n sabor-gestion rollout status deploy/app --timeout=300s
```

## 9) ¡Listo!

```bash
echo "http://$IP"
```

Login: `admin@saborgestion.com`, `cajero@saborgestion.com`, etc.
Muestra los MISMOS datos que el grupo (TiDB Cloud compartido).

---

## Problemas comunes

```bash
kubectl -n sabor-gestion get pods,svc
kubectl -n sabor-gestion logs deploy/app --tail=50

# Pod 0/1 un rato: normal (1ª carga abre SSL a TiDB; probe 15s).
# "Access denied" en logs: DB_PASSWORD mal -> recrear Secret (paso 5).
# "ImagePullBackOff": imagen GHCR no pública -> GitHub > Packages >
#   sabor-gestion > Package settings > Change visibility > Public.

kubectl -n sabor-gestion exec -it deploy/app -- bash
```

## Apagar para no gastar crédito (al terminar)

```bash
az aks stop -g $RG -n $AKS        # parar (reiniciable)
# o
az group delete -n $RG --yes --no-wait   # borrar todo
```

> **Seguridad:** la password de TiDB viajó por WhatsApp; el equipo debe rotarla
> tras la entrega. El `APP_KEY` de este runbook es nuevo (no es el de tu .env).
