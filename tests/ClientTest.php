<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use DateTimeImmutable;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Keboola\NotificationClient\EventsClient;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\Event;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use Keboola\NotificationClient\Requests\PostEvent\JobFailedEventData;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class ClientTest extends TestCase
{
    /**
     * @param array{
     *     handler?: HandlerStack,
     *     backoffMaxTries: int<0, 100>,
     *     userAgent: string,
     *     logger?: LoggerInterface
     * } $options
     */
    private function getClient(array $options): EventsClient
    {
        return new EventsClient(
            'https://example.com/',
            'testToken',
            $options
        );
    }

    public function testCreateClientInvalidUrl(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageMatches(
            '#Invalid parameters when creating client: invalid url:\s*This value is not a valid URL\.#'
        );
        new EventsClient(
            'invalid url',
            'token',
            [
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
            ]
        );
    }

    private function getPostEventData(): Event
    {
        return new Event(
            new JobFailedEventData(
                '1234',
                'My project',
                'Some Error',
                new JobData(
                    '23456',
                    'http://someUrl',
                    new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
                    new DateTimeImmutable('2020-01-01T11:12:00+00:00'),
                    'keboola.orchestrator',
                    'Orchestrator',
                    'my-configuration',
                    'My configuration'
                )
            )
        );
    }

    public function testClientRequestResponse(): void
    {
        $mock = new MockHandler([
            new Response(
                202,
                ['Content-Type' => 'application/json'],
                null
            ),
        ]);
        // Add the history middleware to the handler stack.
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = $this->getClient(['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test']);
        $client->postEvent($this->getPostEventData());
        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('https://example.com/events/job-failed', $request->getUri()->__toString());
        self::assertSame('POST', $request->getMethod());
        self::assertSame('testToken', $request->getHeader('X-Kbc-ManageApiToken')[0]);
        self::assertSame('Test', $request->getHeader('User-Agent')[0]);
        self::assertSame('application/json', $request->getHeader('Content-type')[0]);
    }

    public function testLogger(): void
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{
                    "id": "683194249",
                    "runId": "683194249",
                    "status": "created",
                    "desiredStatus": "processing",
                    "mode": "run",
                    "component": "keboola.ex-db-snowflake",
                    "config": "123",
                    "configRow": null,
                    "tag": null,
                    "createdTime": "2021-03-04T21:59:49+00:00"
                }'
            ),
        ]);
        // Add the history middleware to the handler stack.
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $logger = new TestLogger();
        $client = $this->getClient(
            [
                'handler' => $stack,
                'logger' => $logger,
                'backoffMaxTries' => 3,
                'userAgent' => 'test agent',
            ]
        );
        $client->postEvent($this->getPostEventData());
        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('test agent', $request->getHeader('User-Agent')[0]);
        self::assertTrue($logger->hasDebugThatContains('"POST  /1.1" 200 '));
        self::assertTrue($logger->hasDebugThatContains('test agent'));
    }

    public function testRetrySuccess(): void
    {
        $mock = new MockHandler([
            new Response(
                500,
                ['Content-Type' => 'application/json'],
                '{"message" => "Out of order"}'
            ),
            new Response(
                501,
                ['Content-Type' => 'application/json'],
                'Out of order'
            ),
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{
                    "id": "683194249",
                    "runId": "683194249",
                    "status": "created",
                    "desiredStatus": "processing",
                    "mode": "run",
                    "component": "keboola.ex-db-snowflake",
                    "config": "123",
                    "configRow": null,
                    "tag": null,
                    "createdTime": "2021-03-04T21:59:49+00:00"
                }'
            ),
        ]);
        // Add the history middleware to the handler stack.
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $logger = new TestLogger();
        $client = $this->getClient(
            [
                'handler' => $stack,
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
                'logger' => $logger,
            ]
        );
        $client->postEvent($this->getPostEventData());
        self::assertCount(3, $requestHistory);
        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('https://example.com/events/job-failed', $request->getUri()->__toString());
        $request = $requestHistory[1]['request'];
        self::assertSame('https://example.com/events/job-failed', $request->getUri()->__toString());
        $request = $requestHistory[2]['request'];
        self::assertSame('https://example.com/events/job-failed', $request->getUri()->__toString());
        self::assertTrue($logger->hasNoticeThatContains('retrying'));
    }

    public function testRetryFailure(): void
    {
        $responses = [];
        for ($i = 0; $i < 2; $i++) {
            $responses[] = new Response(
                500,
                ['Content-Type' => 'application/json'],
                '{"message" => "Out of order"}'
            );
        }
        $mock = new MockHandler($responses);
        // Add the history middleware to the handler stack.
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $logger = new TestLogger();
        $client = $this->getClient(
            ['handler' => $stack, 'backoffMaxTries' => 1, 'userAgent' => 'Test', 'logger' => $logger]
        );
        try {
            $client->postEvent($this->getPostEventData());
            self::fail('Must throw exception');
        } catch (ClientException $e) {
            self::assertStringContainsString('500 Internal Server Error', $e->getMessage());
        }
        self::assertCount(2, $requestHistory);
        self::assertTrue($logger->hasNoticeThatContains('We have tried this 1 times. Giving up.'));
    }

    public function testNoRetry(): void
    {
        $mock = new MockHandler([
            new Response(
                401,
                ['Content-Type' => 'application/json'],
                '{"message" => "Unauthorized"}'
            ),
        ]);
        // Add the history middleware to the handler stack.
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = $this->getClient(['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test']);
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('{"message" => "Unauthorized"}');
        $client->postEvent($this->getPostEventData());
    }
}
