<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostSubscription;

use Keboola\NotificationClient\Requests\PostSubscription\Filter;
use Keboola\NotificationClient\Requests\PostSubscription\FilterOperator;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $filter = new Filter('someName', 'someValue');
        self::assertSame(
            [
                'field' => 'someName',
                'value' => 'someValue',
            ],
            $filter->jsonSerialize()
        );
    }

    public function testJsonSerializeWithOperator(): void
    {
        $filter = new Filter('someName', 'someValue', FilterOperator::GREATER_THAN_OR_EQUAL());
        self::assertSame(
            [
                'field' => 'someName',
                'value' => 'someValue',
                'operator' => '>=',
            ],
            $filter->jsonSerialize()
        );
    }
}
