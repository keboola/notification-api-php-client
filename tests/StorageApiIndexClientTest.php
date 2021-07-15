<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use Generator;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\StorageApiIndexClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class StorageApiIndexClientTest extends TestCase
{
    public function testFunctional(): void
    {
        $client = new StorageApiIndexClient(
            (string) getenv('TEST_STORAGE_API_URL'),
        );
        self::assertStringStartsWith('https://notification.', $client->getServiceUrl('notification'));
    }

    /**
     * @dataProvider invalidResponseProvider
     */
    public function testInvalidResponse(string $responseData, string $expectedError): void
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                $responseData
            ),
        ]);
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $logger = new TestLogger();
        $client = new StorageApiIndexClient('https://dummy', ['handler' => $stack, 'logger' => $logger]);
        self::expectException(ClientException::class);
        self::expectExceptionMessage($expectedError);
        $client->getServiceUrl('notification');
    }

    public function invalidResponseProvider(): Generator
    {
        yield 'invalid response' => [
            '{
                "api": "storage"
            }',
            'Invalid response from index',
        ];
        yield 'invalid services' => [
            '{
                "api": "storage",
                "services": "none for the next 100 miles"
             }',
            'Invalid response from index',
        ];
        yield 'malformed service' => [
            '{
                "api": "storage",
                "services": [
                    {
                        "name": "notification",
                        "address": "https://notificiation.keboola.com"
                    }
                ]
            }',
            'Service "notification" was not found in index.',
        ];
        yield 'malformed service 2' => [
            '{
                "api": "storage",
                "services": [
                    {
                        "id": "notification",
                        "address": "https://notificiation.keboola.com"
                    }
                ]
            }',
            'Service "notification" was not found in index.',
        ];
        yield 'service not found' => [
            '{
                "api": "storage",
                "services": [
                    {
                        "id": "encryption",
                        "url": "https://encryption.keboola.com"
                    }
                ]
            }',
            'Service "notification" was not found in index.',
        ];
    }
}
