<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use Keboola\NotificationClient\Requests\EventDataInterface;

class JobSucceededWithWarningEventData implements EventDataInterface
{
    private string $projectId;
    private string $projectName;
    private string $errorMessage;
    private JobData $jobData;

    public function __construct(string $projectId, string $projectName, string $errorMessage, JobData $jobData)
    {
        $this->projectId = $projectId;
        $this->projectName = $projectName;
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
            'job' => $this->jobData->jsonSerialize(),
            'project' => [
                'id' => $this->projectId,
                'name' => $this->projectName,
            ],
        ];
    }

    public static function getEventTypeName(): string
    {
        return 'job-succeeded-with-warning';
    }
}
