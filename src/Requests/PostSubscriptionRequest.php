<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests;

use JsonSerializable;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use Keboola\NotificationClient\Requests\PostSubscription\Filter;

class PostSubscriptionRequest implements JsonSerializable
{
    private const VALID_EVENT_TYPES = ['job_failed'];

    protected string $eventType;
    protected EmailRecipient $recipient;
    /** @var array<Filter> */
    protected array $filters;

    /**
     * PostSubscriptionRequest constructor.
     * @param string $eventType
     * @param EmailRecipient $recipient
     * @param array<Filter> $filters
     */
    public function __construct(string $eventType, EmailRecipient $recipient, array $filters)
    {
        if (!in_array($eventType, self::VALID_EVENT_TYPES)) {
            throw new ClientException(sprintf(
                'Invalid event type "%s", valid types are: "%s".',
                $eventType,
                implode(', ', self::VALID_EVENT_TYPES)
            ));
        }
        $this->eventType = $eventType;
        $this->recipient = $recipient;
        $this->filters = $filters;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        $filters = array_map(
            function (Filter $v) {
                return $v->jsonSerialize();
            },
            $this->filters
        );
        return [
            'event' => $this->eventType,
            'filters' => $filters,
            'recipient' => $this->recipient->jsonSerialize(),
        ];
    }
}
