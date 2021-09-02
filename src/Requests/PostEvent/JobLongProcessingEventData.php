<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use Keboola\NotificationClient\Requests\EventDataInterface;

class JobLongProcessingEventData implements EventDataInterface
{
    private string $projectId;
    private string $projectName;
    private float $prolongation;
    private JobData $jobData;

    public function __construct(string $projectId, string $projectName, float $prolongation, JobData $jobData)
    {
        $this->projectId = $projectId;
        $this->projectName = $projectName;
        $this->prolongation = $prolongation;
        $this->jobData = $jobData;
    }

    public function jsonSerialize(): array
    {
        return [
            'prolongation' => $this->prolongation,
            'job' => $this->jobData->jsonSerialize(),
            'project' => [
                'id' => $this->projectId,
                'name' => $this->projectName,
            ],
        ];
    }

    public static function getEventTypeName(): string
    {
        return 'job-failed';
    }
}
