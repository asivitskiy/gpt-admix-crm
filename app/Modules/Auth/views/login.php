<!doctype html>
<html lang="ru" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=\App\Core\Helpers::h($title ?? 'Login')?></title>
  <style>
    :root{
      --bg:#0f1115; --card:#1b2133; --text:#e6e8ef; --muted:#9aa3b2; --line:rgba(255,255,255,.10);
      --accent:#6ee7ff; --radius:16px;
    }
    body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial;background:var(--bg);color:var(--text);}
    .wrap{min-height:100vh;display:grid;place-items:center;padding:20px;}
    .card{width:420px;max-width:100%;background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:18px;}
    .title{font-weight:800;font-size:18px;margin-bottom:12px;}
    .muted{color:var(--muted);font-size:13px;}
    .row{margin-top:12px;}
    label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px;}
    input{width:100%;padding:10px 12px;border-radius:12px;border:1px solid var(--line);background:rgba(0,0,0,.18);color:var(--text);outline:none;}
    input:focus{border-color:rgba(110,231,255,.35);}
    .btn{margin-top:14px;width:100%;padding:10px 12px;border-radius:12px;border:1px solid rgba(110,231,255,.25);background:rgba(110,231,255,.12);color:var(--text);font-weight:700;cursor:pointer;}
    .err{margin-top:12px;padding:10px 12px;border-radius:12px;border:1px solid rgba(255,120,120,.25);background:rgba(255,120,120,.08);}
  </style>
</head>
<body>
  <div class="wrap">
    <form class="card" method="post" action="/login">
      <div class="title">Вход в CRM</div>
      <div class="muted">Доступ выдает администратор</div>

      <?php if (!empty($error)): ?>
        <div class="err"><?=\App\Core\Helpers::h($error)?></div>
      <?php endif; ?>

      <div class="row">
        <label>Логин</label>
        <input name="login" autocomplete="username" required>
      </div>

      <div class="row">
        <label>Пароль</label>
        <input name="password" type="password" autocomplete="current-password" required>
      </div>

      <button class="btn" type="submit">Войти</button>
    </form>
  </div>
</body>
</html>
