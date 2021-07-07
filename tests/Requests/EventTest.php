<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests;

use Keboola\NotificationClient\Requests\PostEvent\FailedJobEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use Keboola\NotificationClient\Requests\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $postEventRequest = new Event(
            'failed_job',
            new FailedJobEventData(
                'My failed job',
                new JobData('my-project', '123', 'http://someUrl', '2020-01-01', '2020-01-01', 'orchestration-name')
            )
        );
        self::assertSame(
            [
                'errorMessage' => 'My failed job',
                'job' => [
                    'id' => '123',
                    'url' => 'http://someUrl',
                    'startTime' => '2020-01-01',
                    'endTime' => '2020-01-01',
                    'orchestrationName' => 'orchestration-name',
                    'tasks' => [],
                ],
                'project' => [
                    'name' => 'my-project',
                ],
            ],
            $postEventRequest->jsonSerialize()
        );
        self::assertSame('failed_job', $postEventRequest->getEventType());
    }
}
