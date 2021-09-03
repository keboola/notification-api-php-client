# Notification API PHP Client [![Build Status](https://dev.azure.com/keboola-dev/notification-api-php-client/_apis/build/status/keboola.notification-api-php-client?branchName=main)](https://dev.azure.com/keboola-dev/notification-api-php-client/_build/latest?definitionId=82&branchName=main)

PHP client for the Notification API ([API docs](https://app.swaggerhub.com/apis/odinuv/notifications-service/1.0.0)).

## Usage
The client uses two kinds of authorizations - Storage API token for Subscription API (`SubscriptionClient` class) and 
Manage API Application token with scope `notifications:push-event` for the Events API (`EventsClient` class).

```bash
composer require keboola/notification-api-php-client
```

```php
use Keboola\NotificationClient\EventsClient;
use Keboola\NotificationClient\Requests\PostEvent\JobFailedEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use Keboola\NotificationClient\Requests\Event;
use Psr\Log\NullLogger;

$client = new EventsClient(
    new NullLogger(),
    'http://notifications.connection.keboola.com/',
    'xxx-xxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
);
$client->postEvent(
    new Event(        
        new JobFailedEventData(
            '123',
            'My Project',
            'Job finished with error',
            new JobData('my-project', '123', 'http://someUrl', '2020-01-02', '2020-01-01', 'my-orchestration')
        )
    )
);
```

or use a factory to create the client

```php
use Keboola\NotificationClient\ClientFactory;

$clientFactory = new ClientFactory('https://connection.keboola.com');
$clientFactory->getEventsClient('xxx-xxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
```

## Development
- Create an Azure service principal to download the required images and login:

    ```bash
        SERVICE_PRINCIPAL_NAME=[USERNAME]-notification-api-pull
        ACR_REGISTRY_ID=$(az acr show --name keboolapes --query id --output tsv --subscription c5182964-8dca-42c8-a77a-fa2a3c6946ea)
        SP_PASSWORD=$(az ad sp create-for-rbac --name http://$SERVICE_PRINCIPAL_NAME --scopes $ACR_REGISTRY_ID --role acrpull --query password --output tsv)
        SP_APP_ID=$(az ad sp show --id http://$SERVICE_PRINCIPAL_NAME --query appId --output tsv)    
    ```

- Login and check that you can pull the image:

    ```bash
        docker login keboolapes.azurecr.io --username $SP_APP_ID --password $SP_PASSWORD
        docker pull keboolapes.azurecr.io/notification-service:latest
    ```

- Add the credentials to the k8s cluster:

    ```bash
        kubectl create secret docker-registry regcred --docker-server="https://keboolapes.azurecr.io" --docker-username="$SP_APP_ID" --docker-password="$SP_PASSWORD" --namespace dev-notification-client
        kubectl patch serviceaccount default -p "{\"imagePullSecrets\":[{\"name\":\"regcred\"}]}" --namespace dev-notification-client
    ```

- Set the following environment variables in `set-env.sh` file (use `set-env.template.sh` as sample):
    - `STORAGE_API_URL` - Keboola Connection URL to arbitrary stack where the notification service is registered.
    - `TEST_STORAGE_API_TOKEN` - Token to a test project. 
    - `TEST_STORAGE_API_PROJECT_ID` - Project ID of the test project.
    - `TEST_MANAGE_API_APPLICATION_TOKEN` - Application token with scope `notifications:push-event`.

- Set one of Azure or AWS resources (or both, but only one is needed).  

### AWS Setup
- Create a user (`NotificationUser`) for local development using the `provisioning/aws.json` CF template. 
    - Create AWS key for the created user. 
    - Set the following environment variables in `set-env.sh` file (use `set-env.template.sh` as sample):
        - `TEST_AWS_ACCESS_KEY` - The created security credentials for the `JobQueueApiPhpClient` user.
        - `TEST_AWS_SECRET_ACCESS_KEY` - The created security credentials for the `JobQueueApiPhpClient` user.
        - `AWS_REGION` - `Region` output of the above stack.
        - `AWS_LOGS_S3_BUCKET` - `S3LogsBucket` output of the above stackk.

### Azure Setup

- Create a resource group:
    ```bash
    az account set --subscription "Keboola DEV PS Team CI"
    az group create --name testing-notification-api-php-client --location "East US"
    ```

- Create a service principal:
    ```bash
    az ad sp create-for-rbac --name testing-notification-api-php-client
    ```

- Use the response to set values `TEST_AZURE_CLIENT_ID`, `TEST_AZURE_CLIENT_SECRET` and `TEST_AZURE_TENANT_ID` in the `set-env.sh` file:
    ```json 
    {
      "appId": "268a6f05-xxxxxxxxxxxxxxxxxxxxxxxxxxx", //-> TEST_AZURE_CLIENT_ID
      "displayName": "testing-notification-api-php-client",
      "name": "http://testing-notification-api-php-client",
      "password": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx", //-> TEST_AZURE_CLIENT_SECRET
      "tenant": "9b85ee6f-xxxxxxxxxxxxxxxxxxxxxxxxxxx" //-> TEST_AZURE_TENANT_ID
    }
    ```

- Get ID of the service principal:
    ```bash
    SERVICE_PRINCIPAL_ID=$(az ad sp list --display-name testing-notification-api-php-client --query "[0].objectId" --output tsv)
    ```
 
- Deploy the Storage Account for logs, provide tenant ID, service principal ID and group ID from the previous commands:
    ```bash
    az deployment group create --resource-group testing-notification-api-php-client --template-file provisioning/azure.json --parameters vault_name=test-notification-client tenant_id=$TEST_AZURE_TENANT_ID service_principal_object_id=$SERVICE_PRINCIPAL_ID
    ```
  
- Get the connection string
    ```bash
    az storage account show-connection-string -g testing-notification-api-php-client -n mirontfcnacc2 --query "connectionString" --output tsv
    ```

Set the connection string and container name you provided as parameter to the create command to following environment variables in the `set-env.sh` file:
 - AZURE_LOGS_ABS_CONTAINER 
 - AZURE_LOGS_ABS_CONNECTION_STRING

## Generate environment configuration

```bash
export DATABASE_URL_BASE64=$(printf "mysql://root:root@dev-mysql-service:3310/notifications?serverVersion=8.0" | base64 --wrap=0)
export DATABASE_PASSWORD_BASE64=$(printf "root" | base64 --wrap=0)
export TEST_AZURE_CLIENT_SECRET_BASE64=$(printf "%s" "$TEST_AZURE_CLIENT_SECRET"| base64 --wrap=0)
export TEST_AWS_SECRET_ACCESS_KEY_BASE64=$(printf "%s" "$TEST_AWS_SECRET_ACCESS_KEY"| base64 --wrap=0)
export AZURE_LOGS_ABS_CONNECTION_STRING_BASE64=$(printf "%s" "$AZURE_LOGS_ABS_CONNECTION_STRING"| base64 --wrap=0)

./set-env.sh
envsubst < provisioning/environments.yaml.template > provisioning/environments.yaml
kubectl apply -f provisioning/environments.yaml
kubectl apply -f provisioning/notification.yaml
TEST_NOTIFICATION_API_IP=`kubectl get svc --output jsonpath --template "{.items[?(@.metadata.name==\"dev-notification-service\")].status.loadBalancer.ingress[].ip}" --namespace=dev-notification-client`

printf "TEST_NOTIFICATION_API_URL: http://%s:8181" "$TEST_NOTIFICATION_API_IP"
```

Store the result `TEST_NOTIFICATION_API_URL` in `set-env.sh`.


## Run tests
- With the above setup, you can run tests:

    ```bash
    docker-compose build
    source ./set-env.sh && docker-compose run tests
    ```

- To run tests with local code use:

    ```bash
    docker-compose run tests-local composer install
    source ./set-env.sh && docker-compose run tests-local
    ```
