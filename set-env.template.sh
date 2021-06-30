#!/bin/bash

export STORAGE_API_URL=https://connection.keboola.com/
export DATABASE_URL=mysql://root:root@dev-mysql-service:3310/notifications?serverVersion=8.0
export DATABASE_PASSWORD=root

export AWS_LOGS_S3_BUCKET=odin-dev-notification-notifications3logsbucket-1tzt3u3zjsrw2
export AWS_REGION=us-east-1
export AZURE_LOGS_ABS_CONTAINER=
export AZURE_LOGS_ABS_CONNECTION_STRING=

export TEST_AWS_ACCESS_KEY_ID=AKIAQLZBTO5VNIW2XCWW
export TEST_AWS_SECRET_ACCESS_KEY=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
export TEST_AZURE_CLIENT_ID=
export TEST_AZURE_CLIENT_SECRET=
export TEST_AZURE_TENANT_ID=


export TEST_NOTIFICATION_API_URL=https://localhost:8181/
export TEST_STORAGE_API_TOKEN=
export TEST_MANAGE_API_APPLICATION_TOKEN=
