<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests;

use JsonSerializable;
use Keboola\NotificationClient\ClientFactory;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\PostEvent\JobFailedEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobProcessingLongEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobSucceededWithWarningEventData;
use Keboola\NotificationClient\Requests\PostEvent\PhaseJobFailedEventData;
use Keboola\NotificationClient\Requests\PostSubscription\EmailRecipient;
use Keboola\NotificationClient\Requests\PostSubscription\Filter;

class Subscription implements JsonSerializable
{
    private string $eventType;
    private EmailRecipient $recipient;
    /** @var array<Filter> */
    private array $filters;

    /**
     * PostSubscriptionRequest constructor.
     * @param string $eventType
     * @param EmailRecipient $recipient
     * @param array<Filter> $filters
     */
    public function __construct(string $eventType, EmailRecipient $recipient, array $filters)
    {
        $this->checkEventType($eventType);
        $this->eventType = $eventType;
        $this->recipient = $recipient;
        $this->filters = $filters;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        $filters = array_values(array_map(
            fn (Filter $v) => $v->jsonSerialize(),
            $this->filters
        ));
        return [
            'event' => $this->eventType,
            'filters' => $filters,
            'recipient' => $this->recipient->jsonSerialize(),
        ];
    }

    private function checkEventType(string $eventType): void
    {
        $validEventTypes = [
            JobFailedEventData::getEventTypeName(),
            JobSucceededWithWarningEventData::getEventTypeName(),
            JobProcessingLongEventData::getEventTypeName(),
            PhaseJobFailedEventData::getEventTypeName(),
        ];

        if (!in_array($eventType, $validEventTypes)) {
            throw new ClientException(sprintf(
                'Invalid event type "%s", valid types are: "%s".',
                $eventType,
                implode(', ', $validEventTypes)
            ));
        }
    }
}
