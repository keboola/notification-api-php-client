<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Requests\PostNotification;

use Keboola\NotificationClient\Requests\PostNotification\FlowInfo;
use PHPUnit\Framework\TestCase;

class FlowInfoTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $flowInfo = new FlowInfo(
            'flow-123',
            'My Test Flow',
            'https://connection.keboola.com/flows/123',
        );

        $this->assertSame([
            'id' => 'flow-123',
            'name' => 'My Test Flow',
            'url' => 'https://connection.keboola.com/flows/123',
        ], $flowInfo->jsonSerialize());
    }
}
