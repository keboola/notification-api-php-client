<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostSubscription;

use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use PHPUnit\Framework\TestCase;

class EmailRecipientTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $emailRecipient = new EmailRecipient('John.Doe@example.com');
        self::assertSame(
            [
                'channel' => 'email',
                'address' => 'John.Doe@example.com',
            ],
            $emailRecipient->jsonSerialize()
        );
    }
}
