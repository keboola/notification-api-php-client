<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostSubscription;

class EmailRecipient implements RecipientInterface
{
    private string $address;

    public function __construct(string $address)
    {
        $this->address = $address;
    }

    public function jsonSerialize(): array
    {
        return [
            'channel' => 'email',
            'address' => $this->address,
        ];
    }
}
