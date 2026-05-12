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
        $channel = self::extractString($data, 'channel');
        switch ($channel) {
            case EmailRecipient::CHANNEL:
                return new EmailRecipient(self::extractString($data, 'address'));
            case WebhookRecipient::CHANNEL:
                return new WebhookRecipient(self::extractString($data, 'url'));
            default:
                throw new ClientException(sprintf('Unknown recipient channel "%s"', $channel));
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function extractString(array $data, string $key): string
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ClientException(sprintf('Recipient field "%s" must be a string', $key));
        }
        return $data[$key];
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
}
