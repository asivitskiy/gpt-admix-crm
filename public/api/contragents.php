<?php
declare(strict_types=1);

require __DIR__ . '/../../inc/bootstrap.php';

// Only for authenticated users
if (!$gpt_user) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => 0, 'err' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$pdo = $gpt_pdo;

function gpt_json_ok(array $data = []): void {
    echo json_encode(['ok' => 1, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}
function gpt_json_err(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => 0, 'err' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = trim((string)($_POST['gpt_action'] ?? $_GET['gpt_action'] ?? ''));
if ($action === '') gpt_json_err('No action');

// ---------- helpers ----------
function mb_lower(string $s): string {
    return function_exists('mb_strtolower') ? (string)mb_strtolower($s, 'UTF-8') : strtolower($s);
}
function clean_for_tokens(string $s): string {
    $clean = @preg_replace('/[^\p{L}\p{N}#]+/u', ' ', $s);
    if ($clean === null || $clean === '') $clean = $s;
    $clean = trim((string)$clean);
    $clean = (string)preg_replace('/\s+/', ' ', $clean);
    return trim($clean);
}
function layout_swap(string $s): string {
    static $m = null;
    if ($m === null) {
        $m = [
            'q'=>'й','w'=>'ц','e'=>'у','r'=>'к','t'=>'е','y'=>'н','u'=>'г','i'=>'ш','o'=>'щ','p'=>'з','['=>'х',']'=>'ъ',
            'a'=>'ф','s'=>'ы','d'=>'в','f'=>'а','g'=>'п','h'=>'р','j'=>'о','k'=>'л','l'=>'д',';'=>'ж',"'"=>'э',
            'z'=>'я','x'=>'ч','c'=>'с','v'=>'м','b'=>'и','n'=>'т','m'=>'ь',','=>'б','.'=>'ю',
            'й'=>'q','ц'=>'w','у'=>'e','к'=>'r','е'=>'t','н'=>'y','г'=>'u','ш'=>'i','щ'=>'o','з'=>'p','х'=>'[','ъ'=>']',
            'ф'=>'a','ы'=>'s','в'=>'d','а'=>'f','п'=>'g','р'=>'h','о'=>'j','л'=>'k','д'=>'l','ж'=>';','э'=>"'",
            'я'=>'z','ч'=>'x','с'=>'c','м'=>'v','и'=>'b','т'=>'n','ь'=>'m','б'=>',','ю'=>'.',
        ];
    }
    $out = '';
    $len = function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $ch = function_exists('mb_substr') ? mb_substr($s, $i, 1, 'UTF-8') : $s[$i];
        $lo = mb_lower($ch);
        $mapped = $m[$lo] ?? $ch;
        // preserve case roughly
        if ($ch !== $lo && isset($m[$lo])) {
            $mapped = function_exists('mb_strtoupper') ? mb_strtoupper($mapped, 'UTF-8') : strtoupper($mapped);
        }
        $out .= $mapped;
    }
    return $out;
}
function only_digits(string $s): string {
    return (string)preg_replace('/\D+/', '', $s);
}

// ---------- actions ----------

if ($action === 'search_contragents') {
    $term = trim((string)($_GET['gpt_term'] ?? ''));
    if ($term === '') gpt_json_ok(['items' => []]);

    // allow #123 / 123 quick id
    if (preg_match('/^#?(\d+)$/', $term, $m)) {
        $id = (int)$m[1];
        $st = $pdo->prepare('SELECT id, name FROM contragents WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if ($r) gpt_json_ok(['items' => [$r]]);
        gpt_json_ok(['items' => []]);
    }

    // tokenization + layout swap variant
    $variants = [];
    $variants[] = clean_for_tokens($term);
    $swap = clean_for_tokens(layout_swap($term));
    if ($swap !== $variants[0]) $variants[] = $swap;

    // build WHERE for each variant (OR), tokens inside are AND
    $concat = "CONCAT_WS(' ', c.name, IFNULL(c.gpt_keywords,''), IFNULL(c.gpt_note,''), IFNULL(r.gpt_legal_name,''), IFNULL(r.gpt_inn,''), IFNULL(ct.gpt_phone_clear,''), IFNULL(ct.gpt_phone,''), IFNULL(ct.gpt_email,''))";

    $orParts = [];
    $params = [];
    foreach ($variants as $v) {
        $tokens = array_values(array_filter(explode(' ', mb_lower($v))));
        if (count($tokens) === 0) continue;
        $and = [];
        foreach ($tokens as $t) {
            if ($t === '') continue;
            // if token looks like phone/email/inn keep as is
            $and[] = "$concat LIKE ?";
            $params[] = '%' . $t . '%';
        }
        if ($and) $orParts[] = '(' . implode(' AND ', $and) . ')';
    }

    if (!$orParts) gpt_json_ok(['items' => []]);

    $sql = "
      SELECT c.id, c.name
      FROM contragents c
      LEFT JOIN gpt_contragent_requisites r ON r.gpt_contragent_id=c.id AND r.gpt_active=1
      LEFT JOIN gpt_contragent_contacts ct ON ct.gpt_contragent_id=c.id AND ct.gpt_active=1
      WHERE " . implode(' OR ', $orParts) . "
      GROUP BY c.id
      ORDER BY c.name ASC
      LIMIT 30
    ";

    $st = $pdo->prepare($sql);
    $st->execute($params);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);
    gpt_json_ok(['items' => $items]);
}

if ($action === 'get_contragent') {
    $cid = (int)($_GET['gpt_contragent_id'] ?? 0);
    if ($cid <= 0) gpt_json_err('No contragent id');

    $st = $pdo->prepare('SELECT id, name, address, fullinfo, contacts, notification_number, gpt_note AS note, gpt_keywords AS keywords FROM contragents WHERE id=? LIMIT 1');
    $st->execute([$cid]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) gpt_json_err('Not found', 404);
    gpt_json_ok($row);
}

if ($action === 'add_contragent') {
    $name = trim((string)($_POST['gpt_name'] ?? ''));
    $keywords = trim((string)($_POST['gpt_keywords'] ?? ''));
    $note = trim((string)($_POST['gpt_note'] ?? ''));
    if (mb_strlen($name, 'UTF-8') < 2) gpt_json_err('Bad name');

    $st = $pdo->prepare("INSERT INTO contragents(
      relativity, name, address, fullinfo, contacts,
      contragent_amount, contragent_dolg, contragent_completed, contragent_from_money_table, contragent_inwork,
      notification_number, qr_success, gpt_note, gpt_keywords
    ) VALUES(
      0, ?, '', '', '',
      0, 0, 0, 0, 0,
      '', 0, ?, ?
    )");
    $st->execute([$name, ($note === '' ? null : $note), $keywords]);
    $id = (int)$pdo->lastInsertId();
    gpt_json_ok(['id' => $id, 'name' => $name]);
}

if ($action === 'update_contragent') {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    if ($cid <= 0) gpt_json_err('No contragent id');

    $name = trim((string)($_POST['gpt_name'] ?? ''));
    $keywords = trim((string)($_POST['gpt_keywords'] ?? ''));
    $note = trim((string)($_POST['gpt_note'] ?? ''));

    if (mb_strlen($name, 'UTF-8') < 2) gpt_json_err('Bad name');

    $st = $pdo->prepare('UPDATE contragents SET name=?, gpt_keywords=?, gpt_note=? WHERE id=?');
    $st->execute([$name, $keywords, ($note === '' ? null : $note), $cid]);
    gpt_json_ok(['id' => $cid, 'name' => $name]);
}

// ===== requisites =====
if ($action === 'list_requisites') {
    $cid = (int)($_GET['gpt_contragent_id'] ?? 0);
    if ($cid <= 0) gpt_json_err('No contragent id');

    $st = $pdo->prepare('SELECT * FROM gpt_contragent_requisites WHERE gpt_contragent_id=? AND gpt_active=1 ORDER BY gpt_is_default DESC, gpt_id DESC');
    $st->execute([$cid]);
    gpt_json_ok(['items' => $st->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($action === 'save_requisite') {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    if ($cid <= 0) gpt_json_err('No contragent id');
    $id = (int)($_POST['gpt_id'] ?? 0);

    $fields = [
        'gpt_legal_name','gpt_inn','gpt_jur_address','gpt_rs','gpt_bank_name','gpt_bank_bik','gpt_director_fio','gpt_basis','gpt_tax_mode','gpt_contract_no','gpt_details','gpt_dadata_json'
    ];
    $data = [];
    foreach ($fields as $f) $data[$f] = $_POST[$f] ?? null;

    $is_default = (int)($_POST['gpt_is_default'] ?? 0) ? 1 : 0;

    if ($id > 0) {
        $st = $pdo->prepare('UPDATE gpt_contragent_requisites SET gpt_legal_name=?, gpt_inn=?, gpt_jur_address=?, gpt_rs=?, gpt_bank_name=?, gpt_bank_bik=?, gpt_director_fio=?, gpt_basis=?, gpt_tax_mode=?, gpt_contract_no=?, gpt_details=?, gpt_dadata_json=?, gpt_is_default=? WHERE gpt_id=? AND gpt_contragent_id=?');
        $st->execute([
            (string)$data['gpt_legal_name'],
            $data['gpt_inn'],
            $data['gpt_jur_address'],
            $data['gpt_rs'],
            $data['gpt_bank_name'],
            $data['gpt_bank_bik'],
            $data['gpt_director_fio'],
            $data['gpt_basis'],
            $data['gpt_tax_mode'],
            (string)($data['gpt_contract_no'] ?? ''),
            $data['gpt_details'],
            $data['gpt_dadata_json'],
            $is_default,
            $id,
            $cid
        ]);
    } else {
        $st = $pdo->prepare('INSERT INTO gpt_contragent_requisites(gpt_contragent_id,gpt_legal_name,gpt_inn,gpt_jur_address,gpt_rs,gpt_bank_name,gpt_bank_bik,gpt_director_fio,gpt_basis,gpt_tax_mode,gpt_contract_no,gpt_details,gpt_is_default,gpt_active,gpt_dadata_json) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,1,?)');
        $st->execute([
            $cid,
            (string)$data['gpt_legal_name'],
            $data['gpt_inn'],
            $data['gpt_jur_address'],
            $data['gpt_rs'],
            $data['gpt_bank_name'],
            $data['gpt_bank_bik'],
            $data['gpt_director_fio'],
            $data['gpt_basis'],
            $data['gpt_tax_mode'],
            (string)($data['gpt_contract_no'] ?? ''),
            $data['gpt_details'],
            $is_default,
            $data['gpt_dadata_json']
        ]);
        $id = (int)$pdo->lastInsertId();
    }

    if ($is_default) {
        $st = $pdo->prepare('UPDATE gpt_contragent_requisites SET gpt_is_default=0 WHERE gpt_contragent_id=? AND gpt_id<>?');
        $st->execute([$cid, $id]);
    }

    gpt_json_ok(['gpt_id' => $id]);
}

if ($action === 'delete_requisite') {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    $id  = (int)($_POST['gpt_id'] ?? 0);
    if ($cid<=0 || $id<=0) gpt_json_err('Bad params');
    $st = $pdo->prepare('UPDATE gpt_contragent_requisites SET gpt_active=0 WHERE gpt_contragent_id=? AND gpt_id=?');
    $st->execute([$cid, $id]);
    gpt_json_ok(['gpt_id' => $id]);
}

if ($action === 'set_default_requisite') {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    $id  = (int)($_POST['gpt_id'] ?? 0);
    if ($cid<=0 || $id<=0) gpt_json_err('Bad params');
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('UPDATE gpt_contragent_requisites SET gpt_is_default=0 WHERE gpt_contragent_id=?');
        $st->execute([$cid]);
        $st = $pdo->prepare('UPDATE gpt_contragent_requisites SET gpt_is_default=1 WHERE gpt_contragent_id=? AND gpt_id=?');
        $st->execute([$cid, $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        gpt_json_err('DB error: '.$e->getMessage(), 500);
    }
    gpt_json_ok(['gpt_id' => $id]);
}

// ===== contacts =====
if ($action === 'list_contacts') {
    $cid = (int)($_GET['gpt_contragent_id'] ?? 0);
    if ($cid <= 0) gpt_json_err('No contragent id');

    $st = $pdo->prepare('SELECT * FROM gpt_contragent_contacts WHERE gpt_contragent_id=? AND gpt_active=1 ORDER BY gpt_is_default DESC, gpt_sort ASC, gpt_id DESC');
    $st->execute([$cid]);
    gpt_json_ok(['items' => $st->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($action === 'save_contact') {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    if ($cid <= 0) gpt_json_err('No contragent id');
    $id = (int)($_POST['gpt_id'] ?? 0);

    $name = trim((string)($_POST['gpt_name'] ?? ''));
    if ($name === '') gpt_json_err('No name');

    $phone = trim((string)($_POST['gpt_phone'] ?? ''));
    $phone_clear = trim((string)($_POST['gpt_phone_clear'] ?? ''));
    if ($phone_clear === '' && $phone !== '') $phone_clear = only_digits($phone);

    $email = trim((string)($_POST['gpt_email'] ?? ''));
    $chat_id = trim((string)($_POST['gpt_chat_id'] ?? ''));
    $comment = trim((string)($_POST['gpt_comment'] ?? ''));
    $sort = (int)($_POST['gpt_sort'] ?? 0);

    $is_default = (int)($_POST['gpt_is_default'] ?? 0) ? 1 : 0;
    $is_notify  = (int)($_POST['gpt_is_notify_default'] ?? 0) ? 1 : 0;
    $is_invoice = (int)($_POST['gpt_is_invoice_default'] ?? 0) ? 1 : 0;

    $req_id = isset($_POST['gpt_requisite_id']) ? (int)$_POST['gpt_requisite_id'] : null;
    if ($req_id === 0) $req_id = null;

    if ($id > 0) {
        $st = $pdo->prepare('UPDATE gpt_contragent_contacts SET gpt_requisite_id=?, gpt_name=?, gpt_phone=?, gpt_phone_clear=?, gpt_email=?, gpt_chat_id=?, gpt_is_default=?, gpt_is_notify_default=?, gpt_is_invoice_default=?, gpt_comment=?, gpt_sort=? WHERE gpt_id=? AND gpt_contragent_id=?');
        $st->execute([$req_id, $name, ($phone===''?null:$phone), $phone_clear, ($email===''?null:$email), $chat_id, $is_default, $is_notify, $is_invoice, ($comment===''?null:$comment), $sort, $id, $cid]);
    } else {
        $st = $pdo->prepare('INSERT INTO gpt_contragent_contacts(gpt_contragent_id,gpt_requisite_id,gpt_name,gpt_phone,gpt_phone_clear,gpt_email,gpt_chat_id,gpt_is_default,gpt_is_notify_default,gpt_is_invoice_default,gpt_comment,gpt_sort,gpt_active) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,1)');
        $st->execute([$cid, $req_id, $name, ($phone===''?null:$phone), $phone_clear, ($email===''?null:$email), $chat_id, $is_default, $is_notify, $is_invoice, ($comment===''?null:$comment), $sort]);
        $id = (int)$pdo->lastInsertId();
    }

    if ($is_default) {
        $st = $pdo->prepare('UPDATE gpt_contragent_contacts SET gpt_is_default=0 WHERE gpt_contragent_id=? AND gpt_id<>?');
        $st->execute([$cid, $id]);
    }
    if ($is_notify) {
        $st = $pdo->prepare('UPDATE gpt_contragent_contacts SET gpt_is_notify_default=0 WHERE gpt_contragent_id=? AND gpt_id<>?');
        $st->execute([$cid, $id]);
    }
    if ($is_invoice) {
        $st = $pdo->prepare('UPDATE gpt_contragent_contacts SET gpt_is_invoice_default=0 WHERE gpt_contragent_id=? AND gpt_id<>?');
        $st->execute([$cid, $id]);
    }

    gpt_json_ok(['gpt_id' => $id]);
}

if ($action === 'delete_contact') {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    $id  = (int)($_POST['gpt_id'] ?? 0);
    if ($cid<=0 || $id<=0) gpt_json_err('Bad params');
    $st = $pdo->prepare('UPDATE gpt_contragent_contacts SET gpt_active=0 WHERE gpt_contragent_id=? AND gpt_id=?');
    $st->execute([$cid, $id]);
    gpt_json_ok(['gpt_id' => $id]);
}

if (in_array($action, ['set_default_contact','set_notify_contact','set_invoice_contact'], true)) {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    $id  = (int)($_POST['gpt_id'] ?? 0);
    if ($cid<=0 || $id<=0) gpt_json_err('Bad params');

    $col = ($action === 'set_default_contact') ? 'gpt_is_default' : (($action === 'set_notify_contact') ? 'gpt_is_notify_default' : 'gpt_is_invoice_default');

    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare("UPDATE gpt_contragent_contacts SET {$col}=0 WHERE gpt_contragent_id=?");
        $st->execute([$cid]);
        $st = $pdo->prepare("UPDATE gpt_contragent_contacts SET {$col}=1 WHERE gpt_contragent_id=? AND gpt_id=?");
        $st->execute([$cid, $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        gpt_json_err('DB error: '.$e->getMessage(), 500);
    }

    gpt_json_ok(['gpt_id' => $id]);
}

// ===== delivery =====
if ($action === 'list_delivery') {
    $cid = (int)($_GET['gpt_contragent_id'] ?? 0);
    if ($cid <= 0) gpt_json_err('No contragent id');

    $st = $pdo->prepare('SELECT * FROM gpt_contragent_delivery WHERE gpt_contragent_id=? AND gpt_active=1 ORDER BY gpt_is_default DESC, gpt_id DESC');
    $st->execute([$cid]);
    gpt_json_ok(['items' => $st->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($action === 'save_delivery') {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    if ($cid <= 0) gpt_json_err('No contragent id');
    $id = (int)($_POST['gpt_id'] ?? 0);

    $title = trim((string)($_POST['gpt_title'] ?? ''));
    $addr = (string)($_POST['gpt_address'] ?? '');
    $is_default = (int)($_POST['gpt_is_default'] ?? 0) ? 1 : 0;

    if ($id > 0) {
        $st = $pdo->prepare('UPDATE gpt_contragent_delivery SET gpt_title=?, gpt_address=?, gpt_is_default=? WHERE gpt_id=? AND gpt_contragent_id=?');
        $st->execute([$title, $addr, $is_default, $id, $cid]);
    } else {
        $st = $pdo->prepare('INSERT INTO gpt_contragent_delivery(gpt_contragent_id,gpt_title,gpt_address,gpt_is_default,gpt_active) VALUES(?,?,?,?,1)');
        $st->execute([$cid, $title, $addr, $is_default]);
        $id = (int)$pdo->lastInsertId();
    }

    if ($is_default) {
        $st = $pdo->prepare('UPDATE gpt_contragent_delivery SET gpt_is_default=0 WHERE gpt_contragent_id=? AND gpt_id<>?');
        $st->execute([$cid, $id]);
    }

    gpt_json_ok(['gpt_id' => $id]);
}

if ($action === 'delete_delivery') {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    $id  = (int)($_POST['gpt_id'] ?? 0);
    if ($cid<=0 || $id<=0) gpt_json_err('Bad params');
    $st = $pdo->prepare('UPDATE gpt_contragent_delivery SET gpt_active=0 WHERE gpt_contragent_id=? AND gpt_id=?');
    $st->execute([$cid, $id]);
    gpt_json_ok(['gpt_id' => $id]);
}

if ($action === 'set_default_delivery') {
    $cid = (int)($_POST['gpt_contragent_id'] ?? 0);
    $id  = (int)($_POST['gpt_id'] ?? 0);
    if ($cid<=0 || $id<=0) gpt_json_err('Bad params');

    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('UPDATE gpt_contragent_delivery SET gpt_is_default=0 WHERE gpt_contragent_id=?');
        $st->execute([$cid]);
        $st = $pdo->prepare('UPDATE gpt_contragent_delivery SET gpt_is_default=1 WHERE gpt_contragent_id=? AND gpt_id=?');
        $st->execute([$cid, $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        gpt_json_err('DB error: '.$e->getMessage(), 500);
    }

    gpt_json_ok(['gpt_id' => $id]);
}

// ===== DaData =====
if ($action === 'dadata_by_inn') {
    $inn = trim((string)($_GET['gpt_inn'] ?? $_POST['gpt_inn'] ?? ''));
    $inn = only_digits($inn);
    if ($inn === '') gpt_json_err('No INN');

    $token = (string)getenv('DADATA_TOKEN');
    if ($token === '') gpt_json_err('DaData token missing');

    $url = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party';
    $payload = json_encode(['query' => $inn], JSON_UNESCAPED_UNICODE);

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Token ' . $token,
    ];

    // Prefer curl if available
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($resp === false || $code >= 400) {
            gpt_json_err('DaData error'.($err ? (': '.$err) : ''), 502);
        }
        $json = json_decode($resp, true);
        if (!is_array($json)) gpt_json_err('DaData bad response', 502);
        gpt_json_ok(['raw' => $json]);
    }

    // fallback: file_get_contents
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => $payload,
            'timeout' => 12,
        ]
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp === false) gpt_json_err('DaData request failed', 502);
    $json = json_decode($resp, true);
    if (!is_array($json)) gpt_json_err('DaData bad response', 502);
    gpt_json_ok(['raw' => $json]);
}

gpt_json_err('Unknown action: '.$action);
