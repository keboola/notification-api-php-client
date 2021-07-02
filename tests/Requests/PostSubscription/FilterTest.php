<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostSubscription;

use Keboola\NotificationClient\Requests\PostSubscription\Filter;
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
}
