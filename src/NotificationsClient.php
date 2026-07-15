<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use Closure;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Keboola\ApiClientBase\ApiClient;
use Keboola\ApiClientBase\ApiClientOptions;
use Keboola\ApiClientBase\Auth\KeboolaServiceAccountAuthenticator;
use Keboola\ApiClientBase\Auth\ManageApiTokenAuthenticator;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\PostNotification\NotificationInterface;
use Keboola\NotificationClient\Responses\Notification as ResponseNotification;
use Psr\Log\LoggerInterface;

class NotificationsClient
{
    private const FALLBACK_USER_AGENT = 'Keboola Notification PHP Client';

    private readonly ApiClient $apiClient;

    /**
     * @param non-empty-string $baseUrl
     * @param non-empty-string|null $applicationToken When null, authenticates via the projected
     *     Kubernetes ServiceAccount token — see {@see KeboolaServiceAccountAuthenticator}.
     * @param int<0, max> $backoffMaxTries
     */
    public function __construct(
        string $baseUrl,
        ?string $applicationToken = null,
        ?LoggerInterface $logger = null,
        int $backoffMaxTries = ApiClientOptions::DEFAULT_BACKOFF_MAX_TRIES,
        int $connectTimeout = ApiClientOptions::DEFAULT_CONNECT_TIMEOUT,
        int $requestTimeout = ApiClientOptions::DEFAULT_REQUEST_TIMEOUT,
        string $userAgent = self::FALLBACK_USER_AGENT,
        null|Closure|HandlerStack $requestHandler = null,
    ) {
        $authenticator = $applicationToken !== null
            ? new ManageApiTokenAuthenticator($applicationToken)
            : new KeboolaServiceAccountAuthenticator();

        $this->apiClient = new ApiClient(
            $baseUrl,
            $authenticator,
            new ApiClientOptions(
                userAgent: $userAgent,
                backoffMaxTries: $backoffMaxTries,
                connectTimeout: $connectTimeout,
                requestTimeout: $requestTimeout,
                requestHandler: $requestHandler,
                logger: $logger,
            ),
            exceptionClass: ClientException::class,
        );
    }

    public function postNotification(NotificationInterface $notification): ResponseNotification
    {
        try {
            $notificationJson = json_encode($notification->jsonSerialize(), JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ClientException('Invalid notification data: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $this->apiClient->sendRequestAndMapResponse(
            new Request(
                'POST',
                'notifications',
                ['Content-type' => 'application/json'],
                $notificationJson,
            ),
            ResponseNotification::class,
        );
    }
}
