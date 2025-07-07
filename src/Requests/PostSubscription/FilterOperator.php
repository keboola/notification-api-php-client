<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostSubscription;

enum FilterOperator: string
{
    case Equal = '==';
    case LessThan = '<';
    case GreaterThan = '>';
    case LessThanOrEqual = '<=';
    case GreaterThanOrEqual = '>=';
}
