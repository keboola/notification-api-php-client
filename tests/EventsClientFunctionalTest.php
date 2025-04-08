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
use Keboola\NotificationClient\Requests\PostEvent\JobProcessingLongEventData;
use PHPUnit\Framework\TestCase;

class EventsClientFunctionalTest extends TestCase
{
    private function getClient(): EventsClient
    {
        return new EventsClient(
            (string) getenv('TEST_NOTIFICATION_API_URL'),
            (string) getenv('TEST_MANAGE_API_APPLICATION_TOKEN'),
            [
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
            ],
        );
    }

    public function testPostEvent(): void
    {
        $client = $this->getClient();
        $client->postEvent(new Event(
            new JobFailedEventData(
                '1234',
                'My project',
                'branch-id',
                'job failed',
                new JobData(
                    '23456',
                    'http://someUrl',
                    new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
                    new DateTimeImmutable('2020-01-01T12:11:00+00:00'),
                    'keboola.orchestrator',
                    'Orchestrator',
                    'my-configuration',
                    'My configuration',
                ),
            ),
        ));
        self::assertTrue(true);
    }


    public function testCreateClientInvalidToken(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Application token must be non-empty, "" provided.');
        new EventsClient(
            'https://example.com',
            '',
            [
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
            ],
        );
    }

    public function testPostEventUniqueId(): void
    {
        $client = $this->getClient();
        $client->postEvent(new Event(
            new JobProcessingLongEventData(
                '1234',
                'My project',
                'branch-id',
                12.5,
                102.2,
                120,
                new JobData(
                    '23456',
                    'http://someUrl',
                    new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
                    null,
                    'keboola.orchestrator',
                    'Orchestrator',
                    'my-configuration',
                    'My configuration',
                ),
            ),
        ));
        self::assertTrue(true);
    }

    public function testPostEventsHeaders(): void
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"ok": "1"}',
            ),
        ]);
        // Add the history middleware to the handler stack.
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new EventsClient(
            'https://example.com/',
            'testToken',
            ['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test'],
        );
        $client->postEvent(new Event(
            new JobFailedEventData(
                '1234',
                'My project',
                'branch-id',
                'Some Error',
                new JobData(
                    '23456',
                    'http://someUrl',
                    new DateTimeImmutable('2020-01-01T11:11:00+00:00'),
                    new DateTimeImmutable('2020-01-01T11:12:00+00:00'),
                    'keboola.orchestrator',
                    'Orchestrator',
                    'my-configuration',
                    'My configuration',
                ),
            ),
        ));

        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            ['Content-Length', 'User-Agent', 'X-Kbc-ManageApiToken', 'Host', 'Content-type'],
            array_keys($request->getHeaders()),
        );
        self::assertSame(['application/json'], $request->getHeaders()['Content-type']);
    }
}
