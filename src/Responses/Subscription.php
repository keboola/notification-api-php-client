<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Responses;

use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Responses\Recipient\EmailRecipient;
use Keboola\NotificationClient\Responses\Recipient\RecipientInterface;
use Keboola\NotificationClient\Responses\Recipient\WebhookRecipient;
use Throwable;

class Subscription
{
    private string $id;
    private string $event;
    /** @var array<Filter> */
    private array $filters;
    private RecipientInterface $recipient;

    public function __construct(array $data)
    {
        try {
            $this->id = $data['id'];
            $this->event = $data['event'];
            $this->filters = array_values(array_map(
                fn(array $item) => new Filter($item),
                $data['filters'],
            ));
            $this->recipient = self::createRecipient($data['recipient']);
        } catch (Throwable $e) {
            throw new ClientException('Unrecognized response: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function createRecipient(array $data): RecipientInterface
    {
        $channel = $data['channel'];
        assert(is_string($channel));
        switch ($channel) {
            case EmailRecipient::CHANNEL:
                $address = $data['address'];
                assert(is_string($address));
                return new EmailRecipient($address);
            case WebhookRecipient::CHANNEL:
                $url = $data['url'];
                assert(is_string($url));
                return new WebhookRecipient($url);
            default:
                throw new ClientException(sprintf('Unknown recipient channel "%s"', $channel));
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

    public function getRecipient(): RecipientInterface
    {
        return $this->recipient;
    }

    public function getRecipientChannel(): string
    {
        return $this->recipient->getChannel();
    }

    /**
     * BC: previously `: string`. Now `: ?string`. Returns null for non-email channels (e.g. webhook).
     */
    public function getRecipientAddress(): ?string
    {
        if ($this->recipient instanceof EmailRecipient) {
            return $this->recipient->getAddress();
        }
        return null;
    }
}
