<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use GuzzleHttp\Psr7\Request;
use JsonException;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\PostEventRequest;
use Psr\Log\LoggerInterface;

class EventsClient extends Client
{
    protected function getTokenHeaderName(): string
    {
        return 'X-Kbc-ManageApiToken';
    }

    public function __construct(
        string $notificationApiUrl,
        string $applicationToken,
        array $options = []
    ) {
        parent::__construct($notificationApiUrl, $applicationToken, $options);
    }

    public function postEvent(PostEventRequest $requestData): void
    {
        try {
            $jobDataJson = json_encode($requestData->jsonSerialize(), JSON_THROW_ON_ERROR);
            $request = new Request('POST', sprintf('events/%s', $requestData->getEventType()), [], $jobDataJson);
        } catch (JsonException $e) {
            throw new ClientException('Invalid job data: ' . $e->getMessage(), $e->getCode(), $e);
        }
        $this->sendRequest($request);
    }
}
