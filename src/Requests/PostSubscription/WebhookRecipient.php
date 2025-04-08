<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostSubscription;

class WebhookRecipient implements RecipientInterface
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function jsonSerialize(): array
    {
        return [
            'channel' => 'webhook',
            'url' => $this->url,
        ];
    }
}
