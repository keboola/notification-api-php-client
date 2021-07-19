<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostEvent;

use Keboola\NotificationClient\Requests\PostEvent\JobData;
use PHPUnit\Framework\TestCase;

class JobDataTest extends TestCase
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
        self::assertSame(
            [
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
            $jobData->jsonSerialize()
        );
    }

    public function testJsonSerializeEmptyConfigurationId(): void
    {
        $jobData = new JobData(
            '23456',
            'http://someUrl',
            '2020-01-01',
            '2020-02-02',
            'keboola.orchestrator',
            'Orchestrator',
            null,
            'My configuration'
        );
        self::assertSame(
            [
                'id' => '23456',
                'url' => 'http://someUrl',
                'startTime' => '2020-01-01',
                'endTime' => '2020-02-02',
                'component' => [
                    'id' => 'keboola.orchestrator',
                    'name' => 'Orchestrator',
                ],
                'tasks' => [],
            ],
            $jobData->jsonSerialize()
        );
    }

    public function testJsonSerializeEmptyConfigurationName(): void
    {
        $jobData = new JobData(
            '23456',
            'http://someUrl',
            '2020-01-01',
            '2020-02-02',
            'keboola.orchestrator',
            'Orchestrator',
            '1234',
            null
        );
        self::assertSame(
            [
                'id' => '23456',
                'url' => 'http://someUrl',
                'startTime' => '2020-01-01',
                'endTime' => '2020-02-02',
                'component' => [
                    'id' => 'keboola.orchestrator',
                    'name' => 'Orchestrator',
                ],
                'tasks' => [],
            ],
            $jobData->jsonSerialize()
        );
    }
}
