<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Keboola\NotificationClient\Exception\ClientException;
use Psr\Log\LoggerInterface;

class StorageApiIndexClient extends Client
{
    protected array $defaultHeaders = [];

    /**
     * @param array{
     *     handler?: HandlerStack,
     *     backoffMaxTries: int<0, 100>,
     *     userAgent: string,
     *     logger?: LoggerInterface
     * } $options
     */
    public function __construct(string $connectionUrl, array $options)
    {
        parent::__construct($connectionUrl, null, $options);
    }

    private function getIndex(): array
    {
        $request = new Request('GET', 'v2/storage?exclude=components');
        return $this->sendRequest($request);
    }

    private function getService(string $serviceName): array
    {
        $index = $this->getIndex();
        if (!isset($index['services']) || !is_array($index['services'])) {
            throw new ClientException('Invalid response from index');
        }
        foreach ($index['services'] as $service) {
            if (isset($service['id']) && isset($service['url']) && ($service['id'] === $serviceName)) {
                return $service;
            }
        }
        throw new ClientException(sprintf('Service "%s" was not found in index.', $serviceName));
    }

    public function getServiceUrl(string $serviceName): string
    {
        return $this->getService($serviceName)['url'];
    }
}
