<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostEvent;

use DateTimeImmutable;
use Generator;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use PHPUnit\Framework\TestCase;

class JobDataTest extends TestCase
{
    /**
     * @dataProvider jobDataProvider
     */
    public function testJsonSerialize(JobData $jobData, array $expected): void
    {
        self::assertSame($expected, $jobData->jsonSerialize());
    }

    public function jobDataProvider(): Generator
    {
        yield 'full data' => [
            new JobData(
                '23456',
                'http://someUrl',
                new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
                new DateTimeImmutable('2020-01-01T12:11:00+00:00'),
                'keboola.orchestrator',
                'Orchestrator',
                'my-configuration',
                'My configuration'
            ),
            [
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
        ];
        yield 'null configuration id' => [
            new JobData(
                '23456',
                'http://someUrl',
                new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
                new DateTimeImmutable('2020-01-01T12:11:00+00:00'),
                'keboola.orchestrator',
                'Orchestrator',
                null,
                'My configuration'
            ),
            [
                'id' => '23456',
                'url' => 'http://someUrl',
                'component' => [
                    'id' => 'keboola.orchestrator',
                    'name' => 'Orchestrator',
                ],
                'tasks' => [],
                'startTime' => '2020-01-01T11:11:00+00:00',
                'endTime' => '2020-01-01T12:11:00+00:00',
            ],
        ];
        yield 'null configuration name' => [
            new JobData(
                '23456',
                'http://someUrl',
                new DateTimeImmutable('2020-01-01T12:11:00+00:00'),
                new DateTimeImmutable('2020-01-01T13:11:00+00:00'),
                'keboola.orchestrator',
                'Orchestrator',
                '1234',
                null
            ),
            [
                'id' => '23456',
                'url' => 'http://someUrl',
                'component' => [
                    'id' => 'keboola.orchestrator',
                    'name' => 'Orchestrator',
                ],
                'tasks' => [],
                'startTime' => '2020-01-01T12:11:00+00:00',
                'endTime' => '2020-01-01T13:11:00+00:00',
            ],
        ];
        yield 'empty start and end time' => [
            new JobData(
                '23456',
                'http://someUrl',
                null,
                null,
                'keboola.orchestrator',
                'Orchestrator',
                '1234',
                null
            ),
            [
                'id' => '23456',
                'url' => 'http://someUrl',
                'component' => [
                    'id' => 'keboola.orchestrator',
                    'name' => 'Orchestrator',
                ],
                'tasks' => [],
            ],
        ];
    }
}
