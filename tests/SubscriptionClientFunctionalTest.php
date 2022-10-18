<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use Keboola\NotificationClient\Exception\ClientException;
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
            (string) getenv('TEST_STORAGE_API_TOKEN'),
            [
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
            ]
        );
    }

    public function testCreateSubscription(): void
    {
        $client = $this->getClient();
        $response = $client->createSubscription(new Subscription(
            'job-failed',
            new EmailRecipient('johnDoe@example.com'),
            [
                new Filter('project.id', (string) getenv('TEST_STORAGE_API_PROJECT_ID')),
            ]
        ));

        self::assertNotEmpty($response->getId());
        self::assertSame('job-failed', $response->getEvent());
        self::assertSame('project.id', $response->getFilters()[0]->getField());
        self::assertSame((string) getenv('TEST_STORAGE_API_PROJECT_ID'), $response->getFilters()[0]->getValue());
        self::assertSame('johnDoe@example.com', $response->getRecipientAddress());
        self::assertSame('email', $response->getRecipientChannel());
    }

    public function testCreateInvalidSubscription(): void
    {
        $client = $this->getClient();
        self::expectException(ClientException::class);
        self::expectExceptionMessage(
            'Invalid event type "dummy-event", valid types are: ' .
            '"job-failed, job-succeeded, job-succeeded-with-warning, job-processing-long, phase-job-failed, ' .
            'phase-job-succeeded, phase-job-succeeded-with-warning, phase-job-processing-long".'
        );
        $client->createSubscription(new Subscription(
            'dummy-event',
            new EmailRecipient('johnDoe@example.com'),
            [new Filter('projectId', (string) getenv('TEST_STORAGE_API_PROJECT_ID'))]
        ));
    }
}
