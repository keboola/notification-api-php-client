<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses\Recipient;

interface RecipientInterface
{
    public function getChannel(): string;
}
