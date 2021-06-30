<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostSubscription;

use Keboola\NotificationClient\Requests\PostSubscription\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testCreate(): void
    {
        $filter = new Filter('someName', 'someValue');
        self::assertSame(
            [
                'name' => 'someName',
                'value' => 'someValue',
            ],
            $filter->jsonSerialize()
        );
    }
}
