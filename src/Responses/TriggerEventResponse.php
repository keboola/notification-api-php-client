<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses;

class TriggerEventResponse
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
