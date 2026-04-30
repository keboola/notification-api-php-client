<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Responses\Recipient;

use Keboola\NotificationClient\Responses\Recipient\RecipientInterface;
use Keboola\NotificationClient\Responses\Recipient\WebhookRecipient;
use PHPUnit\Framework\TestCase;

class WebhookRecipientTest extends TestCase
{
    public function testAccessors(): void
    {
        $recipient = new WebhookRecipient('https://example.com/webhook');
        self::assertInstanceOf(RecipientInterface::class, $recipient);
        self::assertSame('webhook', $recipient->getChannel());
        self::assertSame('https://example.com/webhook', $recipient->getUrl());
    }
}
