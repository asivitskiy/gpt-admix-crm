<?php
declare(strict_types=1);

/*
 * ADMIX CRM: упрощённая модульная архитектура (НЕ full MVC)
 * --------------------------------------------------------
 * Точка входа: public/index.php
 * Роутинг: по GET параметру m -> modules/<m>/page.php
 * Макет: views/layout.php
 * Виджеты (модалки/переисп. UI): views/partials/widgets + public/assets/widgets
 * API: public/api/*.php (JSON)
 *
 * ВАЖНО: не добавлять параллельные "контроллерные" структуры (app/Controllers, service layers и т.п.)
 * чтобы не возникала путаница и дублирование.
 */

require __DIR__ . '/../inc/bootstrap.php';

use App\Core\Helpers;

// guard
if (empty($gpt_user)) {
    header('Location: login.php');
    exit;
}

// если пароль временный (первый вход / сброс) — сначала заставляем установить новый
if (!empty($gpt_user['must_set_password']) || !empty($_SESSION['force_pw_change'])) {
    header('Location: set_password.php');
    exit;
}

// модуль
$m = isset($_GET['m']) ? (string)$_GET['m'] : 'home';
$m = trim($m);

// белый список
$routes = [
    'order_new' => __DIR__ . '/../modules/order_new/page.php',
    'home'      => __DIR__ . '/../modules/home/page.php',
    'orders'    => __DIR__ . '/../modules/orders/page.php',
    'schedule'  => __DIR__ . '/../modules/schedule/page.php',
    'cash'      => __DIR__ . '/../modules/cash/page.php',
    'clients'   => __DIR__ . '/../modules/clients/page.php',
    'suppliers' => __DIR__ . '/../modules/suppliers/page.php',
    'messages'  => __DIR__ . '/../modules/messages/page.php',
    'materials' => __DIR__ . '/../modules/materials/page.php',
    'admin'     => __DIR__ . '/../modules/admin/page.php',
    'profile'   => __DIR__ . '/../modules/profile/page.php',
];


if (!isset($routes[$m])) {
    $m = 'home';
}

$nav = [
    ['key'=>'order_new', 'title' => 'Новый заказ', 'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>', 'href' => 'index.php?m=order_new', 'active' => ($m==='order_new')],
    ['key'=>'home',      'title' => 'Главная',        'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 3l9 8h-3v9h-5v-6H11v6H6v-9H3l9-8z"/></svg>',      'href' => 'index.php?m=home',      'active' => ($m==='home')],
    ['key'=>'orders',    'title' => 'Заказы',         'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M7 3h10v2H7V3zm-2 4h14v2H5V7zm0 4h14v2H5v-2zm0 4h10v2H5v-2z"/></svg>',    'href' => 'index.php?m=orders',    'active' => ($m==='orders')],
    ['key'=>'schedule',  'title' => 'График работ',   'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M7 2h2v2h6V2h2v2h3v18H4V4h3V2zm13 6H4v12h16V8z"/></svg>',  'href' => 'index.php?m=schedule',  'active' => ($m==='schedule')],
    ['key'=>'cash',      'title' => 'Касса',          'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M3 7h18v10H3V7zm2 2v6h14V9H5zm2 1h4v4H7v-4z"/></svg>',      'href' => 'index.php?m=cash',      'active' => ($m==='cash')],
    ['key'=>'clients',   'title' => 'Клиенты',        'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M16 11c1.66 0 3-1.34 3-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.98 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',   'href' => 'index.php?m=clients',   'active' => ($m==='clients')],
    ['key'=>'suppliers', 'title' => 'Поставщики',     'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M3 6h13v10H3V6zm14 4h3l1 2v4h-4v-6zm-1 7a2 2 0 11.001 3.999A2 2 0 0116 17zm-9 0a2 2 0 11.001 3.999A2 2 0 017 17z"/></svg>', 'href' => 'index.php?m=suppliers', 'active' => ($m==='suppliers')],
    ['key'=>'messages',  'title' => 'Сообщения',      'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M4 4h16v12H7l-3 3V4zm2 2v8h12V6H6z"/></svg>',  'href' => 'index.php?m=messages',  'active' => ($m==='messages')],
    ['key'=>'materials', 'title' => 'Материалы',      'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M21 8l-9-5-9 5 9 5 9-5zm-9 7l-9-5v10l9 5 9-5V10l-9 5z"/></svg>', 'href' => 'index.php?m=materials', 'active' => ($m==='materials')],
    ['key'=>'admin',     'title' => 'Админпанель',    'icon' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 2l8 4v6c0 5-3.4 9.4-8 10-4.6-.6-8-5-8-10V6l8-4zm0 6a3 3 0 00-3 3v2H8v2h8v-2h-1v-2a3 3 0 00-3-3zm-1 3a1 1 0 112 0v2h-2v-2z"/></svg>',     'href' => 'index.php?m=admin',     'active' => ($m==='admin')],
];


// контекст для модулей
$pdo   = $gpt_pdo;
$user  = $gpt_user;
$theme = $gpt_theme;

// модуль устанавливает: $title, $subtitle, $content
$title = '';
$subtitle = '';
$content = '';
// optional: modules may inject html into top-right area (e.g. action buttons)
$topRightHtml = '';

require $routes[$m];

// рендерим layout (без View::render)
$gpt_view_vars = [
    'title' => $title,
    'subtitle' => $subtitle,
    'content' => $content,
    'nav' => $nav,
    'user' => $user,
    'theme' => $theme,
    'topRightHtml' => $topRightHtml,
];
extract($gpt_view_vars, EXTR_SKIP);
ob_start();
include __DIR__ . '/../views/layout.php';
echo ob_get_clean();
