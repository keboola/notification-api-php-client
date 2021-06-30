<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests;

use Keboola\NotificationClient\Requests\PostEvent\FailedJobEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use Keboola\NotificationClient\Requests\PostEventRequest;
use PHPUnit\Framework\TestCase;

class PostEventRequestTest extends TestCase
{
    public function testCreate(): void
    {
        $postEventRequest = new PostEventRequest(
            'failed_job',
            new FailedJobEventData(
                'My failed job',
                new JobData('my-project', '123', 'http://someUrl', '2020-01-01', '2020-01-01', 'orchestration-name')
            )
        );
        self::assertSame(
            [
                'errorMessage' => 'My failed job',
                'jobData' => [
                    'project' => [
                        'name' => 'my-project',
                    ],
                    'job' => [
                        'id' => '123',
                        'url' => 'http://someUrl',
                        'startTime' => '2020-01-01',
                        'endTime' => '2020-01-01',
                        'orchestrationName' => 'orchestration-name',
                        'tasks' => [],
                    ],
                ],
            ],
            $postEventRequest->jsonSerialize()
        );
        self::assertSame('failed_job', $postEventRequest->getEventType());
    }
}
