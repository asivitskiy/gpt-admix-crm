<?php
declare(strict_types=1);

namespace App\Core;

final class Config
{
    public static function load(): array
    {
        return [
            'env' => getenv('APP_ENV') ?: 'local',
            'debug' => (getenv('APP_DEBUG') ?: '0') === '1',
            'base_url' => getenv('APP_BASE_URL') ?: '',

            'db' => [
                'host' => getenv('DB_HOST') ?: '127.0.0.1',
                'port' => (int)(getenv('DB_PORT') ?: 3306),
                'name' => getenv('DB_NAME') ?: 'crm',
                'user' => getenv('DB_USER') ?: 'root',
                'pass' => getenv('DB_PASS') ?: '',
                'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
            ],
        ];
    }
}
