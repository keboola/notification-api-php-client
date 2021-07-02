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

    public function testInvalidData(): void
    {
        self::expectException(ClientException::class);
        self::expectExceptionMessage('$field must be string, null used');
        new Filter(['boo']);
    }
}