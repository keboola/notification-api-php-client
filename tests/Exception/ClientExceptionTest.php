<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests\Exception;

use Keboola\ApiClientBase\Exception\ClientException as BaseClientException;
use Keboola\NotificationClient\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ClientExceptionTest extends TestCase
{
    public function testExtendsBasePackageExceptionAndCarriesStatusAndBody(): void
    {
        $exception = new ClientException('boom', 42, null, 404, '{"error":"x"}');

        self::assertInstanceOf(BaseClientException::class, $exception);
        self::assertInstanceOf(RuntimeException::class, $exception);
        self::assertSame('boom', $exception->getMessage());
        self::assertSame(42, $exception->getCode());
        self::assertSame(404, $exception->getStatusCode());
        self::assertSame('{"error":"x"}', $exception->getResponseBody());
    }

    public function testDefaultsMatchLegacyUsage(): void
    {
        $exception = new ClientException('just a message');

        self::assertSame('just a message', $exception->getMessage());
        self::assertSame(0, $exception->getCode());
        self::assertNull($exception->getStatusCode());
        self::assertNull($exception->getResponseBody());
    }
}
