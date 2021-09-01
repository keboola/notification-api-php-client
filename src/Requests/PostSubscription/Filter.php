<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostSubscription;

use JsonSerializable;

class Filter implements JsonSerializable
{
    private string $field;
    private string $value;
    private ?FilterOperator $operator;

    public function __construct(string $field, string $value, ?FilterOperator $operator = null)
    {
        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'field' => $this->field,
            'value' => $this->value,
        ];

        if ($this->operator !== null) {
            $data['operator'] = $this->operator->getValue();
        }

        return $data;
    }
}
