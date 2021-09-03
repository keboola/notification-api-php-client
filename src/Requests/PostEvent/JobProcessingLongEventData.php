<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use Keboola\NotificationClient\Requests\EventDataInterface;

class JobProcessingLongEventData implements EventDataInterface
{
    private string $projectId;
    private string $projectName;
    private float $prolongation;
    private float $averageDuration;
    private int $currentDuration;
    private JobData $jobData;

    public function __construct(
        string  $projectId,
        string  $projectName,
        float   $prolongation,
        float   $averageDuration,
        int   $currentDuration,
        JobData $jobData
    ) {
        $this->projectId = $projectId;
        $this->projectName = $projectName;
        $this->prolongation = $prolongation;
        $this->jobData = $jobData;
        $this->averageDuration = $averageDuration;
        $this->currentDuration = $currentDuration;
    }

    public function jsonSerialize(): array
    {
        return [
            'averageDuration' => $this->averageDuration,
            'currentDuration' => $this->currentDuration,
            'durationOvertimePercentage' => $this->prolongation,
            'job' => $this->jobData->jsonSerialize(),
            'project' => [
                'id' => $this->projectId,
                'name' => $this->projectName,
            ],
        ];
    }

    public static function getEventTypeName(): string
    {
        return 'job-processing-long';
    }
}
