<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostNotification;

class FlowInfo
{
    private string $id;

    private string $name;

    private string $url;

    public function __construct(string $id, string $name, string $url)
    {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
        ];
    }
}
