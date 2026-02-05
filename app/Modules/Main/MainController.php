<?php
declare(strict_types=1);

namespace App\Modules\Main;

use App\Core\Page;
use App\Core\Request;
use App\Core\View;

final class MainController
{
    private array $c;

    public function __construct(array $c) { $this->c = $c; }

    public function page(Request $req, string $slug): string
    {
        $map = [
            'home'      => ['title'=>'Главная',      'subtitle'=>'',            'view'=>'home.php'],
            'orders'    => ['title'=>'Заказы',       'subtitle'=>'',            'view'=>'placeholder.php'],
            'clients'   => ['title'=>'Клиенты',      'subtitle'=>'',            'view'=>'placeholder.php'],
            'schedule'  => ['title'=>'График работ', 'subtitle'=>'',            'view'=>'placeholder.php'],
            'billing'   => ['title'=>'Касса',        'subtitle'=>'',            'view'=>'placeholder.php'],
            'expenses'  => ['title'=>'Поставщики',   'subtitle'=>'',            'view'=>'placeholder.php'],
            'messages'  => ['title'=>'Сообщения',    'subtitle'=>'',            'view'=>'placeholder.php'],
            'materials' => ['title'=>'Материалы',    'subtitle'=>'',            'view'=>'placeholder.php'],
            'admin'     => ['title'=>'Админка',      'subtitle'=>'',            'view'=>'placeholder.php'],
        ];
        $cfg = $map[$slug] ?? $map['home'];

        $content = View::render(__DIR__.'/views/'.$cfg['view'], ['title'=>$cfg['title']]);

        return Page::layout($this->c, [
            'title' => $cfg['title'],
            'subtitle' => $cfg['subtitle'],
            'content' => $content,
        ]);
    }
}
