#!/usr/bin/env bash
set -Eeuo pipefail

urlencode() {
  local length="${#1}"
  for (( i = 0; i < length; i++ )); do
    local c="${1:i:1}"
    case $c in
      [a-zA-Z0-9.~_-]) printf "$c" ;;
      *) printf '%s' "$c" | xxd -p -c1 |
           while read c; do printf '%%%s' "$c"; done ;;
    esac
  done
}

# strip new lines
DB_PASSWORD_RAW=$(openssl rand -base64 32 | tr -d '\n')
DB_PASSWORD_URLENCODED_RAW=$(urlencode "$DB_PASSWORD_RAW")
# for mysql database, we need the password to be base64 encoded to be stored in k8s secrets
DATABASE_PASSWORD_BASE64=$(printf "%s" "$DB_PASSWORD_RAW" | base64 --wrap=0)
# for notification api, we need to construct the database url
DATABASE_URL_BASE64=$(printf \
  "mysql://root:%s@dev-mysql-service:3310/notifications?serverVersion=8.0" "$DB_PASSWORD_URLENCODED_RAW" \
  | base64 --wrap=0)
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
