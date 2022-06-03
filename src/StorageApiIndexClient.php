<?php

declare(strict_types=1);

namespace Keboola\NotificationClient;

use GuzzleHttp\Psr7\Request;
use Keboola\NotificationClient\Exception\ClientException;

class StorageApiIndexClient extends Client
{
    public function __construct(string $connectionUrl, array $options = [])
    {
        parent::__construct($connectionUrl, null, $options);
    }

    public function getIndex(): array
    {
        $request = new Request('GET', 'v2/storage?exclude=components', [], '{}');
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

    protected function getTokenHeaderName(): ?string
    {
        return null;
    }
}
