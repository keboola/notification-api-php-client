<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostEvent;

use DateTimeImmutable;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use Keboola\NotificationClient\Requests\PostEvent\PhaseJobProcessingLongEventData;
use PHPUnit\Framework\TestCase;

class PhaseJobProcessingLongEventDataTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $jobData = new JobData(
            '23456',
            'http://someUrl',
            new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
            new DateTimeImmutable('2020-01-01T12:11:00+00:00'),
            'keboola.orchestrator',
            'Orchestrator',
            'my-configuration',
            'My configuration'
        );
        $failedEventData = new PhaseJobProcessingLongEventData(
            '1234',
            'My project',
            'Lithium Extractors',
            '123',
            12.534,
            23.854,
            12.0,
            $jobData
        );
        self::assertSame(
            [
                'averageDuration' => 23.854,
                'currentDuration' => 12.0,
                'durationOvertimePercentage' => 12.534,
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
                'phase' => [
                    'id' => '123',
                    'name' => 'Lithium Extractors',
                ],
                'project' => [
                    'id' => '1234',
                    'name' => 'My project',
                ],
                'uniqueId' => '23456',
            ],
            $failedEventData->jsonSerialize()
        );
        self::assertSame('phase-job-processing-long', $failedEventData::getEventTypeName());
    }
}
