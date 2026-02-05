<?php
declare(strict_types=1);

namespace App\Core;

final class Page
{
    public static function layout(array $c, array $pageVars): string
    {
        $pageVars['nav']   = $pageVars['nav']   ?? ($c['nav'] ?? []);
        $pageVars['user']  = $pageVars['user']  ?? ($c['user'] ?? null);
        $pageVars['theme'] = $pageVars['theme'] ?? ($c['theme'] ?? ($_SESSION['theme'] ?? 'dark'));

        return View::render(__DIR__.'/../Shared/views/layout.php', $pageVars);
    }
}
