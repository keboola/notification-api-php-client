<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use Generator;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\StorageApiIndexClient;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class StorageApiIndexClientTest extends TestCase
{
    public function testFunctional(): void
    {
        $client = new StorageApiIndexClient(
            sprintf('https://connection.%s', (string) getenv('HOSTNAME_SUFFIX')),
            [
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
            ],
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
                $responseData,
            ),
        ]);
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $logsHandler = new TestHandler();
        $logger = new Logger('test', [$logsHandler]);

        $client = new StorageApiIndexClient(
            'https://dummy',
            ['handler' => $stack, 'logger' => $logger, 'backoffMaxTries' => 3, 'userAgent' => 'Test'],
        );
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($expectedError);
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

    public function testGetServiceUrlHeaders(): void
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"services": [{"id": "boo", "url": "foo"}]}',
            ),
        ]);
        // Add the history middleware to the handler stack.
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new StorageApiIndexClient(
            'https://example.com/',
            ['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test'],
        );
        $client->getServiceUrl('boo');

        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('GET', $request->getMethod());
        self::assertSame(
            ['User-Agent', 'Host'],
            array_keys($request->getHeaders()),
        );
    }
}
