<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostNotification;

use Keboola\NotificationClient\Requests\PostNotification\JobInfo;
use PHPUnit\Framework\TestCase;

class JobInfoTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $jobInfo = new JobInfo(
            'job-123',
            'https://connection.keboola.com/jobs/123',
        );

        $this->assertSame([
            'id' => 'job-123',
            'url' => 'https://connection.keboola.com/jobs/123',
        ], $jobInfo->jsonSerialize());
    }
}
