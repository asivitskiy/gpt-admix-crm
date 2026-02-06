<?php
declare(strict_types=1);

require __DIR__ . '/../inc/bootstrap.php';

use App\Core\Auth;
use App\Core\View;

if (!empty($gpt_user)) {
    header('Location: index.php?m=home');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = isset($_POST['login']) ? (string)$_POST['login'] : '';
    $pass  = isset($_POST['password']) ? (string)$_POST['password'] : '';

    if (Auth::attempt($gpt_pdo, $login, $pass)) {
        if (!empty($_SESSION['force_pw_change'])) {
            header('Location: set_password.php');
        } else {
            header('Location: index.php?m=home');
        }
        exit;
    }
    $error = 'Неверный логин или пароль';
}

echo View::render(__DIR__ . '/../views/auth/login.php', [
    'title' => 'Вход',
    'error' => $error,
]);
