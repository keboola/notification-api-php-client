<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use Keboola\NotificationClient\Requests\EventDataInterface;

class JobProcessingLongEventData implements EventDataInterface
{
    private string $projectId;
    private string $projectName;
    private float $durationOvertimePercentage;
    private float $averageDuration;
    private float $currentDuration;
    private JobData $jobData;

    public function __construct(
        string $projectId,
        string $projectName,
        float $durationOvertimePercentage,
        float $averageDuration,
        float $currentDuration,
        JobData $jobData
    ) {
        $this->projectId = $projectId;
        $this->projectName = $projectName;
        $this->durationOvertimePercentage = $durationOvertimePercentage;
        $this->jobData = $jobData;
        $this->averageDuration = $averageDuration;
        $this->currentDuration = $currentDuration;
    }

    public function jsonSerialize(): array
    {
        return [
            'averageDuration' => $this->averageDuration,
            'currentDuration' => $this->currentDuration,
            'durationOvertimePercentage' => $this->durationOvertimePercentage,
            'job' => $this->jobData->jsonSerialize(),
            'project' => [
                'id' => $this->projectId,
                'name' => $this->projectName,
            ],
            'uniqueId' => $this->jobData->getJobId(),
        ];
    }

    public static function getEventTypeName(): string
    {
        return 'job-processing-long';
    }
}
