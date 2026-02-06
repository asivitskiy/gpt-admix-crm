<?php
declare(strict_types=1);

// Composer autoload (vendor packages).
require __DIR__ . '/../vendor/autoload.php';

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

// Local project core (explicit includes; no framework-like structure).
require_once __DIR__ . '/core/Helpers.php';
require_once __DIR__ . '/core/Config.php';
require_once __DIR__ . '/core/Db.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/View.php';
require_once __DIR__ . '/core/Request.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Page.php';
require_once __DIR__ . '/core/App.php';

use App\Core\Helpers;
use App\Core\Config;
use App\Core\Db;
use App\Core\Auth;

Helpers::loadEnv(__DIR__ . '/../.env');

Auth::start();

$gpt_config = Config::load();
$gpt_pdo = Db::pdo($gpt_config['db']);

// текущий пользователь (или null)
$gpt_user = Auth::current($gpt_pdo);

// тема (берём из сессии; Auth::attempt кладёт сюда theme)
$gpt_theme = $_SESSION['theme'] ?? ($gpt_user['theme'] ?? 'dark');
