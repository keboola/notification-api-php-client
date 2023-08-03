<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use Keboola\NotificationClient\Requests\EventDataInterface;

class JobSucceededEventData implements EventDataInterface
{
    private string $projectId;
    private string $projectName;
    private string $branchId;
    private JobData $jobData;

    public function __construct(
        string $projectId,
        string $projectName,
        string $branchId,
        JobData $jobData
    ) {
        $this->projectId = $projectId;
        $this->projectName = $projectName;
        $this->branchId = $branchId;
        $this->jobData = $jobData;
    }

    public function jsonSerialize(): array
    {
        return [
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
        return 'job-succeeded';
    }
}
