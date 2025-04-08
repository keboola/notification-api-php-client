<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses;

use Keboola\NotificationClient\Exception\ClientException;

class Notification
{
    private string $id;

    public function __construct(array $data)
    {
        if (!array_key_exists('id', $data)) {
            throw new ClientException('Unrecognized response');
        }

        $this->id = $data['id'];
    }

    public function getId(): string
    {
        return $this->id;
    }
}
