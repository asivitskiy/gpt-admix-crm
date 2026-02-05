<?php
declare(strict_types=1);

namespace App\Modules\Auth;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class AuthController
{
    private array $c;

    public function __construct(array $c) { $this->c = $c; }

    public function form(Request $req): string
    {
        if (Auth::logged()) {
            Response::redirect('/');
        }

        return View::render(__DIR__.'/views/login.php', [
            'title' => 'Вход',
            'error' => '',
        ]);
    }

    public function submit(Request $req): string
    {
        $login = (string)$req->post('login', '');
        $pass  = (string)$req->post('password', '');

        if (Auth::attempt($this->c['pdo'], $login, $pass)) {
            Response::redirect('/');
        }

        return View::render(__DIR__.'/views/login.php', [
            'title' => 'Вход',
            'error' => 'Неверный логин или пароль',
        ]);
    }

    public function logout(Request $req): string
    {
        Auth::logout();
        Response::redirect('/login');
        return '';
    }
}
