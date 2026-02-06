<?php
declare(strict_types=1);

require __DIR__ . '/../inc/bootstrap.php';

use App\Core\View;

if (empty($gpt_user)) {
    header('Location: login.php');
    exit;
}

// If password change isn't required, go home
if (empty($gpt_user['must_set_password']) && empty($_SESSION['force_pw_change'])) {
    header('Location: index.php?m=home');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p1 = isset($_POST['password1']) ? (string)$_POST['password1'] : '';
    $p2 = isset($_POST['password2']) ? (string)$_POST['password2'] : '';

    if (strlen($p1) < 6) {
        $error = 'Пароль должен быть минимум 6 символов';
    } elseif ($p1 !== $p2) {
        $error = 'Пароли не совпадают';
    } else {
        $hash = password_hash($p1, PASSWORD_DEFAULT);
        $st = $gpt_pdo->prepare("UPDATE users SET password_hash=?, must_set_password=0 WHERE id=? LIMIT 1");
        $st->execute([$hash, (int)$gpt_user['id']]);

        $_SESSION['force_pw_change'] = 0;
        header('Location: index.php?m=home');
        exit;
    }
}

echo View::render(__DIR__ . '/../views/auth/set_password.php', [
    'title' => 'Установка пароля',
    'error' => $error,
    'success' => $success,
    'user' => $gpt_user,
]);
