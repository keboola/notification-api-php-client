<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Keboola\ApiClientBase\Auth\KeboolaServiceAccountAuthenticator;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\NotificationsClient;
use Keboola\NotificationClient\Requests\PostNotification\ProjectEmail;
use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class NotificationsFunctionalClientTest extends TestCase
{
    private function getClient(): NotificationsClient
    {
        $baseUrl = (string) getenv('TEST_NOTIFICATION_API_URL');
        $applicationToken = (string) getenv('TEST_MANAGE_API_APPLICATION_TOKEN');
        if ($baseUrl === '' || $applicationToken === '') {
            throw new RuntimeException('Test environment variables are not configured.');
        }

        return new NotificationsClient(
            $baseUrl,
            $applicationToken,
            backoffMaxTries: 3,
            userAgent: 'Test',
        );
    }

    private function getNotification(): ProjectEmail
    {
        return new ProjectEmail(
            new EmailRecipient('devel+test-notification-api-client@keboola.com'),
            (string) getenv('TEST_STORAGE_API_PROJECT_ID'),
            'Test Project',
            'Notification API client test',
            'Test Notification Body',
        );
    }

    public function testPostNotification(): void
    {
        $client = $this->getClient();
        $response = $client->postNotification($this->getNotification());

        self::assertIsNumeric($response->getId());
    }

    public function testPostNotificationMapsResponseAndSendsManageTokenHeader(): void
    {
        $mock = new MockHandler([new Response(200, [], '{"id": "12345"}')]);
        $client = new NotificationsClient(
            'https://example.com/',
            'testToken',
            requestHandler: HandlerStack::create($mock),
        );

        $response = $client->postNotification($this->getNotification());

        self::assertSame('12345', $response->getId());
        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://example.com/notifications', (string) $request->getUri());
        self::assertSame('testToken', $request->getHeaderLine('X-KBC-ManageApiToken'));
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

        $client = new NotificationsClient(
            'https://example.com/',
            null,
            backoffMaxTries: 0,
            requestHandler: HandlerStack::create(new MockHandler([new Response(200, [], '{"id":"1"}')])),
        );

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage($defaultPath);
        $client->postNotification($this->getNotification());
    }
}
