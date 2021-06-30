<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostSubscription;

use JsonSerializable;

class EmailRecipient implements JsonSerializable
{
    private string $address;

    public function __construct(string $address)
    {
        $this->address = $address;
    }

    function jsonSerialize()
    {
        return [
            'channel' => 'email',
            'address' => $this->address,
        ];
    }
}
