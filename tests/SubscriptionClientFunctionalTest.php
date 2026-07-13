<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use Keboola\NotificationClient\Requests\PostSubscription\Filter;
use Keboola\NotificationClient\Requests\Subscription;
use Keboola\NotificationClient\Responses\Recipient\EmailRecipient as ResponseEmailRecipient;
use Keboola\NotificationClient\Responses\Subscription as ResponseSubscription;
use Keboola\NotificationClient\SubscriptionClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SubscriptionClientFunctionalTest extends TestCase
{
    private function getClient(): SubscriptionClient
    {
        $baseUrl = (string) getenv('TEST_NOTIFICATION_API_URL');
        $storageApiToken = (string) getenv('TEST_STORAGE_API_TOKEN');
        if ($baseUrl === '' || $storageApiToken === '') {
            throw new RuntimeException('Test environment variables are not configured.');
        }

        return new SubscriptionClient(
            $baseUrl,
            $storageApiToken,
            backoffMaxTries: 3,
            userAgent: 'Test',
        );
    }

    private function mockClient(MockHandler $mock): SubscriptionClient
    {
        return new SubscriptionClient(
            'https://example.com/',
            'testToken',
            requestHandler: HandlerStack::create($mock),
        );
    }

    public function testCreateSubscription(): void
    {
        $client = $this->getClient();
        $response = $client->createSubscription(new Subscription(
            'job-failed',
            new EmailRecipient('johnDoe@example.com'),
            [new Filter('project.id', (string) getenv('TEST_STORAGE_API_PROJECT_ID'))],
        ));

        self::assertNotEmpty($response->getId());
        self::assertSame('job-failed', $response->getEvent());
        self::assertSame('project.id', $response->getFilters()[0]->getField());
        self::assertSame((string) getenv('TEST_STORAGE_API_PROJECT_ID'), $response->getFilters()[0]->getValue());
        $recipient = $response->getRecipient();
        self::assertInstanceOf(ResponseEmailRecipient::class, $recipient);
        self::assertSame('email', $recipient->getChannel());
        self::assertSame('johnDoe@example.com', $recipient->getAddress());
    }

    public function testCreateInvalidSubscription(): void
    {
        $client = $this->getClient();
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage(
            'Invalid event type "dummy-event", valid types are: ' .
            '"job-failed, job-succeeded, job-succeeded-with-warning, job-processing-long, phase-job-failed, ' .
            'phase-job-succeeded, phase-job-succeeded-with-warning, phase-job-processing-long".',
        );
        $client->createSubscription(new Subscription(
            'dummy-event',
            new EmailRecipient('johnDoe@example.com'),
            [new Filter('projectId', (string) getenv('TEST_STORAGE_API_PROJECT_ID'))],
        ));
    }

    public function testCreateSubscriptionSendsStorageTokenHeader(): void
    {
        $mock = new MockHandler([new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"id": "1", "event": "2", "filters": [], "recipient": {"channel": "email", "address": "bar"}}',
        )]);
        $client = $this->mockClient($mock);

        $client->createSubscription(new Subscription(
            'job-failed',
            new EmailRecipient('john.doe@example.com'),
            [new Filter('key', 'value')],
        ));

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://example.com/project-subscriptions', (string) $request->getUri());
        self::assertSame('testToken', $request->getHeaderLine('X-StorageApi-Token'));
        self::assertSame('application/json', $request->getHeaderLine('Content-type'));
    }

    public function testDeleteSubscription(): void
    {
        $mock = new MockHandler([new Response(204, [], '')]);
        $client = $this->mockClient($mock);

        $client->deleteSubscription('subscription-123');

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame('https://example.com/project-subscriptions/subscription-123', (string) $request->getUri());
        self::assertSame('testToken', $request->getHeaderLine('X-StorageApi-Token'));
    }

    public function testGetSubscription(): void
    {
        $mock = new MockHandler([new Response(
            200,
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                'id' => 'subscription-123',
                'event' => 'job-failed',
                'filters' => [['field' => 'project.id', 'value' => '123', 'operator' => '==']],
                'recipient' => ['channel' => 'email', 'address' => 'a@example.com'],
            ]),
        )]);
        $client = $this->mockClient($mock);

        $result = $client->getSubscription('subscription-123');

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://example.com/project-subscriptions/subscription-123', (string) $request->getUri());
        self::assertSame('testToken', $request->getHeaderLine('X-StorageApi-Token'));

        self::assertSame('subscription-123', $result->getId());
        self::assertSame('job-failed', $result->getEvent());
        self::assertSame('project.id', $result->getFilters()[0]->getField());
        self::assertSame('123', $result->getFilters()[0]->getValue());
        self::assertSame('==', $result->getFilters()[0]->getOperator());
        $recipient = $result->getRecipient();
        self::assertInstanceOf(ResponseEmailRecipient::class, $recipient);
        self::assertSame('a@example.com', $recipient->getAddress());
    }

    public function testListSubscriptions(): void
    {
        $mock = new MockHandler([new Response(
            200,
            ['Content-Type' => 'application/json'],
            (string) json_encode([
                [
                    'id' => 'sub-1',
                    'event' => 'job-failed',
                    'filters' => [['field' => 'project.id', 'value' => '123', 'operator' => '==']],
                    'recipient' => ['channel' => 'email', 'address' => 'a@example.com'],
                ],
                [
                    'id' => 'sub-2',
                    'event' => 'job-succeeded',
                    'filters' => [],
                    'recipient' => ['channel' => 'email', 'address' => 'b@example.com'],
                ],
            ]),
        )]);
        $client = $this->mockClient($mock);

        $result = $client->listSubscriptions();

        $request = $mock->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://example.com/project-subscriptions', (string) $request->getUri());
        self::assertSame('testToken', $request->getHeaderLine('X-StorageApi-Token'));

        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(ResponseSubscription::class, $result);
        self::assertSame('sub-1', $result[0]->getId());
        self::assertSame('sub-2', $result[1]->getId());
    }

    public function testListAndDeleteSubscriptionLifecycle(): void
    {
        $client = $this->getClient();

        $created = $client->createSubscription(new Subscription(
            'job-failed',
            new EmailRecipient('ajda-2680@example.com'),
            [new Filter('project.id', (string) getenv('TEST_STORAGE_API_PROJECT_ID'))],
        ));
        self::assertNotEmpty($created->getId());

        $fetched = $client->getSubscription($created->getId());
        self::assertSame($created->getId(), $fetched->getId());

        $beforeDelete = $client->listSubscriptions();
        $ids = array_map(fn(ResponseSubscription $s): string => $s->getId(), $beforeDelete);
        self::assertContains($created->getId(), $ids);

        $client->deleteSubscription($created->getId());

        $afterDelete = $client->listSubscriptions();
        $idsAfter = array_map(fn(ResponseSubscription $s): string => $s->getId(), $afterDelete);
        self::assertNotContains($created->getId(), $idsAfter);
    }
}
