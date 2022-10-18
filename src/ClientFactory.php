<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;

class ClientFactory
{
    private const NOTIFICATION_SERVICE_NAME = 'notification';
    private ?string $notificationUrl;
    private string $connectionUrl;
    /**
     * @var array{
     *     handler?: HandlerStack,
     *     backoffMaxTries: int<0, 100>,
     *     userAgent: string,
     *     logger?: LoggerInterface
     * }
     */
    private array $connectionClientOptions;

    /**
     * @param array{
     *     handler?: HandlerStack,
     *     backoffMaxTries: int<0, 100>,
     *     userAgent: string,
     *     logger?: LoggerInterface
     * } $connectionClientOptions
     */
    public function __construct(string $connectionUrl, array $connectionClientOptions)
    {
        $this->connectionUrl = $connectionUrl;
        $this->connectionClientOptions = $connectionClientOptions;
        $this->notificationUrl = null;
    }

    private function getNotificationUrl(): string
    {
        if ($this->notificationUrl === null) {
            $storageApiIndexClient = new StorageApiIndexClient($this->connectionUrl, $this->connectionClientOptions);
            $this->notificationUrl = $storageApiIndexClient->getServiceUrl(self::NOTIFICATION_SERVICE_NAME);
        }
        return (string) $this->notificationUrl;
    }

    /**
     * @param array{
     *     handler?: HandlerStack,
     *     backoffMaxTries: int<0, 100>,
     *     userAgent: string,
     *     logger?: LoggerInterface
     * } $options
     */
    public function getEventsClient(string $applicationToken, array $options): EventsClient
    {
        return new EventsClient($this->getNotificationUrl(), $applicationToken, $options);
    }

    /**
     * @param array{
     *     handler?: HandlerStack,
     *     backoffMaxTries: int<0, 100>,
     *     userAgent: string,
     *     logger?: LoggerInterface
     * } $options
     */
    public function getSubscriptionClient(string $storageApiToken, array $options): SubscriptionClient
    {
        return new SubscriptionClient($this->getNotificationUrl(), $storageApiToken, $options);
    }
}
