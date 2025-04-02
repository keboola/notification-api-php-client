<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostSubscription;

use Keboola\NotificationClient\Requests\PostSubscription\WebhookRecipient;
use PHPUnit\Framework\TestCase;

class WebhookRecipientTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $webhookRecipient = new WebhookRecipient('https://example.com/webhook');
        self::assertSame(
            [
                'channel' => 'webhook',
                'url' => 'https://example.com/webhook',
            ],
            $webhookRecipient->jsonSerialize(),
        );
    }
}
