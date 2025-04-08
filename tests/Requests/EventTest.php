<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests;

use DateTimeImmutable;
use Keboola\NotificationClient\Requests\Event;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use Keboola\NotificationClient\Requests\PostEvent\JobFailedEventData;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $postEventRequest = new Event(
            new JobFailedEventData(
                '1234',
                'My Project',
                'branch-id',
                'My failed job',
                new JobData(
                    '23456',
                    'http://someUrl',
                    new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
                    new DateTimeImmutable('2020-01-01T12:11:00+00:00'),
                    'keboola.orchestrator',
                    'Orchestrator',
                    'my-configuration',
                    'My configuration',
                ),
            ),
        );
        self::assertSame(
            [
                'errorMessage' => 'My failed job',
                'job' => [
                    'id' => '23456',
                    'url' => 'http://someUrl',
                    'component' => [
                        'id' => 'keboola.orchestrator',
                        'name' => 'Orchestrator',
                    ],
                    'tasks' => [],
                    'startTime' => '2020-01-01T11:11:00+00:00',
                    'endTime' => '2020-01-01T12:11:00+00:00',
                    'configuration' => [
                        'id' => 'my-configuration',
                        'name' => 'My configuration',
                    ],
                ],
                'project' => [
                    'id' => '1234',
                    'name' => 'My Project',
                ],
                'branch' => [
                    'id' => 'branch-id',
                ],
            ],
            $postEventRequest->jsonSerialize(),
        );
        self::assertSame('job-failed', $postEventRequest->getEventType());
    }
}
