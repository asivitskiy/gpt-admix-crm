<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $html): void
    {
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    public static function redirect(string $url): void
    {
        header('Location: '.$url);
        exit;
    }
}
