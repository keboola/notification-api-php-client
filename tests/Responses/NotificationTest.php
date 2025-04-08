<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Responses;

use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Responses\Notification;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testAccessors(): void
    {
        $data = [
            'id' => '123',
        ];
        $notification = new Notification($data);
        self::assertSame('123', $notification->getId());
    }

    public function testInvalidData(): void
    {
        $data = [
            'some' => 'value',
        ];
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Unrecognized response');
        $this->expectExceptionCode(0);
        new Notification($data);
    }
}
