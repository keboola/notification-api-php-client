<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostNotification;

use Keboola\NotificationClient\Requests\PostNotification\FlowInfo;
use Keboola\NotificationClient\Requests\PostNotification\JobInfo;
use Keboola\NotificationClient\Requests\PostNotification\ProjectEmail;
use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use PHPUnit\Framework\TestCase;

class ProjectEmailTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $recipient = new EmailRecipient('john.doe@example.com');
        $projectId = '12345';
        $projectName = 'Test Project';
        $title = 'Test Notification';
        $message = 'This is a test notification message';

        $projectEmail = new ProjectEmail(
            $recipient,
            $projectId,
            $projectName,
            $title,
            $message,
        );

        self::assertSame(
            [
                'type' => 'direct-project-email',
                'recipient' => [
                    'channel' => 'email',
                    'address' => 'john.doe@example.com',
                ],
                'data' => [
                    'project' => [
                        'id' => $projectId,
                        'name' => $projectName,
                    ],
                    'title' => $title,
                    'message' => $message,
                ],
            ],
            $projectEmail->jsonSerialize(),
        );
    }

    public function testJsonSerializeWithFlowAndJob(): void
    {
        $recipient = new EmailRecipient('john.doe@example.com');
        $projectId = '12345';
        $projectName = 'Test Project';
        $title = 'Test Notification';
        $message = 'This is a test notification message';
        $flow = new FlowInfo(
            'flow-123',
            'My Test Flow',
            'https://connection.keboola.com/flows/123',
        );
        $job = new JobInfo(
            'job-123',
            'https://connection.keboola.com/jobs/123',
        );

        $projectEmail = new ProjectEmail(
            $recipient,
            $projectId,
            $projectName,
            $title,
            $message,
            $flow,
            $job,
        );

        self::assertSame(
            [
                'type' => 'direct-project-email',
                'recipient' => [
                    'channel' => 'email',
                    'address' => 'john.doe@example.com',
                ],
                'data' => [
                    'project' => [
                        'id' => $projectId,
                        'name' => $projectName,
                    ],
                    'title' => $title,
                    'message' => $message,
                    'flow' => [
                        'id' => 'flow-123',
                        'name' => 'My Test Flow',
                        'url' => 'https://connection.keboola.com/flows/123',
                    ],
                    'job' => [
                        'id' => 'job-123',
                        'url' => 'https://connection.keboola.com/jobs/123',
                    ],
                ],
            ],
            $projectEmail->jsonSerialize(),
        );
    }

    public function testJsonSerializeWithFlow(): void
    {
        $recipient = new EmailRecipient('john.doe@example.com');
        $projectId = '12345';
        $projectName = 'Test Project';
        $title = 'Test Notification';
        $message = 'This is a test notification message';
        $flow = new FlowInfo(
            'flow-123',
            'My Test Flow',
            'https://connection.keboola.com/flows/123',
        );

        $projectEmail = new ProjectEmail(
            $recipient,
            $projectId,
            $projectName,
            $title,
            $message,
            $flow,
        );

        self::assertSame(
            [
                'type' => 'direct-project-email',
                'recipient' => [
                    'channel' => 'email',
                    'address' => 'john.doe@example.com',
                ],
                'data' => [
                    'project' => [
                        'id' => $projectId,
                        'name' => $projectName,
                    ],
                    'title' => $title,
                    'message' => $message,
                    'flow' => [
                        'id' => 'flow-123',
                        'name' => 'My Test Flow',
                        'url' => 'https://connection.keboola.com/flows/123',
                    ],
                ],
            ],
            $projectEmail->jsonSerialize(),
        );
    }

    public function testJsonSerializeWithJob(): void
    {
        $recipient = new EmailRecipient('john.doe@example.com');
        $projectId = '12345';
        $projectName = 'Test Project';
        $title = 'Test Notification';
        $message = 'This is a test notification message';
        $job = new JobInfo(
            'job-123',
            'https://connection.keboola.com/jobs/123',
        );

        $projectEmail = new ProjectEmail(
            $recipient,
            $projectId,
            $projectName,
            $title,
            $message,
            null,
            $job,
        );

        self::assertSame(
            [
                'type' => 'direct-project-email',
                'recipient' => [
                    'channel' => 'email',
                    'address' => 'john.doe@example.com',
                ],
                'data' => [
                    'project' => [
                        'id' => $projectId,
                        'name' => $projectName,
                    ],
                    'title' => $title,
                    'message' => $message,
                    'job' => [
                        'id' => 'job-123',
                        'url' => 'https://connection.keboola.com/jobs/123',
                    ],
                ],
            ],
            $projectEmail->jsonSerialize(),
        );
    }
}
