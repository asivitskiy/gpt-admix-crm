<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(array $db): PDO
    {
        if (self::$pdo) return self::$pdo;

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $db['host'], (int)$db['port'], $db['name'], $db['charset']
        );

        $pdo = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        self::$pdo = $pdo;
        return self::$pdo;
    }
}
