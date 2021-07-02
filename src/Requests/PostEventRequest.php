<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests;

use JsonSerializable;

class PostEventRequest implements JsonSerializable
{
    private JsonSerializable $eventData;
    private string $eventType;

    public function __construct(string $eventType, JsonSerializable $eventData)
    {
        $this->eventType = $eventType;
        $this->eventData = $eventData;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        //$data['name'] = $this->eventType;
        $data['data'] = $this->eventData->jsonSerialize();
        return $data;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
