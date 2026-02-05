<?php
declare(strict_types=1);

namespace App\Modules\Profile;

use App\Core\Page;
use App\Core\Request;
use App\Core\Response;

final class ProfileController
{
    private array $c;

    public function __construct(array $c) { $this->c = $c; }

    public function me(Request $req): string
    {
        $content = \App\Core\View::render(__DIR__.'/views/me.php', [
            'user' => $this->c['user'],
            'theme' => $this->c['theme'] ?? 'dark',
        ]);

        return Page::layout($this->c, [
            'title' => 'Личный кабинет',
            'subtitle' => 'Настройки пользователя',
            'content' => $content,
        ]);
    }

    public function saveTheme(Request $req): string
    {
        $user = $this->c['user'];
        if (!$user) {
            Response::redirect('/login');
        }

        $theme = (string)$req->post('theme', 'dark');
        if (!in_array($theme, ['dark','light','auto'], true)) $theme = 'dark';

        $st = $this->c['pdo']->prepare("UPDATE users SET theme=? WHERE id=? LIMIT 1");
        $st->execute([$theme, (int)$user['id']]);

        $_SESSION['theme'] = $theme;

        Response::redirect('/me');
        return '';
    }
}
