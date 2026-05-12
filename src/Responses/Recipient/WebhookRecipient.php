<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses\Recipient;

class WebhookRecipient implements RecipientInterface
{
    public const CHANNEL = 'webhook';

    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getChannel(): string
    {
        return self::CHANNEL;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
