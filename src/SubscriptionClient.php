<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use GuzzleHttp\Psr7\Request;
use JsonException;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\Subscription as RequestSubscription;
use Keboola\NotificationClient\Responses\Subscription as ResponseSubscription;

class SubscriptionClient extends Client
{
    protected string $tokenHeaderName = 'X-StorageApi-Token';

    /** @inheritDoc */
    public function __construct(string $notificationApiUrl, string $storageApiToken, array $options)
    {
        if (empty($storageApiToken)) {
            throw new ClientException(sprintf(
                'Storage API token must be non-empty, %s provided.',
                json_encode($storageApiToken)
            ));
        }
        parent::__construct($notificationApiUrl, $storageApiToken, $options);
    }

    public function createSubscription(RequestSubscription $requestData): ResponseSubscription
    {
        try {
            $jobDataJson = json_encode($requestData->jsonSerialize(), JSON_THROW_ON_ERROR);
            $request = new Request(
                'POST',
                'project-subscriptions',
                ['Content-type' => 'application/json'],
                $jobDataJson
            );
        } catch (JsonException $e) {
            throw new ClientException('Invalid job data: ' . $e->getMessage(), $e->getCode(), $e);
        }
        return new ResponseSubscription($this->sendRequest($request));
    }
}
