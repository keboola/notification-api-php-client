<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use GuzzleHttp\Psr7\Request;
use JsonException;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\PostNotification\NotificationInterface;
use Keboola\NotificationClient\Responses\Notification as ResponseNotification;

class NotificationsClient extends Client
{
    protected string $tokenHeaderName = 'X-Kbc-ManageApiToken';

    public function __construct(string $notificationApiUrl, string $applicationToken, array $options)
    {
        if (empty($applicationToken)) {
            throw new ClientException(sprintf(
                'Application token must be non-empty, %s provided.',
                json_encode($applicationToken),
            ));
        }
        parent::__construct($notificationApiUrl, $applicationToken, $options);
    }

    public function postNotification(NotificationInterface $notification): ResponseNotification
    {
        try {
            $notificationJson = json_encode($notification->jsonSerialize(), JSON_THROW_ON_ERROR);
            $request = new Request(
                'POST',
                'notifications',
                ['Content-type' => 'application/json'],
                $notificationJson,
            );
        } catch (JsonException $e) {
            throw new ClientException('Invalid notification data: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return new ResponseNotification($this->sendRequest($request));
    }
}
