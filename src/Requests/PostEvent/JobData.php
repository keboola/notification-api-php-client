<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Requests\PostEvent;

use DateTimeInterface;
use JsonSerializable;

class JobData implements JsonSerializable
{
    private string $jobId;
    private string $jobUrl;
    private ?DateTimeInterface $jobStartTime;
    private ?DateTimeInterface $jobEndTime;
    private string $componentId;
    private string $componentName;
    private ?string $configurationId;
    private ?string $configurationName;

    public function __construct(
        string $jobId,
        string $jobUrl,
        ?DateTimeInterface $jobStartTime,
        ?DateTimeInterface $jobEndTime,
        string $componentId,
        string $componentName,
        ?string $configurationId,
        ?string $configurationName,
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

    public function jsonSerialize(): array
    {
        $result = [
            'id' => $this->jobId,
            'url' => $this->jobUrl,
            'component' => [
                'id' => $this->componentId,
                'name' => $this->componentName,
            ],
            'tasks' => [],
        ];
        if (!is_null($this->jobStartTime)) {
            $result['startTime'] = $this->jobStartTime->format(DateTimeInterface::ATOM);
        }
        if (!is_null($this->jobEndTime)) {
            $result['endTime'] = $this->jobEndTime->format(DateTimeInterface::ATOM);
        }

        if ($this->configurationId && $this->configurationName) {
            $result['configuration'] = [
                'id' => $this->configurationId,
                'name' => $this->configurationName,
            ];
        }
        return $result;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }
}
