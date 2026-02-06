<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $file, array $vars = []): string
    {
        if (!is_file($file)) {
            return '<h1>View not found</h1><pre>'.Helpers::h($file).'</pre>';
        }

        extract($vars, EXTR_SKIP);
        ob_start();
        include $file;
        return (string)ob_get_clean();
    }
}
