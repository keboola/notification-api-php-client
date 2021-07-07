<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use JsonSerializable;

class JobData implements JsonSerializable
{
    private string $jobEndTime;
    private string $jobStartTime;
    private string $jobUrl;
    private string $jobId;
    private string $projectName;
    private string $orchestrationName;

    public function __construct(
        string $projectName,
        string $jobId,
        string $jobUrl,
        string $jobStartTime,
        string $jobEndTime,
        string $orchestrationName
    ) {
        $this->projectName = $projectName;
        $this->jobId = $jobId;
        $this->jobUrl = $jobUrl;
        $this->jobStartTime = $jobStartTime;
        $this->jobEndTime = $jobEndTime;
        $this->orchestrationName = $orchestrationName;
    }

    public function getProject(): array
    {
        return [
            'name' => $this->projectName,
        ];
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->jobId,
            'url' => $this->jobUrl,
            'startTime' => $this->jobStartTime,
            'endTime' => $this->jobEndTime,
            'orchestrationName' => $this->orchestrationName,
            'tasks' => [],
        ];
    }
}
