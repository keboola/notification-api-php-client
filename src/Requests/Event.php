<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests;

use JsonSerializable;

class Event implements JsonSerializable
{
    private EventDataInterface $eventData;
    private string $eventType;

    public function __construct(EventDataInterface $eventData)
    {
        $this->eventType = $eventData::getEventTypeName();
        $this->eventData = $eventData;
    }

    public function jsonSerialize(): array
    {
        return $this->eventData->jsonSerialize();
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
