#!/bin/bash

export DATABASE_URL=mysql://root:root@dev-mysql-service:3310/notifications?serverVersion=8.0
export DATABASE_PASSWORD=root

export AWS_REGION=us-east-1
export MESSENGER_TRANSPORT_DSN="${AWS_SQS_QUEUE_URL}?auto_setup=false"

export TEST_AWS_ACCESS_KEY_ID=
export TEST_AWS_SECRET_ACCESS_KEY=
export TEST_AZURE_CLIENT_ID=
export TEST_AZURE_CLIENT_SECRET=
export TEST_AZURE_TENANT_ID=


export TEST_NOTIFICATION_API_URL=https://localhost:8181/
export TEST_STORAGE_API_TOKEN=
export TEST_MANAGE_API_APPLICATION_TOKEN=
export TEST_STORAGE_API_PROJECT_ID=
export HOSTNAME_SUFFIX=keboola.com
