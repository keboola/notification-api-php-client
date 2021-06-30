<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use GuzzleHttp\Psr7\Request;
use JsonException;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\PostSubscriptionRequest;
use Psr\Log\LoggerInterface;

class SubscriptionClient extends Client
{
    protected function getTokenHeaderName(): string
    {
        return 'X-StorageApi-Token';
    }

    public function __construct(
        LoggerInterface $logger,
        string $notificationApiUrl,
        string $storageApiToken,
        array $options = []
    ) {
        parent::__construct($logger, $notificationApiUrl, $storageApiToken, $options);
    }

    public function createSubscription(PostSubscriptionRequest $requestData): array
    {
        try {
            $jobDataJson = json_encode($requestData->jsonSerialize(), JSON_THROW_ON_ERROR);
            $request = new Request('POST', 'subscriptions', [], $jobDataJson);
        } catch (JsonException $e) {
            throw new ClientException('Invalid job data: ' . $e->getMessage(), $e->getCode(), $e);
        }
        return $this->sendRequest($request);
    }
}
