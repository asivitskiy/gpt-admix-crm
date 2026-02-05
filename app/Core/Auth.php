<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function id(): int
    {
        return (int)($_SESSION['uid'] ?? 0);
    }

    public static function logged(): bool
    {
        return self::id() > 0;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function attempt(PDO $pdo, string $login, string $password): bool
    {
        $login = trim($login);
        if ($login === '' || $password === '') return false;

        $st = $pdo->prepare("SELECT id, password_hash, is_active, theme FROM users WHERE login=? LIMIT 1");
        $st->execute([$login]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        if (!$u) return false;
        if ((int)$u['is_active'] !== 1) return false;

        if (!password_verify($password, (string)$u['password_hash'])) return false;

        $_SESSION['uid'] = (int)$u['id'];
        $_SESSION['theme'] = $u['theme'] ?? 'dark';
        return true;
    }

    public static function current(PDO $pdo): ?array
    {
        $uid = self::id();
        if ($uid <= 0) return null;

        $st = $pdo->prepare("SELECT id, name, login, role, theme, is_active FROM users WHERE id=? LIMIT 1");
        $st->execute([$uid]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        if (!$u) return null;
        if ((int)$u['is_active'] !== 1) return null;

        return $u;
    }
}
