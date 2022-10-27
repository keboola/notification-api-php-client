<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use Keboola\NotificationClient\Requests\PostSubscription\Filter;
use Keboola\NotificationClient\Requests\Subscription;
use Keboola\NotificationClient\StorageApiIndexClient;
use Keboola\NotificationClient\SubscriptionClient;
use PHPUnit\Framework\TestCase;

class SubscriptionClientFunctionalTest extends TestCase
{
    private function getClient(): SubscriptionClient
    {
        return new SubscriptionClient(
            (string) getenv('TEST_NOTIFICATION_API_URL'),
            (string) getenv('TEST_STORAGE_API_TOKEN'),
            [
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
            ]
        );
    }


    public function testCreateClientInvalidToken(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Storage API token must be non-empty, "" provided.');
        new SubscriptionClient(
            'https://example.com',
            '',
            [
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
            ]
        );
    }

    public function testCreateSubscription(): void
    {
        $client = $this->getClient();
        $response = $client->createSubscription(new Subscription(
            'job-failed',
            new EmailRecipient('johnDoe@example.com'),
            [
                new Filter('project.id', (string) getenv('TEST_STORAGE_API_PROJECT_ID')),
            ]
        ));

        self::assertNotEmpty($response->getId());
        self::assertSame('job-failed', $response->getEvent());
        self::assertSame('project.id', $response->getFilters()[0]->getField());
        self::assertSame((string) getenv('TEST_STORAGE_API_PROJECT_ID'), $response->getFilters()[0]->getValue());
        self::assertSame('johnDoe@example.com', $response->getRecipientAddress());
        self::assertSame('email', $response->getRecipientChannel());
    }

    public function testCreateInvalidSubscription(): void
    {
        $client = $this->getClient();
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage(
            'Invalid event type "dummy-event", valid types are: ' .
            '"job-failed, job-succeeded, job-succeeded-with-warning, job-processing-long, phase-job-failed, ' .
            'phase-job-succeeded, phase-job-succeeded-with-warning, phase-job-processing-long".'
        );
        $client->createSubscription(new Subscription(
            'dummy-event',
            new EmailRecipient('johnDoe@example.com'),
            [new Filter('projectId', (string) getenv('TEST_STORAGE_API_PROJECT_ID'))]
        ));
    }

    public function testCreateSubscriptionHeaders(): void
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"id": "1", "event": "2", "filters": [], "recipient": {"channel": "foo", "address": "bar"}}'
            ),
        ]);
        // Add the history middleware to the handler stack.
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new SubscriptionClient(
            'https://example.com/',
            'testToken',
            ['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test']
        );
        $client->createSubscription(new Subscription(
            'job-failed',
            new EmailRecipient('john.doe@example.com'),
            [
                new Filter('key', 'value'),
            ]
        ));

        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            ['Content-Length', 'User-Agent', 'X-StorageApi-Token', 'Host', 'Content-type'],
            array_keys($request->getHeaders())
        );
        self::assertSame(['application/json'], $request->getHeaders()['Content-type']);
    }
}
