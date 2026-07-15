<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses;

use Keboola\ApiClientBase\ResponseModelInterface;
use Keboola\NotificationClient\Exception\ClientException;

final class Notification implements ResponseModelInterface
{
    private string $id;

    public function __construct(array $data)
    {
        if (!array_key_exists('id', $data)) {
            throw new ClientException('Unrecognized response');
        }

        $this->id = $data['id'];
    }

    public static function fromResponseData(array $data): static
    {
        return new self($data);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
