<?php
declare(strict_types=1);

namespace App\Core;

use App\Modules\Auth\AuthController;
use App\Modules\Profile\ProfileController;
use App\Modules\Main\MainController;

final class App
{
    private array $config;
    private \PDO $pdo;
    private Router $router;

    public function __construct()
    {
        Auth::start();
        $this->config = Config::load();
        $this->pdo = Db::pdo($this->config['db']);
        $this->router = new Router();

        $this->defineRoutes();
    }

    private function defineRoutes(): void
    {
        $this->router->get('/login', fn($req,$c)=> (new AuthController($c))->form($req));
        $this->router->post('/login', fn($req,$c)=> (new AuthController($c))->submit($req));
        $this->router->get('/logout', fn($req,$c)=> (new AuthController($c))->logout($req));

        $this->router->get('/', fn($req,$c)=> (new MainController($c))->page($req, 'home'));
        $this->router->get('/orders', fn($req,$c)=> (new MainController($c))->page($req, 'orders'));
        $this->router->get('/clients', fn($req,$c)=> (new MainController($c))->page($req, 'clients'));
        $this->router->get('/schedule', fn($req,$c)=> (new MainController($c))->page($req, 'schedule'));
        $this->router->get('/billing', fn($req,$c)=> (new MainController($c))->page($req, 'billing'));
        $this->router->get('/expenses', fn($req,$c)=> (new MainController($c))->page($req, 'expenses'));
        $this->router->get('/messages', fn($req,$c)=> (new MainController($c))->page($req, 'messages'));
        $this->router->get('/materials', fn($req,$c)=> (new MainController($c))->page($req, 'materials'));
        $this->router->get('/admin', fn($req,$c)=> (new MainController($c))->page($req, 'admin'));

        $this->router->get('/me', fn($req,$c)=> (new ProfileController($c))->me($req));
        $this->router->post('/me/theme', fn($req,$c)=> (new ProfileController($c))->saveTheme($req));
    }

    private function buildNav(string $path): array
    {
        $items = [
            ['title'=>'Главная',      'href'=>'/',          'key'=>'/'],
            ['title'=>'Заказы',       'href'=>'/orders',    'key'=>'/orders'],
            ['title'=>'Клиенты',      'href'=>'/clients',   'key'=>'/clients'],
            ['title'=>'График работ', 'href'=>'/schedule',  'key'=>'/schedule'],
            ['title'=>'Касса',        'href'=>'/billing',   'key'=>'/billing'],
            ['title'=>'Поставщики',   'href'=>'/expenses',  'key'=>'/expenses'],
            ['title'=>'Сообщения',    'href'=>'/messages',  'key'=>'/messages'],
            ['title'=>'Материалы',    'href'=>'/materials', 'key'=>'/materials'],
            ['title'=>'Админка',      'href'=>'/admin',     'key'=>'/admin'],
        ];

        foreach ($items as &$it) {
            $it['active'] = ($it['key'] === $path);
            unset($it['key']);
        }
        return $items;
    }

    private function guard(Request $req): void
    {
        if ($req->path === '/login') return;

        if (!Auth::logged()) {
            Response::redirect('/login');
        }
    }

    public function run(): void
    {
        $req = new Request();

        $rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($rawPath !== '/' && substr($rawPath, -1) === '/') {
            Response::redirect(rtrim($rawPath, '/'));
        }

        $this->guard($req);

        $user = Auth::current($this->pdo);
        $theme = $user['theme'] ?? ($_SESSION['theme'] ?? 'dark');
        $_SESSION['theme'] = $theme;

        $c = [
            'config' => $this->config,
            'pdo'    => $this->pdo,
            'user'   => $user,
            'theme'  => $theme,
            'nav'    => $this->buildNav($req->path),
        ];

        $html = $this->router->dispatch($req, $c);
        Response::html($html);
    }
}
