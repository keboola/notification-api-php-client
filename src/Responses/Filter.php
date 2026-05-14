<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses;

use Keboola\NotificationClient\Exception\ClientException;
use Throwable;

class Filter
{
    private string $field;
    private int|float|bool|string|null $value;
    private string $operator;

    public function __construct(array $data)
    {
        try {
            $this->field = $data['field'];
            $this->value = $data['value'];
            $this->operator = $data['operator'];
            // @phpstan-ignore-next-line PHPStan is confused
        } catch (Throwable $e) {
            throw new ClientException('Unrecognized response: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): int|float|bool|string|null
    {
        return $this->value;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }
}
