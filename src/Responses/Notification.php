<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses;

use Keboola\NotificationClient\Exception\ClientException;
use Throwable;

class Notification
{
    private string $eventId;

    public function __construct(array $data)
    {
        try {
            $this->eventId = $data['eventId'];
        } catch (Throwable $e) {
            throw new ClientException('Unrecognized response: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }
}
