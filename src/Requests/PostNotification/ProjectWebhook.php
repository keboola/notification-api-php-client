<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostNotification;

use Keboola\NotificationClient\Requests\PostSubscription\WebhookRecipient;

class ProjectWebhook implements NotificationInterface
{
    private const TYPE = 'direct-project-webhook';

    private string $projectId;
    private string $projectName;
    private string $title;
    private ?string $message;
    private WebhookRecipient $recipient;

    public function __construct(
        WebhookRecipient $recipient,
        string $projectId,
        string $projectName,
        string $title,
        ?string $message,
    ) {
        $this->recipient = $recipient;
        $this->projectId = $projectId;
        $this->projectName = $projectName;
        $this->title = $title;
        $this->message = $message;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => self::TYPE,
            'recipient' => $this->recipient->jsonSerialize(),
            'data' => [
                'project' => [
                    'id' => $this->projectId,
                    'name' => $this->projectName,
                ],
                'title' => $this->title,
                'message' => $this->message,
            ],
        ];
    }
}
