<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostSubscription;

use JsonSerializable;

class Filter implements JsonSerializable
{
    private string $name;
    private string $value;

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
