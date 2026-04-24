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
use Keboola\NotificationClient\Responses\Subscription as ResponseSubscription;
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
            ],
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
            ],
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
            ],
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
            'phase-job-succeeded, phase-job-succeeded-with-warning, phase-job-processing-long".',
        );
        $client->createSubscription(new Subscription(
            'dummy-event',
            new EmailRecipient('johnDoe@example.com'),
            [new Filter('projectId', (string) getenv('TEST_STORAGE_API_PROJECT_ID'))],
        ));
    }

    public function testCreateSubscriptionHeaders(): void
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
        $client = new SubscriptionClient(
            'https://example.com/',
            'testToken',
            ['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test'],
        );
        $client->createSubscription(new Subscription(
            'job-failed',
            new EmailRecipient('john.doe@example.com'),
            [
                new Filter('key', 'value'),
            ],
        ));

        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('POST', $request->getMethod());
        self::assertSame(
            ['Content-Length', 'User-Agent', 'X-StorageApi-Token', 'Host', 'Content-type'],
            array_keys($request->getHeaders()),
        );
        self::assertSame(['application/json'], $request->getHeaders()['Content-type']);
    }

    public function testDeleteSubscriptionHeaders(): void
    {
        $mock = new MockHandler([
            new Response(204, [], ''),
        ]);
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new SubscriptionClient(
            'https://example.com/',
            'testToken',
            ['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test'],
        );

        $client->deleteSubscription('subscription-123');

        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame('https://example.com/project-subscriptions/subscription-123', (string) $request->getUri());
        self::assertSame(
            ['User-Agent', 'X-StorageApi-Token', 'Host'],
            array_keys($request->getHeaders()),
        );
    }

    public function testDetailSubscriptionHeaders(): void
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                (string) json_encode([
                    'id' => 'subscription-123',
                    'event' => 'job-failed',
                    'filters' => [
                        ['field' => 'project.id', 'value' => '123'],
                    ],
                    'recipient' => ['channel' => 'email', 'address' => 'a@example.com'],
                ]),
            ),
        ]);
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new SubscriptionClient(
            'https://example.com/',
            'testToken',
            ['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test'],
        );

        $result = $client->detailSubscription('subscription-123');

        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://example.com/project-subscriptions/subscription-123', (string) $request->getUri());
        self::assertSame(
            ['User-Agent', 'X-StorageApi-Token', 'Host'],
            array_keys($request->getHeaders()),
        );

        self::assertSame('subscription-123', $result->getId());
        self::assertSame('job-failed', $result->getEvent());
        self::assertSame('project.id', $result->getFilters()[0]->getField());
        self::assertSame('123', $result->getFilters()[0]->getValue());
        self::assertSame('email', $result->getRecipientChannel());
        self::assertSame('a@example.com', $result->getRecipientAddress());
    }

    public function testListSubscriptionsHeaders(): void
    {
        $responseBody = json_encode([
            [
                'id' => 'sub-1',
                'event' => 'job-failed',
                'filters' => [
                    ['field' => 'project.id', 'value' => '123'],
                ],
                'recipient' => ['channel' => 'email', 'address' => 'a@example.com'],
            ],
            [
                'id' => 'sub-2',
                'event' => 'job-succeeded',
                'filters' => [],
                'recipient' => ['channel' => 'email', 'address' => 'b@example.com'],
            ],
        ]);

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], (string) $responseBody),
        ]);
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $client = new SubscriptionClient(
            'https://example.com/',
            'testToken',
            ['handler' => $stack, 'backoffMaxTries' => 3, 'userAgent' => 'Test'],
        );

        $result = $client->listSubscriptions();

        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://example.com/project-subscriptions', (string) $request->getUri());
        self::assertSame(
            ['User-Agent', 'X-StorageApi-Token', 'Host'],
            array_keys($request->getHeaders()),
        );

        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(
            ResponseSubscription::class,
            $result,
        );
        self::assertSame('sub-1', $result[0]->getId());
        self::assertSame('job-failed', $result[0]->getEvent());
        self::assertSame('project.id', $result[0]->getFilters()[0]->getField());
        self::assertSame('123', $result[0]->getFilters()[0]->getValue());
        self::assertSame('email', $result[0]->getRecipientChannel());
        self::assertSame('a@example.com', $result[0]->getRecipientAddress());
        self::assertSame('sub-2', $result[1]->getId());
    }

    public function testListAndDeleteSubscriptionLifecycle(): void
    {
        $client = $this->getClient();

        // create
        $created = $client->createSubscription(new Subscription(
            'job-failed',
            new EmailRecipient('ajda-2680@example.com'),
            [
                new Filter('project.id', (string) getenv('TEST_STORAGE_API_PROJECT_ID')),
            ],
        ));
        self::assertNotEmpty($created->getId());

        // detail — must return the same subscription
        $detail = $client->detailSubscription($created->getId());
        self::assertSame($created->getId(), $detail->getId());
        self::assertSame('job-failed', $detail->getEvent());

        // list — must contain the new subscription
        $beforeDelete = $client->listSubscriptions();
        self::assertContainsOnlyInstancesOf(
            ResponseSubscription::class,
            $beforeDelete,
        );
        $ids = array_map(
            fn(ResponseSubscription $s): string => $s->getId(),
            $beforeDelete,
        );
        self::assertContains($created->getId(), $ids);

        // delete
        $client->deleteSubscription($created->getId());

        // list — must no longer contain it
        $afterDelete = $client->listSubscriptions();
        $idsAfter = array_map(
            fn(ResponseSubscription $s): string => $s->getId(),
            $afterDelete,
        );
        self::assertNotContains($created->getId(), $idsAfter);
    }
}
