<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests;

use JsonSerializable;

class Event implements JsonSerializable
{
    private JsonSerializable $eventData;
    private string $eventType;

    public function __construct(EventDataInterface $eventData)
    {
        $this->eventType = $eventData::getEventTypeName();
        $this->eventData = $eventData;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->eventData->jsonSerialize();
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
