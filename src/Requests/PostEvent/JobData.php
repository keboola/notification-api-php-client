<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use JsonSerializable;

class JobData implements JsonSerializable
{
    private string $jobId;
    private string $jobUrl;
    private string $jobStartTime;
    private string $jobEndTime;
    private string $componentId;
    private string $componentName;
    private ?string $configurationId;
    private ?string $configurationName;

    public function __construct(
        string $jobId,
        string $jobUrl,
        string $jobStartTime,
        string $jobEndTime,
        string $componentId,
        string $componentName,
        ?string $configurationId,
        ?string $configurationName
    ) {
        $this->jobId = $jobId;
        $this->jobUrl = $jobUrl;
        $this->jobStartTime = $jobStartTime;
        $this->jobEndTime = $jobEndTime;
        $this->componentId = $componentId;
        $this->componentName = $componentName;
        $this->configurationId = $configurationId;
        $this->configurationName = $configurationName;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        $result = [
            'id' => $this->jobId,
            'url' => $this->jobUrl,
            'startTime' => $this->jobStartTime,
            'endTime' => $this->jobEndTime,
            'component' => [
                'id' => $this->componentId,
                'name' => $this->componentName,
            ],
            'tasks' => [],
        ];
        if ($this->configurationId && $this->configurationName) {
            $result['configuration'] = [
                'id' => $this->configurationId,
                'name' => $this->configurationName,
            ];
        }
        return $result;
    }
}
