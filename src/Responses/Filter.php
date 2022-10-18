<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses;

use Keboola\NotificationClient\Exception\ClientException;
use Throwable;

class Filter
{
    private string $field;
    private string $value;

    public function __construct(array $data)
    {
        try {
            $this->field = $data['field'];
            $this->value = $data['value'];
            // @phpstan-ignore-next-line PHPStan is confused
        } catch (Throwable $e) {
            throw new ClientException('Unrecognized response: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
