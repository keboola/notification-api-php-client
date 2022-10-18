<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotEnv = new Dotenv();
$dotEnv->usePutenv();
$dotEnv->bootEnv(dirname(__DIR__).'/.env', 'dev', []);

$requiredEnvs = ['TEST_NOTIFICATION_API_URL', 'TEST_STORAGE_API_TOKEN', 'TEST_MANAGE_API_APPLICATION_TOKEN',
    'STORAGE_API_URL', 'TEST_STORAGE_API_PROJECT_ID'];
foreach ($requiredEnvs as $env) {
    if (empty(getenv($env))) {
        throw new Exception(sprintf('Environment variable "%s" is empty', $env));
    }
}
