<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostEvent;

use Keboola\NotificationClient\Requests\PostEvent\FailedJobEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use PHPUnit\Framework\TestCase;

class FailedJobEventDataTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $jobData = new JobData(
            '23456',
            'http://someUrl',
            '2020-01-01',
            '2020-02-02',
            'keboola.orchestrator',
            'Orchestrator',
            'my-configuration',
            'My configuration'
        );
        $failedEventData = new FailedJobEventData('1234', 'My project', 'someMessage', $jobData);
        self::assertSame(
            [
                'errorMessage' => 'someMessage',
                'job' => [
                    'id' => '23456',
                    'url' => 'http://someUrl',
                    'startTime' => '2020-01-01',
                    'endTime' => '2020-02-02',
                    'component' => [
                        'id' => 'keboola.orchestrator',
                        'name' => 'Orchestrator',
                    ],
                    'configuration' => [
                        'id' => 'my-configuration',
                        'name' => 'My configuration',
                    ],
                    'tasks' => [],
                ],
                'project' => [
                    'id' => '1234',
                    'name' => 'My project',
                ],
            ],
            $failedEventData->jsonSerialize()
        );
    }
}
