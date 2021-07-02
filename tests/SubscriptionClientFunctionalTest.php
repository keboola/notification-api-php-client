<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use Keboola\NotificationClient\Requests\PostSubscriptionRequest;
use Keboola\NotificationClient\SubscriptionClient;
use Psr\Log\NullLogger;

class SubscriptionClientFunctionalTest extends BaseTest
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
        $response = $client->createSubscription(new PostSubscriptionRequest(
            'job_failed',
            new EmailRecipient('johnDoe@example.com'),
            []
        ));

        self::assertNotEmpty($response['id']);
        self::assertSame('job_failed', $response['event']);
        self::assertSame([], $response['filters']);
        self::assertSame('johnDoe@example.com', $response['recipient']['address']);
        self::assertSame('email', $response['recipient']['channel']);
    }
}
