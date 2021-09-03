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
        self::assertSame($expectedValue, $operator->getValue());
    }

    public function provideOperatorValues(): iterable
    {
        yield 'equals' => [FilterOperator::EQUAL(), '=='];
        yield 'less than' => [FilterOperator::LESS_THAN(), '<'];
        yield 'greater than' => [FilterOperator::GREATER_THAN(), '>'];
        yield 'less than or equals' => [FilterOperator::LESS_THAN_OR_EQUAL(), '<='];
        yield 'greater than or equals' => [FilterOperator::GREATER_THAN_OR_EQUAL(), '>='];
    }
}
