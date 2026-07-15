<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use Closure;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use JsonException;
use Keboola\ApiClientBase\ApiClient;
use Keboola\ApiClientBase\ApiClientOptions;
use Keboola\ApiClientBase\Auth\StorageApiTokenAuthenticator;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\Subscription as RequestSubscription;
use Keboola\NotificationClient\Responses\Subscription as ResponseSubscription;
use Psr\Log\LoggerInterface;

class SubscriptionClient
{
    private const FALLBACK_USER_AGENT = 'Keboola Notification PHP Client';

    private readonly ApiClient $apiClient;

    /**
     * @param non-empty-string $baseUrl
     * @param non-empty-string $storageApiToken
     * @param int<0, max> $backoffMaxTries
     */
    public function __construct(
        string $baseUrl,
        string $storageApiToken,
        ?LoggerInterface $logger = null,
        int $backoffMaxTries = ApiClientOptions::DEFAULT_BACKOFF_MAX_TRIES,
        int $connectTimeout = ApiClientOptions::DEFAULT_CONNECT_TIMEOUT,
        int $requestTimeout = ApiClientOptions::DEFAULT_REQUEST_TIMEOUT,
        string $userAgent = self::FALLBACK_USER_AGENT,
        null|Closure|HandlerStack $requestHandler = null,
    ) {
        $this->apiClient = new ApiClient(
            $baseUrl,
            new StorageApiTokenAuthenticator($storageApiToken),
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

    public function createSubscription(RequestSubscription $requestData): ResponseSubscription
    {
        try {
            $jobDataJson = json_encode($requestData->jsonSerialize(), JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ClientException('Invalid job data: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $this->apiClient->sendRequestAndMapResponse(
            new Request(
                'POST',
                'project-subscriptions',
                ['Content-type' => 'application/json'],
                $jobDataJson,
            ),
            ResponseSubscription::class,
        );
    }

    public function deleteSubscription(string $id): void
    {
        $this->apiClient->sendRequest(
            new Request('DELETE', 'project-subscriptions/' . rawurlencode($id)),
        );
    }

    public function getSubscription(string $id): ResponseSubscription
    {
        return $this->apiClient->sendRequestAndMapResponse(
            new Request('GET', 'project-subscriptions/' . rawurlencode($id)),
            ResponseSubscription::class,
        );
    }

    /**
     * @return list<ResponseSubscription>
     */
    public function listSubscriptions(): array
    {
        return $this->apiClient->sendRequestAndMapResponse(
            new Request('GET', 'project-subscriptions'),
            ResponseSubscription::class,
            [],
            true,
        );
    }
}
