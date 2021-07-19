<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests;

use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use Keboola\NotificationClient\Requests\PostSubscription\Filter;
use Keboola\NotificationClient\Requests\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $subscriptionRequest = new Subscription(
            'job-failed',
            new EmailRecipient('john.doe@example.com'),
            [new Filter('foo', 'bar')]
        );
        self::assertSame(
            [
                'event' => 'job-failed',
                'filters' => [
                    [
                        'field' => 'foo',
                        'value' => 'bar',
                    ],
                ],
                'recipient' => [
                    'channel' => 'email',
                    'address' => 'john.doe@example.com',
                ],
            ],
            $subscriptionRequest->jsonSerialize()
        );
    }

    public function testJsonSerializeStrangeFilters(): void
    {
        $subscriptionRequest = new Subscription(
            'job-failed',
            new EmailRecipient('john.doe@example.com'),
            ['a' => new Filter('foo', 'bar'), '4filter' => new Filter('bar', 'Kochba')]
        );
        self::assertSame(
            [
                'event' => 'job-failed',
                'filters' => [
                    [
                        'field' => 'foo',
                        'value' => 'bar',
                    ],
                    [
                        'field' => 'bar',
                        'value' => 'Kochba',
                    ],
                ],
                'recipient' => [
                    'channel' => 'email',
                    'address' => 'john.doe@example.com',
                ],
            ],
            $subscriptionRequest->jsonSerialize()
        );
    }
}
