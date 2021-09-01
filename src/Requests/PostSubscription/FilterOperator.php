<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostSubscription;

use DomainException;
use MyCLabs\Enum\Enum;

/**
 * @method static self EQUAL()
 * @method static self LESS_THAN()
 * @method static self GREATER_THAN()
 * @method static self LESS_THAN_OR_EQUAL()
 * @method static self GREATER_THAN_OR_EQUAL()
 *
 * @phpstan-extends Enum<string>
 */
class FilterOperator extends Enum
{
    // phpcs:disable SlevomatCodingStandard.Classes.UnusedPrivateElements
    private const EQUAL = '=='; // can't be called EQUALS because there is a method called `equals` on base Enum class
    private const LESS_THAN = '<';
    private const GREATER_THAN = '>';
    private const LESS_THAN_OR_EQUAL = '<=';
    private const GREATER_THAN_OR_EQUAL = '>=';
    // phpcs:enable SlevomatCodingStandard.Classes.UnusedPrivateElements
}
