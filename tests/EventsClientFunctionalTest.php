<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use Keboola\NotificationClient\EventsClient;
use Keboola\NotificationClient\Requests\Event;
use Keboola\NotificationClient\Requests\PostEvent\FailedJobEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
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
            new FailedJobEventData(
                '1234',
                'My project',
                'job failed',
                new JobData(
                    '23456',
                    'http://someUrl',
                    '2020-01-01T12:00:01Z',
                    '2020-02-02T15:15:15Z',
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
