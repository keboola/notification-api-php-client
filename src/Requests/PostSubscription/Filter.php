<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostSubscription;

use JsonSerializable;

class Filter implements JsonSerializable
{
    private string $field;
    private string $value;

    public function __construct(string $field, string $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'field' => $this->field,
            'value' => $this->value,
        ];
    }
}
