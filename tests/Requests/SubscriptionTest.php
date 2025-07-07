<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests;

use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use Keboola\NotificationClient\Requests\PostSubscription\Filter;
use Keboola\NotificationClient\Requests\PostSubscription\RecipientInterface;
use Keboola\NotificationClient\Requests\PostSubscription\WebhookRecipient;
use Keboola\NotificationClient\Requests\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    public function recipientProvider(): iterable
    {
        yield 'email' => [
            new EmailRecipient('john.doe@example.com'),
            [
                'channel' => 'email',
                'address' => 'john.doe@example.com',
            ],
        ];

        yield 'webhook' => [
            new WebhookRecipient('https://example.com/webhook'),
            [
                'channel' => 'webhook',
                'url' => 'https://example.com/webhook',
            ],
        ];
    }


    /** @dataProvider recipientProvider */
    public function testJsonSerialize(RecipientInterface $recipient, array $expectedRecipient): void
    {
        $subscriptionRequest = new Subscription(
            'job-failed',
            $recipient,
            [new Filter('foo', 'bar')],
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
                'recipient' => $expectedRecipient,
            ],
            $subscriptionRequest->jsonSerialize(),
        );
    }

    /** @dataProvider recipientProvider */
    public function testJsonSerializeStrangeFilters(RecipientInterface $recipient, array $expectedRecipient): void
    {
        $subscriptionRequest = new Subscription(
            'job-failed',
            $recipient,
            ['a' => new Filter('foo', 'bar'), '4filter' => new Filter('bar', 'Kochba')],
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
                'recipient' => $expectedRecipient,
            ],
            $subscriptionRequest->jsonSerialize(),
        );
    }

    /** @dataProvider recipientProvider */
    public function testPhaseJobFailedSubscription(RecipientInterface $recipient, array $expectedRecipient): void
    {
        $subscriptionRequest = new Subscription(
            'phase-job-failed',
            $recipient,
            [
                new Filter('job.component.id', 'my.component'),
                new Filter('job.configuration.id', '12345'),
                new Filter('phase.id', '123'),
            ],
        );
        self::assertSame(
            [
                'event' => 'phase-job-failed',
                'filters' => [
                    [
                        'field' => 'job.component.id',
                        'value' => 'my.component',
                    ],
                    [
                        'field' => 'job.configuration.id',
                        'value' => '12345',
                    ],
                    [
                        'field' => 'phase.id',
                        'value' => '123',
                    ],
                ],
                'recipient' => $expectedRecipient,
            ],
            $subscriptionRequest->jsonSerialize(),
        );
    }

    /** @dataProvider recipientProvider */
    public function testPhaseJobSucceededWithWarningSubscription(
        RecipientInterface $recipient,
        array $expectedRecipient,
    ): void {
        $subscriptionRequest = new Subscription(
            'phase-job-succeeded-with-warning',
            $recipient,
            [
                new Filter('job.component.id', 'my.component'),
                new Filter('job.configuration.id', '12345'),
                new Filter('phase.id', '123'),
            ],
        );
        self::assertSame(
            [
                'event' => 'phase-job-succeeded-with-warning',
                'filters' => [
                    [
                        'field' => 'job.component.id',
                        'value' => 'my.component',
                    ],
                    [
                        'field' => 'job.configuration.id',
                        'value' => '12345',
                    ],
                    [
                        'field' => 'phase.id',
                        'value' => '123',
                    ],
                ],
                'recipient' => $expectedRecipient,
            ],
            $subscriptionRequest->jsonSerialize(),
        );
    }

    /** @dataProvider recipientProvider */
    public function testJobSucceededSubscription(RecipientInterface $recipient, array $expectedRecipient): void
    {
        $subscriptionRequest = new Subscription(
            'job-succeeded',
            $recipient,
            [
                new Filter('job.component.id', 'my.component'),
                new Filter('job.configuration.id', '12345'),
            ],
        );

        self::assertSame(
            [
                'event' => 'job-succeeded',
                'filters' => [
                    [
                        'field' => 'job.component.id',
                        'value' => 'my.component',
                    ],
                    [
                        'field' => 'job.configuration.id',
                        'value' => '12345',
                    ],
                ],
                'recipient' => $expectedRecipient,
            ],
            $subscriptionRequest->jsonSerialize(),
        );
    }

    /** @dataProvider recipientProvider */
    public function testPhaseJobSucceededSubscription(RecipientInterface $recipient, array $expectedRecipient): void
    {
        $subscriptionRequest = new Subscription(
            'phase-job-succeeded',
            $recipient,
            [
                new Filter('job.component.id', 'my.component'),
                new Filter('job.configuration.id', '12345'),
                new Filter('phase.id', '123'),
            ],
        );

        self::assertSame(
            [
                'event' => 'phase-job-succeeded',
                'filters' => [
                    [
                        'field' => 'job.component.id',
                        'value' => 'my.component',
                    ],
                    [
                        'field' => 'job.configuration.id',
                        'value' => '12345',
                    ],
                    [
                        'field' => 'phase.id',
                        'value' => '123',
                    ],
                ],
                'recipient' => $expectedRecipient,
            ],
            $subscriptionRequest->jsonSerialize(),
        );
    }

    /** @dataProvider recipientProvider */
    public function testPhaseJobProcessingLongSubscription(
        RecipientInterface $recipient,
        array $expectedRecipient,
    ): void {
        $subscriptionRequest = new Subscription(
            'phase-job-processing-long',
            $recipient,
            [
                new Filter('job.component.id', 'my.component'),
                new Filter('job.configuration.id', '12345'),
                new Filter('phase.id', '123'),
            ],
        );
        self::assertSame(
            [
                'event' => 'phase-job-processing-long',
                'filters' => [
                    [
                        'field' => 'job.component.id',
                        'value' => 'my.component',
                    ],
                    [
                        'field' => 'job.configuration.id',
                        'value' => '12345',
                    ],
                    [
                        'field' => 'phase.id',
                        'value' => '123',
                    ],
                ],
                'recipient' => $expectedRecipient,
            ],
            $subscriptionRequest->jsonSerialize(),
        );
    }
}
