<?php
declare(strict_types=1);

$title = 'Новый заказ';
$subtitle = 'Макет (без логики БД)';

// Тут позже будут кнопки «Сохранить», «Печать», и т.п.
$topRightHtml = '';


// Рендер центральной части (без View::render, чтобы не путаться)
ob_start();
include __DIR__ . '/view.php';
$content = ob_get_clean();
