<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\NotificationsClient;
use Keboola\NotificationClient\Requests\PostNotification\ProjectEmail;
use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use PHPUnit\Framework\TestCase;

class NotificationsFunctionalClientTest extends TestCase
{
    private function getClient(): NotificationsClient
    {
        return new NotificationsClient(
            (string) getenv('TEST_NOTIFICATION_API_URL'),
            (string) getenv('TEST_MANAGE_API_APPLICATION_TOKEN'),
            [
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
            ],
        );
    }


    public function testCreateClientInvalidToken(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Application token must be non-empty, "" provided.');
        new NotificationsClient(
            'https://example.com',
            '',
            [
                'backoffMaxTries' => 3,
                'userAgent' => 'Test',
            ],
        );
    }

    public function testPostNotification(): void
    {
        $client = $this->getClient();
        $response = $client->postNotification(new ProjectEmail(
            new EmailRecipient('devel+test-notification-api-client@keboola.com'),
            (string) getenv('TEST_STORAGE_API_PROJECT_ID'),
            'Test Project',
            'Notification API client test',
            'Test Notification Body',
        ));

        self::assertIsNumeric($response->getId());
    }

    public function testPostNotificationHeaders(): void
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{"id": "1", "event": "2", "filters": [], "recipient": {"channel": "foo", "address": "bar"}}',
            ),
        ]);
        // Add the history middleware to the handler stack.
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new NotificationsClient(
            'https://example.com/',
            'testToken',
            ['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test'],
        );
        $client->postNotification(new ProjectEmail(
            new EmailRecipient('devel+test-notification-api-client@keboola.com'),
            (string) getenv('TEST_STORAGE_API_PROJECT_ID'),
            'Test Project',
            'Notification API client test',
            'Test Notification Body',
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
