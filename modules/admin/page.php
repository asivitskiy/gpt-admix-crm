<?php
declare(strict_types=1);

// Ожидает: $pdo (PDO), $user (array|null)

$is_admin = !empty($user) && (($user['role'] ?? '') === 'admin');

// простая флешка для токенов/сообщений
if (!isset($_SESSION['admin_flash'])) {
    $_SESSION['admin_flash'] = [];
}

// обработка действий на странице пользователей
if ($is_admin && isset($_GET['p']) && (string)$_GET['p'] === 'users' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = isset($_POST['act']) ? (string)$_POST['act'] : '';

    // генератор токена (для первого входа / сброса пароля)
    $gen_token = function(int $len = 10): string {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // без 0/O/I/1
        $out = '';
        for ($i=0; $i<$len; $i++) {
            $out .= $alphabet[random_int(0, strlen($alphabet)-1)];
        }
        return $out;
    };

    $allowed_roles = ['admin','manager','printer','preprinter','handwork','storage'];

    try {
        if ($act === 'create_user') {
            $name   = trim((string)($_POST['name'] ?? ''));
            $letter = trim((string)($_POST['letter'] ?? ''));
            $login  = trim((string)($_POST['login'] ?? ''));
            $role   = (string)($_POST['role'] ?? 'manager');
            $theme  = (string)($_POST['theme'] ?? 'dark');
            $theme  = ($theme === 'light') ? 'light' : 'dark';

            if ($name === '' || $login === '') {
                throw new \RuntimeException('Имя и логин обязательны');
            }
            if (!in_array($role, $allowed_roles, true)) {
                throw new \RuntimeException('Неверная роль');
            }

            // проверка уникальности логина
            $st = $pdo->prepare('SELECT id FROM users WHERE login=? LIMIT 1');
            $st->execute([$login]);
            if ($st->fetchColumn()) {
                throw new \RuntimeException('Такой логин уже существует');
            }

            $token = $gen_token(10);
            $hash  = password_hash($token, PASSWORD_DEFAULT);

            $ins = $pdo->prepare('INSERT INTO users(name, letter, login, password_hash, must_set_password, role, theme, is_active) VALUES(?,?,?,?,1,?,?,1)');
            $ins->execute([
                $name,
                ($letter !== '' ? $letter : null),
                $login,
                $hash,
                $role,
                $theme,
            ]);

            $_SESSION['admin_flash'] = [
                'type' => 'ok',
                'msg'  => 'Пользователь создан. Токен для первого входа:',
                'token'=> $token,
                'login'=> $login,
            ];
        }

        if ($act === 'toggle_active') {
            $uid = (int)($_POST['uid'] ?? 0);
            $to  = (int)($_POST['to'] ?? 0);
            if ($uid <= 0) throw new \RuntimeException('Неверный UID');
            $to = ($to === 1) ? 1 : 0;

            $upd = $pdo->prepare('UPDATE users SET is_active=? WHERE id=? LIMIT 1');
            $upd->execute([$to, $uid]);

            $_SESSION['admin_flash'] = [
                'type' => 'ok',
                'msg'  => $to ? 'Пользователь активирован' : 'Пользователь деактивирован',
            ];
        }

        if ($act === 'change_role') {
    $uid  = (int)($_POST['uid'] ?? 0);
    $role = (string)($_POST['role'] ?? '');
    if ($uid <= 0) throw new \RuntimeException('Неверный UID');
    if (!in_array($role, $allowed_roles, true)) throw new \RuntimeException('Неверная роль');

    $upd = $pdo->prepare('UPDATE users SET role=? WHERE id=? LIMIT 1');
    $upd->execute([$role, $uid]);

    $_SESSION['admin_flash'] = [
        'type' => 'ok',
        'msg'  => 'Роль пользователя обновлена',
    ];
}

if ($act === 'reset_password') {
            $uid = (int)($_POST['uid'] ?? 0);
            if ($uid <= 0) throw new \RuntimeException('Неверный UID');

            $token = $gen_token(10);
            $hash  = password_hash($token, PASSWORD_DEFAULT);

            $upd = $pdo->prepare('UPDATE users SET password_hash=?, must_set_password=1 WHERE id=? LIMIT 1');
            $upd->execute([$hash, $uid]);

            // получим логин для удобства
            $st = $pdo->prepare('SELECT login FROM users WHERE id=? LIMIT 1');
            $st->execute([$uid]);
            $login = (string)($st->fetchColumn() ?: '');

            $_SESSION['admin_flash'] = [
                'type' => 'ok',
                'msg'  => 'Пароль сброшен. Токен для входа:',
                'token'=> $token,
                'login'=> $login,
            ];
        }
    } catch (\Throwable $e) {
        $_SESSION['admin_flash'] = [
            'type' => 'err',
            'msg'  => $e->getMessage(),
        ];
    }

    header('Location: index.php?m=admin&p=users');
    exit;
}

$sub = isset($_GET['p']) ? (string)$_GET['p'] : '';

// test button for contragents constructor (will be removed later)
$topRightHtml = '<button class="btn" style="width:auto" type="button" onclick="window.GPT_Contragents && window.GPT_Contragents.open()">Конструктор контрагентов</button>';

if ($sub === 'users') {
    $title = 'Админка';
    $subtitle = 'Пользователи';

    $users = [];
    if ($is_admin) {
        $st = $pdo->query("SELECT id, name, letter, login, role, is_active, must_set_password, created_at FROM users ORDER BY id DESC");
        $users = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    $flash = $_SESSION['admin_flash'] ?? [];
    $_SESSION['admin_flash'] = [];

    // Рендер центральной части (без View::render)
    $gpt_view_vars = [
        'users' => $users,
        'is_admin' => $is_admin,
        'flash' => $flash,
    ];
    extract($gpt_view_vars, EXTR_SKIP);
    ob_start();
    include __DIR__ . '/views/users.php';
    $content = ob_get_clean();
} else {
    $title = 'Админка';
    $subtitle = '';

    // Рендер центральной части (без View::render)
    $gpt_view_vars = [
        'is_admin' => $is_admin,
    ];
    extract($gpt_view_vars, EXTR_SKIP);
    ob_start();
    include __DIR__ . '/views/index.php';
    $content = ob_get_clean();
}
