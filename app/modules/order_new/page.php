<?php
declare(strict_types=1);

$title = 'Новый заказ';
$subtitle = 'Макет (без логики БД)';

// Тут позже будут кнопки «Сохранить», «Печать», и т.п.
$topRightHtml = '';

$content = \App\Core\View::render(__DIR__ . '/view.php', []);
