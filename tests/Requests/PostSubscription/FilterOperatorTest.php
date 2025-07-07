<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostSubscription;

use Keboola\NotificationClient\Requests\PostSubscription\FilterOperator;
use PHPUnit\Framework\TestCase;

class FilterOperatorTest extends TestCase
{
    /**
     * @dataProvider provideOperatorValues
     */
    public function testOperatorValues(FilterOperator $operator, string $expectedValue): void
    {
        self::assertSame($expectedValue, $operator->value);
    }

    public function provideOperatorValues(): iterable
    {
        yield 'equals' => [FilterOperator::Equal, '=='];
        yield 'less than' => [FilterOperator::LessThan, '<'];
        yield 'greater than' => [FilterOperator::GreaterThan, '>'];
        yield 'less than or equals' => [FilterOperator::LessThanOrEqual, '<='];
        yield 'greater than or equals' => [FilterOperator::GreaterThanOrEqual, '>='];
    }
}
