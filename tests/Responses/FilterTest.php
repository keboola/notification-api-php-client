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
        $filter = new Filter(['field' => 'foo', 'value' => 'bar']);
        self::assertSame('foo', $filter->getField());
        self::assertSame('bar', $filter->getValue());
    }

    /**
     * @dataProvider provideScalarValues
     */
    public function testScalarValues(int|float|bool|string|null $value): void
    {
        $filter = new Filter(['field' => 'foo', 'value' => $value]);
        self::assertSame('foo', $filter->getField());
        self::assertSame($value, $filter->getValue());
    }

    public function provideScalarValues(): iterable
    {
        yield 'int' => [12345];
        yield 'float' => [0.2];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'null' => [null];
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
}
