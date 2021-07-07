<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses;

use Keboola\NotificationClient\Exception\ClientException;
use Throwable;

class Subscription
{
    private string $id;
    private string $event;
    /** @var array<Filter> */
    private array $filters;
    /** @var string */
    private $recipientAddress;
    /** @var string */
    private $recipientChannel;

    public function __construct(array $data)
    {
        try {
            $this->id = $data['id'];
            $this->event = $data['event'];
            $this->filters = array_values(array_map(
                fn(array $item) => new Filter($item),
                $data['filters']
            ));
            $this->recipientChannel = $data['recipient']['channel'];
            $this->recipientAddress = $data['recipient']['address'];
        } catch (Throwable $e) {
            throw new ClientException('Unrecognized response: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return array<Filter>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getRecipientAddress(): string
    {
        return $this->recipientAddress;
    }

    public function getRecipientChannel(): string
    {
        return $this->recipientChannel;
    }
}
