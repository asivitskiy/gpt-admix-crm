<?php
declare(strict_types=1);

$title = 'Главная';
$subtitle = 'Сводка';

// Рендер центральной части (без View::render, чтобы не путаться)
ob_start();
include __DIR__ . '/view.php';
$content = ob_get_clean();
