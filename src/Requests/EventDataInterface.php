<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests;

use JsonSerializable;

interface EventDataInterface extends JsonSerializable
{
    public static function getEventTypeName(): string;
}
