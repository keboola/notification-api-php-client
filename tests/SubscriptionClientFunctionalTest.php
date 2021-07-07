<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use Keboola\NotificationClient\Requests\PostSubscription\Filter;
use Keboola\NotificationClient\Requests\Subscription;
use Keboola\NotificationClient\SubscriptionClient;
use PHPUnit\Framework\TestCase;

class SubscriptionClientFunctionalTest extends TestCase
{
    private function getClient(): SubscriptionClient
    {
        return new SubscriptionClient(
            (string) getenv('TEST_NOTIFICATION_API_URL'),
            (string) getenv('TEST_STORAGE_API_TOKEN')
        );
    }

    public function testCreateSubscription(): void
    {
        $client = $this->getClient();
        $response = $client->createSubscription(new Subscription(
            'job_failed',
            new EmailRecipient('johnDoe@example.com'),
            [new Filter('foo', 'bar')]
        ));

        self::assertNotEmpty($response->getId());
        self::assertSame('job_failed', $response->getEvent());
        self::assertSame('foo', $response->getFilters()[0]->getField());
        self::assertSame('bar', $response->getFilters()[0]->getValue());
        self::assertSame('johnDoe@example.com', $response->getRecipientAddress());
        self::assertSame('email', $response->getRecipientChannel());
    }
}
