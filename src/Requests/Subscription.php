<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests;

use JetBrains\PhpStorm\Internal\TentativeType;
use JsonSerializable;
use Keboola\NotificationClient\ClientFactory;
use Keboola\NotificationClient\Exception\ClientException;
use Keboola\NotificationClient\Requests\PostEvent\JobFailedEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobProcessingLongEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobSucceededEventData;
use Keboola\NotificationClient\Requests\PostEvent\JobSucceededWithWarningEventData;
use Keboola\NotificationClient\Requests\PostEvent\PhaseJobFailedEventData;
use Keboola\NotificationClient\Requests\PostEvent\PhaseJobProcessingLongEventData;
use Keboola\NotificationClient\Requests\PostEvent\PhaseJobSucceededEventData;
use Keboola\NotificationClient\Requests\PostEvent\PhaseJobSucceededWithWarningEventData;
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

    public function jsonSerialize(): array
    {
        $filters = array_values(array_map(
            fn (Filter $v) => $v->jsonSerialize(),
            $this->filters,
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
            JobSucceededEventData::getEventTypeName(),
            JobSucceededWithWarningEventData::getEventTypeName(),
            JobProcessingLongEventData::getEventTypeName(),
            PhaseJobFailedEventData::getEventTypeName(),
            PhaseJobSucceededEventData::getEventTypeName(),
            PhaseJobSucceededWithWarningEventData::getEventTypeName(),
            PhaseJobProcessingLongEventData::getEventTypeName(),
        ];

        if (!in_array($eventType, $validEventTypes)) {
            throw new ClientException(sprintf(
                'Invalid event type "%s", valid types are: "%s".',
                $eventType,
                implode(', ', $validEventTypes),
            ));
        }
    }
}
