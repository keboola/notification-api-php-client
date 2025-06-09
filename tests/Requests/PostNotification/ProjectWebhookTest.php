<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostNotification;

use Keboola\NotificationClient\Requests\PostNotification\ProjectWebhook;
use Keboola\NotificationClient\Requests\PostSubscription\WebhookRecipient;
use PHPUnit\Framework\TestCase;

class ProjectWebhookTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $recipient = new WebhookRecipient('https://example.com/webhook');
        $projectId = '12345';
        $projectName = 'Test Project';
        $title = 'Test Notification';
        $message = 'This is a test notification message';

        $projectWebhook = new ProjectWebhook(
            $recipient,
            $projectId,
            $projectName,
            $title,
            $message,
        );

        self::assertSame(
            [
                'type' => 'direct-project-webhook',
                'recipient' => [
                    'channel' => 'webhook',
                    'url' => 'https://example.com/webhook',
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
            $projectWebhook->jsonSerialize(),
        );
    }

    public function testJsonSerializeWithNullMessage(): void
    {
        $recipient = new WebhookRecipient('https://example.com/webhook');
        $projectId = '67890';
        $projectName = 'Another Test Project';
        $title = 'Test Notification Without Message';
        $message = null;

        $projectWebhook = new ProjectWebhook(
            $recipient,
            $projectId,
            $projectName,
            $title,
            $message,
        );

        self::assertSame(
            [
                'type' => 'direct-project-webhook',
                'recipient' => [
                    'channel' => 'webhook',
                    'url' => 'https://example.com/webhook',
                ],
                'data' => [
                    'project' => [
                        'id' => $projectId,
                        'name' => $projectName,
                    ],
                    'title' => $title,
                    'message' => null,
                ],
            ],
            $projectWebhook->jsonSerialize(),
        );
    }
}
