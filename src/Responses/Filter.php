<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses;

use Keboola\ApiClientBase\ResponseModelInterface;
use Keboola\NotificationClient\Exception\ClientException;
use Throwable;

final class Filter implements ResponseModelInterface
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

    public static function fromResponseData(array $data): static
    {
        return new self($data);
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
