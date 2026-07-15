<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use DateTimeImmutable;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Keboola\ApiClientBase\Auth\KeboolaServiceAccountAuthenticator;
use Keboola\NotificationClient\EventsClient;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\Event;
use Keboola\NotificationClient\Requests\PostEvent\JobData;
use Keboola\NotificationClient\Requests\PostEvent\JobFailedEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobProcessingLongEventData;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EventsClientFunctionalTest extends TestCase
{
    private function getClient(): EventsClient
    {
        $baseUrl = (string) getenv('TEST_NOTIFICATION_API_URL');
        $applicationToken = (string) getenv('TEST_MANAGE_API_APPLICATION_TOKEN');
        if ($baseUrl === '' || $applicationToken === '') {
            throw new RuntimeException('Test environment variables are not configured.');
        }

        return new EventsClient(
            $baseUrl,
            $applicationToken,
            backoffMaxTries: 3,
            userAgent: 'Test',
        );
    }

    private function getFailedEvent(): Event
    {
        return new Event(new JobFailedEventData(
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
        ));
    }

    public function testPostEvent(): void
    {
        $client = $this->getClient();
        $client->postEvent($this->getFailedEvent());
        self::assertTrue(true);
    }

    public function testPostEventUniqueId(): void
    {
        $client = $this->getClient();
        $client->postEvent(new Event(new JobProcessingLongEventData(
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
        )));
        self::assertTrue(true);
    }

    public function testPostEventSendsManageTokenHeaderAndBody(): void
    {
        $mock = new MockHandler([new Response(202, [], '')]);
        $client = new EventsClient(
            'https://example.com/',
            'testToken',
            requestHandler: HandlerStack::create($mock),
        );

        $client->postEvent($this->getFailedEvent());

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://example.com/events/job-failed', (string) $request->getUri());
        self::assertSame('testToken', $request->getHeaderLine('X-KBC-ManageApiToken'));
        self::assertSame('', $request->getHeaderLine('X-Kubernetes-Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Content-type'));
        self::assertSame('Keboola Notification PHP Client', $request->getHeaderLine('User-Agent'));
    }

    public function testNullTokenSelectsServiceAccountAuth(): void
    {
        $defaultPath = KeboolaServiceAccountAuthenticator::DEFAULT_TOKEN_PATH;
        if (is_readable($defaultPath)) {
            self::markTestSkipped(sprintf(
                'SA token at "%s" is mounted; cannot exercise the default-auth failure path.',
                $defaultPath,
            ));
        }

        // Construction is lazy; the request triggers the SA-token file read and its failure.
        // backoffMaxTries: 0 disables retries so the failure surfaces immediately.
        $client = new EventsClient(
            'https://example.com/',
            null,
            backoffMaxTries: 0,
            requestHandler: HandlerStack::create(new MockHandler([new Response(202, [], '')])),
        );

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($defaultPath);
        $client->postEvent($this->getFailedEvent());
    }

    public function testRetriesOn5xxThenSucceeds(): void
    {
        $mock = new MockHandler([new Response(500, [], ''), new Response(202, [], '')]);
        $client = new EventsClient(
            'https://example.com/',
            'testToken',
            backoffMaxTries: 2,
            requestHandler: HandlerStack::create($mock),
        );

        $client->postEvent($this->getFailedEvent());

        self::assertSame(0, $mock->count());
    }

    public function testThrowsClientExceptionOn4xxWithJsonErrorBody(): void
    {
        $mock = new MockHandler([new Response(422, [], '{"error":"Invalid event"}')]);
        $client = new EventsClient(
            'https://example.com/',
            'testToken',
            requestHandler: HandlerStack::create($mock),
        );

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Invalid event');
        $this->expectExceptionCode(422);
        $client->postEvent($this->getFailedEvent());
    }
}
