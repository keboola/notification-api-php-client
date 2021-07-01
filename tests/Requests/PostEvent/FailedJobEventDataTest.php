<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostEvent;

use Keboola\NotificationClient\Requests\PostEvent\FailedJobEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use PHPUnit\Framework\TestCase;

class FailedJobEventDataTest extends TestCase
{
    public function testCreate(): void
    {
        $jobData = new JobData(
            'test-project',
            '23456',
            'http://someUrl',
            '2020-01-01',
            '2020-02-02',
            'my-orchestration'
        );
        $failedEventData = new FailedJobEventData('someMessage', $jobData);
        self::assertSame(
            [
                'errorMessage' => 'someMessage',
                'job' => [
                    'id' => '23456',
                    'url' => 'http://someUrl',
                    'startTime' => '2020-01-01',
                    'endTime' => '2020-02-02',
                    'orchestrationName' => 'my-orchestration',
                    'tasks' => [],
                ],
                'project' => [
                    'name' => 'test-project',
                ],
            ],
            $failedEventData->jsonSerialize()
        );
    }
}
