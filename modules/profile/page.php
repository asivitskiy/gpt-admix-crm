<?php
declare(strict_types=1);

// Ожидает: $pdo (PDO), $user (array|null), $theme (string)

if (empty($user)) {
    header('Location: login.php');
    exit;
}

$action = isset($_GET['action']) ? (string)$_GET['action'] : '';

if ($action === 'theme' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $newTheme = isset($_POST['theme']) ? (string)$_POST['theme'] : 'dark';
    if (!in_array($newTheme, ['dark','light','auto'], true)) $newTheme = 'dark';

    $st = $pdo->prepare("UPDATE users SET theme=? WHERE id=? LIMIT 1");
    $st->execute([$newTheme, (int)$user['id']]);

    $_SESSION['theme'] = $newTheme;

    header('Location: index.php?m=profile');
    exit;
}

$title = 'Личный кабинет';
$subtitle = 'Настройки пользователя';

$content = \App\Core\View::render(__DIR__ . '/views/me.php', [
    'user' => $user,
    'theme' => $theme ?? ($_SESSION['theme'] ?? 'dark'),
]);
