<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Responses;

use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Responses\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function testAccessors(): void
    {
        $data = [
            'id' => '123',
            'event' => 'some_event',
            'recipient' => [
                'channel' => 'boo',
                'address' => 'foo',
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
        self::assertSame('boo', $subscription->getRecipientChannel());
        self::assertSame('foo', $subscription->getRecipientAddress());
        self::assertSame('some_event', $subscription->getEvent());
        self::assertCount(1, $subscription->getFilters());
        self::assertSame('bar', $subscription->getFilters()[0]->getField());
        self::assertSame('Kochba', $subscription->getFilters()[0]->getValue());
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
