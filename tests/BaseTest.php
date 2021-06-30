<?php

declare(strict_types=1);

namespace Keboola\NotificationClient\Tests;

use Exception;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $requiredEnvs = ['TEST_NOTIFICATION_API_URL', 'TEST_STORAGE_API_TOKEN', 'TEST_MANAGE_API_APPLICATION_TOKEN'];
        foreach ($requiredEnvs as $env) {
            if (empty(getenv($env))) {
                throw new Exception(sprintf('Environment variable "%s" is empty', $env));
            }
        }
    }
}
