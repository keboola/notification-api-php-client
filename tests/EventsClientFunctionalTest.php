<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use Keboola\NotificationClient\EventsClient;
use Keboola\NotificationClient\Requests\PostEvent\FailedJobEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use Keboola\NotificationClient\Requests\Event;
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
            'jobFailed',
            new FailedJobEventData(
                'job failed',
                new JobData(
                    'my-project',
                    '123',
                    'https://someUrl',
                    '2021-06-07T10:00:00Z',
                    '2021-06-07T10:00:00Z',
                    'my-orchestration'
                )
            )
        ));
        self::assertTrue(true);
    }
}
