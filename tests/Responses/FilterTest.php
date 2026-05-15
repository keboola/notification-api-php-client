<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Responses;

use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Responses\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testAccessors(): void
    {
        $filter = new Filter(['field' => 'foo', 'value' => 'bar', 'operator' => '==']);
        self::assertSame('foo', $filter->getField());
        self::assertSame('bar', $filter->getValue());
        self::assertSame('==', $filter->getOperator());
    }

    /**
     * @dataProvider provideScalarValues
     */
    public function testScalarValues(int|float|bool|string|null $value): void
    {
        $filter = new Filter(['field' => 'foo', 'value' => $value, 'operator' => '==']);
        self::assertSame('foo', $filter->getField());
        self::assertSame($value, $filter->getValue());
        self::assertSame('==', $filter->getOperator());
    }

    public function provideScalarValues(): iterable
    {
        yield 'int' => [12345];
        yield 'float' => [0.2];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'null' => [null];
    }

    /**
     * @dataProvider provideOperators
     */
    public function testOperators(string $operator): void
    {
        $filter = new Filter(['field' => 'foo', 'value' => 'bar', 'operator' => $operator]);
        self::assertSame($operator, $filter->getOperator());
    }

    public function provideOperators(): iterable
    {
        yield 'equal' => ['=='];
        yield 'less than' => ['<'];
        yield 'greater than' => ['>'];
        yield 'less than or equal' => ['<='];
        yield 'greater than or equal' => ['>='];
    }

    public function testInvalidData(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageMatches(
            '#Unrecognized response:.*?(\$field must be string|\$field of type string)#',
        );
        $this->expectExceptionCode(0);
        new Filter(['boo', 'field' => 1]);
    }

    public function testMissingOperator(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageMatches('#Unrecognized response:.*?operator#');
        $this->expectExceptionCode(0);
        new Filter(['field' => 'foo', 'value' => 'bar']);
    }
}
