<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use Keboola\NotificationClient\Requests\EventDataInterface;

class PhaseJobFailedEventData implements EventDataInterface
{
    private string $projectId;
    private string $projectName;
    private string $errorMessage;
    private JobData $jobData;
    private string $phaseName;

    public function __construct(
        string $projectId,
        string $projectName,
        string $phaseName,
        string $errorMessage,
        JobData $jobData
    ) {
        $this->projectId = $projectId;
        $this->projectName = $projectName;
        $this->errorMessage = $errorMessage;
        $this->jobData = $jobData;
        $this->phaseName = $phaseName;
    }

    public function jsonSerialize(): array
    {
        return [
            'errorMessage' => $this->errorMessage,
            'job' => $this->jobData->jsonSerialize(),
            'phaseName' => $this->phaseName,
            'project' => [
                'id' => $this->projectId,
                'name' => $this->projectName,
            ],
        ];
    }

    public static function getEventTypeName(): string
    {
        return 'phase-job-failed';
    }
}
