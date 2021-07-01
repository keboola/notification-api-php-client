#!/usr/bin/env bash
set -Eeuo pipefail
source ./provisioning/functions.sh

# strip new lines
DB_PASSWORD_RAW=$(openssl rand -base64 32 | tr -d '\n')
DB_PASSWORD_URLENCODED_RAW=$(urlencode "$DB_PASSWORD_RAW")
# for mysql database, we need the password to be base64 encoded to be stored in k8s secrets
DATABASE_PASSWORD_BASE64=$(printf "%s" "$DB_PASSWORD_RAW" | base64 --wrap=0)
# for notification api, we need to construct the database url
DATABASE_URL_BASE64=$(printf "mysql://root:%s@dev-mysql-service:3310/notifications?serverVersion=8.0" "$DB_PASSWORD_RAW" | base64 --wrap=0)
TEST_AWS_SECRET_ACCESS_KEY_BASE64=$(printf "%s" "$TEST_AWS_SECRET_ACCESS_KEY" | base64 --wrap=0)

export DATABASE_URL_BASE64
export DATABASE_PASSWORD_BASE64
export TEST_AWS_SECRET_ACCESS_KEY_BASE64

envsubst < ./provisioning/environments.yaml.template > ./provisioning/environments.yaml
kubectl apply -f ./provisioning/environments.yaml
kubectl apply -f ./provisioning/notification.yaml

kubectl rollout status deployment/dev-notification-api --namespace="$KUBERNETES_NAMESPACE" --timeout=900s

# wait for ingress to get ip
sleep 10

NOTIFICATION_API_IP=$(kubectl get svc \
  --output jsonpath \
  --template "{.items[?(@.metadata.name==\"dev-notification-service\")].status.loadBalancer.ingress[].ip}" \
  --namespace="$KUBERNETES_NAMESPACE")

printf "API IP:%s" "$NOTIFICATION_API_IP"
