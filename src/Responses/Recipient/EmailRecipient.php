<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses\Recipient;

class EmailRecipient implements RecipientInterface
{
    public const CHANNEL = 'email';

    private string $address;

    public function __construct(string $address)
    {
        $this->address = $address;
    }

    public function getChannel(): string
    {
        return self::CHANNEL;
    }

    public function getAddress(): string
    {
        return $this->address;
    }
}
