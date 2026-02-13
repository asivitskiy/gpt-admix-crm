<?php
declare(strict_types=1);

$title = 'Касса';
$subtitle = 'Макет (без данных)';

// Демо-данные: имитируем поступления. Позже заменим на БД.
$demo = [
  [
    'id' => 'p1',
    'dt' => '2026-02-04 12:10',
    'client' => 'ООО «Ромашка»',
    'order' => '7812',
    'invoice' => 'Счёт №154',
    'method' => 'bank_ooo',
    'amount' => 58555.80,
    'note' => 'Оплата по счёту',
  ],
  [
    'id' => 'p2',
    'dt' => '2026-02-04 13:05',
    'client' => 'ИП Иванов',
    'order' => '7813',
    'invoice' => '',
    'method' => 'terminal',
    'amount' => 1200.00,
    'note' => 'Терминал',
  ],
  [
    'id' => 'p3',
    'dt' => '2026-02-04 18:42',
    'client' => 'ООО «Плюс»',
    'order' => '7809',
    'invoice' => '',
    'method' => 'cash',
    'amount' => 5000.00,
    'note' => 'Наличные',
  ],
  [
    'id' => 'p4',
    'dt' => '2026-02-05 09:15',
    'client' => 'ООО «Ромашка»',
    'order' => '7812',
    'invoice' => '',
    'method' => 'sbp',
    'amount' => 2500.00,
    'note' => 'СБП (доплата)',
  ],
  [
    'id' => 'p5',
    'dt' => '2026-02-05 10:20',
    'client' => 'Частное лицо',
    'order' => '7814',
    'invoice' => '',
    'method' => 'qr',
    'amount' => 980.00,
    'note' => 'QR',
  ],
  [
    'id' => 'p6',
    'dt' => '2026-02-05 11:30',
    'client' => 'ООО «Лаборатория»',
    'order' => '7815',
    'invoice' => 'Счёт №155',
    'method' => 'bank_ip',
    'amount' => 31500.00,
    'note' => 'Оплата по счёту',
  ],
  [
    'id' => 'p7',
    'dt' => '2026-02-05 12:05',
    'client' => 'ООО «Лаборатория»',
    'order' => '7816',
    'invoice' => 'Счёт №155',
    'method' => 'bank_ip',
    'amount' => 12200.00,
    'note' => 'Оплата по счёту',
  ],
  [
    'id' => 'p8',
    'dt' => '2026-02-05 12:07',
    'client' => 'ООО «Лаборатория»',
    'order' => '7817',
    'invoice' => 'Счёт №155',
    'method' => 'bank_ip',
    'amount' => 8900.00,
    'note' => 'Оплата по счёту',
  ],
  [
    'id' => 'p9',
    'dt' => '2026-02-05 15:40',
    'client' => 'ООО «Ромашка»',
    'order' => '7812',
    'invoice' => '',
    'method' => 'cash_receipt',
    'amount' => 1500.00,
    'note' => 'Нал + чек',
  ],
];

// Рендер центральной части (без View::render)
$gpt_view_vars = [
  'demo' => $demo,
];
extract($gpt_view_vars, EXTR_SKIP);
ob_start();
include __DIR__ . '/view.php';
$content = ob_get_clean();
