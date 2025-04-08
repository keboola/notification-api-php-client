<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostNotification;

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
}
