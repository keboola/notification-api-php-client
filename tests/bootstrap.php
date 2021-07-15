<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$requiredEnvs = ['TEST_NOTIFICATION_API_URL', 'TEST_STORAGE_API_TOKEN', 'TEST_MANAGE_API_APPLICATION_TOKEN',
    'TEST_STORAGE_API_URL', 'TEST_STORAGE_API_PROJECT_ID'];
foreach ($requiredEnvs as $env) {
    if (empty(getenv($env))) {
        throw new Exception(sprintf('Environment variable "%s" is empty', $env));
    }
}
