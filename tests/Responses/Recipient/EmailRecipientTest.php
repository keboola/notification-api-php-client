<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Responses\Recipient;

use Keboola\NotificationClient\Responses\Recipient\EmailRecipient;
use Keboola\NotificationClient\Responses\Recipient\RecipientInterface;
use PHPUnit\Framework\TestCase;

class EmailRecipientTest extends TestCase
{
    public function testAccessors(): void
    {
        $recipient = new EmailRecipient('john.doe@example.com');
        self::assertInstanceOf(RecipientInterface::class, $recipient);
        self::assertSame('email', $recipient->getChannel());
        self::assertSame('john.doe@example.com', $recipient->getAddress());
    }
}
