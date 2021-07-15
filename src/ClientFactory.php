<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

class ClientFactory
{
    const NOTIFICATION_SERVICE_NAME = 'notification';
    private string $notificationUrl;

    public function __construct(string $connectionUrl, array $connectionClientOptions = [])
    {
        $storageApiIndexClient = new StorageApiIndexClient($connectionUrl, $connectionClientOptions);
        $this->notificationUrl = $storageApiIndexClient->getServiceUrl(self::NOTIFICATION_SERVICE_NAME);
    }

    public function getEventsClient(string $applicationToken, array $options = []): EventsClient
    {
        return new EventsClient($this->notificationUrl, $applicationToken, $options);
    }

    public function getSubscriptionClient(string $storageApiToken, array $options = []): SubscriptionClient
    {
        return new SubscriptionClient($this->notificationUrl, $storageApiToken, $options);
    }
}
