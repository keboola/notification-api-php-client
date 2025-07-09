<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostNotification;

class JobInfo
{
    private string $id;

    private string $url;

    public function __construct(string $id, string $url)
    {
        $this->id = $id;
        $this->url = $url;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
        ];
    }
}
