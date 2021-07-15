<?php

namespace Keboola\NotificationClient\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Keboola\NotificationClient\ClientFactory;
use Keboola\NotificationClient\EventsClient;
use Keboola\NotificationClient\SubscriptionClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class ClientFactoryTest extends TestCase
{
    public function getEventsClient(): void
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '{
                        "api": "storage",
                        "version": "v2",
                        "services": [
                            {
                                "id": "gooddata-provisioning",
                                "url": "https://gooddata-provisioning.keboola.com"
                            },
                            {
                                "id": "notification",
                                "url": "https://notificiation.keboola.com"
                            },
                            {
                                "id": "encryption",
                                "url": "https://encryption.keboola.com"
                            }
                        ],
                        "features": [
                            "use-different-stack-payg-wizard",
                            "use-payg-wizard-instead-of-registration"
                        ],
                    }'
            ),
        ]);
        $requestHistory = [];
        $history = Middleware::history($requestHistory);
        $stack = HandlerStack::create($mock);
        $stack->push($history);
        $logger = new TestLogger();

        $clientFactory = new ClientFactory(
            'https://dummy',
            ['handler' => $stack, 'logger' => $logger, 'userAgent' => 'test agent']
        );

        /** @var Request $request */
        $request = $requestHistory[0]['request'];
        self::assertEquals('test agent', $request->getHeader('User-Agent')[0]);
        self::assertTrue($logger->hasInfoThatContains('"GET  /1.1" 200 '));
        self::assertTrue($logger->hasInfoThatContains('test agent'));
        self::assertInstanceOf(SubscriptionClient::class, $clientFactory->getSubscriptionClient('dummy'));
        self::assertInstanceOf(EventsClient::class, $clientFactory->getEventsClient('dummy'));
    }
}
