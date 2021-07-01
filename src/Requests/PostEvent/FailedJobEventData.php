<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use JsonSerializable;

class FailedJobEventData implements JsonSerializable
{
    private string $errorMessage;
    private JobData $jobData;

    public function __construct(string $errorMessage, JobData $jobData)
    {
        $this->errorMessage = $errorMessage;
        $this->jobData = $jobData;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'errorMessage' => $this->errorMessage,
            'jobData' => $this->jobData->jsonSerialize(),
        ];
    }
}
