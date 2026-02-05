<?php
declare(strict_types=1);

namespace App\Core;

final class Helpers
{
    public static function loadEnv(string $path): void
    {
        if (!is_file($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;

            $pos = strpos($line, '=');
            if ($pos === false) continue;

            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1));

            if ((strlen($val) >= 2) && (
                ($val[0] === '"' && substr($val, -1) === '"') ||
                ($val[0] === "'" && substr($val, -1) === "'")
            )) {
                $val = substr($val, 1, -1);
            }

            if (getenv($key) === false) {
                putenv($key.'='.$val);
                $_ENV[$key] = $val;
            }
        }
    }

    public static function h(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
