<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use DateTimeImmutable;
use Keboola\NotificationClient\EventsClient;
use Keboola\NotificationClient\Requests\Event;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use Keboola\NotificationClient\Requests\PostEvent\JobFailedEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobProcessingLongEventData;
use PHPUnit\Framework\TestCase;

class EventsClientFunctionalTest extends TestCase
{
    private function getClient(): EventsClient
    {
        return new EventsClient(
            (string) getenv('TEST_NOTIFICATION_API_URL'),
            (string) getenv('TEST_MANAGE_API_APPLICATION_TOKEN')
        );
    }

    public function testPostEvent(): void
    {
        $client = $this->getClient();
        $client->postEvent(new Event(
            new JobFailedEventData(
                '1234',
                'My project',
                'job failed',
                new JobData(
                    '23456',
                    'http://someUrl',
                    new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
                    new DateTimeImmutable('2020-01-01T12:11:00+00:00'),
                    'keboola.orchestrator',
                    'Orchestrator',
                    'my-configuration',
                    'My configuration'
                )
            )
        ));
        self::assertTrue(true);
    }

    public function testPostEventUniqueId(): void
    {
        $client = $this->getClient();
        $client->postEvent(new Event(
            new JobProcessingLongEventData(
                '1234',
                'My project',
                12.5,
                102.2,
                120,
                new JobData(
                    '23456',
                    'http://someUrl',
                    new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
                    null,
                    'keboola.orchestrator',
                    'Orchestrator',
                    'my-configuration',
                    'My configuration'
                )
            )
        ));
        self::assertTrue(true);
    }
}
