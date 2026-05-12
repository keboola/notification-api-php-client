<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Responses;

use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Responses\Recipient\EmailRecipient;
use Keboola\NotificationClient\Responses\Recipient\WebhookRecipient;
use Keboola\NotificationClient\Responses\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function testEmailRecipientAccessors(): void
    {
        $data = [
            'id' => '123',
            'event' => 'some_event',
            'recipient' => [
                'channel' => 'email',
                'address' => 'john.doe@example.com',
            ],
            'filters' => [
                2 => [
                    'field' => 'bar',
                    'value' => 'Kochba',
                ],
            ],
        ];
        $subscription = new Subscription($data);
        self::assertSame('123', $subscription->getId());
        self::assertSame('some_event', $subscription->getEvent());
        self::assertCount(1, $subscription->getFilters());
        self::assertSame('bar', $subscription->getFilters()[0]->getField());
        self::assertSame('Kochba', $subscription->getFilters()[0]->getValue());

        $recipient = $subscription->getRecipient();
        self::assertInstanceOf(EmailRecipient::class, $recipient);
        self::assertSame('email', $recipient->getChannel());
        self::assertSame('john.doe@example.com', $recipient->getAddress());
    }

    public function testWebhookRecipientAccessors(): void
    {
        $data = [
            'id' => '29746',
            'event' => 'job-succeeded',
            'recipient' => [
                'channel' => 'webhook',
                'url' => 'https://asd.as',
            ],
            'filters' => [],
        ];
        $subscription = new Subscription($data);
        self::assertSame('29746', $subscription->getId());
        self::assertSame('job-succeeded', $subscription->getEvent());
        self::assertSame([], $subscription->getFilters());

        $recipient = $subscription->getRecipient();
        self::assertInstanceOf(WebhookRecipient::class, $recipient);
        self::assertSame('webhook', $recipient->getChannel());
        self::assertSame('https://asd.as', $recipient->getUrl());
    }

    public function testUnknownChannelThrows(): void
    {
        $data = [
            'id' => '1',
            'event' => 'some_event',
            'recipient' => [
                'channel' => 'boo',
                'address' => 'foo',
            ],
            'filters' => [],
        ];
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageMatches('#Unrecognized response:.*?boo#');
        $this->expectExceptionCode(0);
        new Subscription($data);
    }

    public function testInvalidData(): void
    {
        $data = [
            'id' => 123,
            'event' => 'some_event',
        ];
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageMatches(
            '#Unrecognized response:.*?(\$id must be string, int used|\$id of type string)#',
        );
        $this->expectExceptionCode(0);
        new Subscription($data);
    }
}
