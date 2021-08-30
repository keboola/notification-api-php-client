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
use Keboola\NotificationClient\Requests\PostEvent\JobFailedEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class ClientTest extends TestCase
{
    private function getClient(array $options): EventsClient
    {
        return new EventsClient(
            'http://example.com/',
            'testToken',
            $options
        );
    }

    public function testCreateClientInvalidBackoff(): void
    {
        self::expectException(ClientException::class);
        self::expectExceptionMessage(
            'Invalid parameters when creating client: Value "abc" is invalid: This value should be a valid number'
        );
        new EventsClient(
            'http://example.com/',
            'testToken',
            ['backoffMaxTries' => 'abc']
        );
    }

    public function testCreateClientTooLowBackoff(): void
    {
        self::expectException(ClientException::class);
        self::expectExceptionMessage(
            'Invalid parameters when creating client: Value "-1" is invalid: This value should be between 0 and 100.'
        );
        new EventsClient(
            'http://example.com/',
            'testToken',
            ['backoffMaxTries' => -1]
        );
    }

    public function testCreateClientTooHighBackoff(): void
    {
        self::expectException(ClientException::class);
        self::expectExceptionMessage(
            'Invalid parameters when creating client: Value "101" is invalid: This value should be between 0 and 100.'
        );
        new EventsClient(
            'http://example.com/',
            'testToken',
            ['backoffMaxTries' => 101]
        );
    }

    public function testCreateClientInvalidToken(): void
    {
        self::expectException(ClientException::class);
        self::expectExceptionMessage(
            'Application token must be non-empty, "" provided.'
        );
        new EventsClient('http://example.com/', '');
    }

    public function testCreateClientInvalidUrl(): void
    {
        self::expectException(ClientException::class);
        self::expectExceptionMessage(
            'Value "invalid url" is invalid: This value is not a valid URL'
        );
        new EventsClient('invalid url', 'testToken');
    }

    public function testCreateClientMultipleErrors(): void
    {
        self::expectException(ClientException::class);
        self::expectExceptionMessage(
            'Invalid parameters when creating client: Value "invalid url" is invalid: This value is not a valid URL.'
            . "\n" . 'Value "abc" is invalid: This value should be a valid number.'
        );
        new EventsClient('invalid url', 'SomeToken', ['backoffMaxTries' => 'abc']);
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
        $client = $this->getClient(['handler' => $stack]);
        $client->postEvent($this->getPostEventData());
        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertEquals('http://example.com/events/job-failed', $request->getUri()->__toString());
        self::assertEquals('POST', $request->getMethod());
        self::assertEquals('testToken', $request->getHeader('X-Kbc-ManageApiToken')[0]);
        self::assertEquals('Notification PHP Client', $request->getHeader('User-Agent')[0]);
        self::assertEquals('application/json', $request->getHeader('Content-type')[0]);
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
        $client = $this->getClient(['handler' => $stack, 'logger' => $logger, 'userAgent' => 'test agent']);
        $client->postEvent($this->getPostEventData());
        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertEquals('test agent', $request->getHeader('User-Agent')[0]);
        self::assertTrue($logger->hasInfoThatContains('"POST  /1.1" 200 '));
        self::assertTrue($logger->hasInfoThatContains('test agent'));
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
        $client = $this->getClient(['handler' => $stack]);
        $client->postEvent($this->getPostEventData());
        self::assertCount(3, $requestHistory);
        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertEquals('http://example.com/events/job-failed', $request->getUri()->__toString());
        $request = $requestHistory[1]['request'];
        self::assertEquals('http://example.com/events/job-failed', $request->getUri()->__toString());
        $request = $requestHistory[2]['request'];
        self::assertEquals('http://example.com/events/job-failed', $request->getUri()->__toString());
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
        $client = $this->getClient(['handler' => $stack, 'backoffMaxTries' => 1]);
        try {
            $client->postEvent($this->getPostEventData());
            self::fail('Must throw exception');
        } catch (ClientException $e) {
            self::assertStringContainsString('500 Internal Server Error', $e->getMessage());
        }
        self::assertCount(2, $requestHistory);
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
        $client = $this->getClient(['handler' => $stack]);
        self::expectException(ClientException::class);
        self::expectExceptionMessage('{"message" => "Unauthorized"}');
        $client->postEvent($this->getPostEventData());
    }
}
