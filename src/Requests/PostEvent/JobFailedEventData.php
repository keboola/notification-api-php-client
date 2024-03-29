<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use Keboola\NotificationClient\Requests\EventDataInterface;

class JobFailedEventData implements EventDataInterface
{
    private string $projectId;
    private string $projectName;
    private string $branchId;
    private string $errorMessage;
    private JobData $jobData;

    public function __construct(
        string $projectId,
        string $projectName,
        string $branchId,
        string $errorMessage,
        JobData $jobData
    ) {
        $this->projectId = $projectId;
        $this->projectName = $projectName;
        $this->branchId = $branchId;
        $this->errorMessage = $errorMessage;
        $this->jobData = $jobData;
    }

    public function jsonSerialize(): array
    {
        return [
            'errorMessage' => $this->errorMessage,
            'job' => $this->jobData->jsonSerialize(),
            'project' => [
                'id' => $this->projectId,
                'name' => $this->projectName,
            ],
            'branch' => [
                'id' => $this->branchId,
            ],
        ];
    }

    public static function getEventTypeName(): string
    {
        return 'job-failed';
    }
}
