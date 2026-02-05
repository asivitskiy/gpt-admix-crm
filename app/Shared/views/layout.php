<?php
$theme = $theme ?? ($_SESSION['theme'] ?? 'dark');
?>
<!doctype html>
<html lang="ru" data-theme="<?=\App\Core\Helpers::h((string)$theme)?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=\App\Core\Helpers::h($title ?? 'CRM')?></title>
  <style>
    :root{
      --bg:#0f1115;
      --panel:#151926;
      --card:#1b2133;
      --text:#e6e8ef;
      --muted:#9aa3b2;
      --accent:#6ee7ff;
      --line:rgba(255,255,255,.08);
      --radius:16px;

      --sideW:260px;
      --fs:14px;
    }
    html[data-theme="light"]{
      --bg:#f5f6fa;
      --panel:#ffffff;
      --card:#ffffff;
      --text:#111827;
      --muted:#6b7280;
      --accent:#0ea5e9;
      --line:rgba(17,24,39,.10);
    }
    @media (prefers-color-scheme: light){
      html[data-theme="auto"]{
        --bg:#f5f6fa;
        --panel:#ffffff;
        --card:#ffffff;
        --text:#111827;
        --muted:#6b7280;
        --accent:#0ea5e9;
        --line:rgba(17,24,39,.10);
      }
    }
    @media (prefers-color-scheme: dark){
      html[data-theme="auto"]{
        --bg:#0f1115;
        --panel:#151926;
        --card:#1b2133;
        --text:#e6e8ef;
        --muted:#9aa3b2;
        --accent:#6ee7ff;
        --line:rgba(255,255,255,.08);
      }
    }

    *{box-sizing:border-box}
    body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial;font-size:var(--fs);background:var(--bg);color:var(--text);}
    a{color:inherit;text-decoration:none}

    .app{display:grid;grid-template-columns:var(--sideW) 1fr;min-height:100vh;}
    .sidebar{
      background:var(--panel);
      border-right:1px solid var(--line);
      padding:14px;
      display:flex;
      flex-direction:column;
      gap:12px;
    }
    .brand{font-weight:800;letter-spacing:.5px;}
    .nav{flex:1;}
    .nav a{display:block;padding:10px 10px;border-radius:12px;color:var(--muted);}
    .nav a:hover{background:rgba(127,127,127,.08);color:var(--text);}
    .nav a.active{background:color-mix(in srgb, var(--accent) 14%, transparent);color:var(--text);border:1px solid color-mix(in srgb, var(--accent) 28%, transparent);}

    .sidebarUser{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:12px;}
    .sidebarUserName{font-weight:700;margin-bottom:4px;}
    .sidebarUserLogin{color:var(--muted);font-size:12px;margin-bottom:10px;}
    .btn{
      display:block;width:100%;text-align:center;
      padding:10px 12px;border-radius:12px;
      border:1px solid color-mix(in srgb, var(--accent) 28%, transparent);
      background:color-mix(in srgb, var(--accent) 12%, transparent);
      color:var(--text);font-weight:700;cursor:pointer;
    }
    .link{display:block;text-align:center;color:var(--muted);padding:8px 0;}
    .link:hover{color:var(--text);text-decoration:underline;}

    .top{padding:14px 18px;border-bottom:1px solid var(--line);background:rgba(0,0,0,.03);}
    html[data-theme="light"] .top{background:rgba(255,255,255,.65);}
    .content{padding:18px;min-width:0;}
    .card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:14px;}
    .muted{color:var(--muted)}
    .grid{display:grid;gap:12px;}
  </style>
</head>
<body>
  <div class="app">
    <aside class="sidebar">
      <div class="brand">ADMIX CRM</div>

      <nav class="nav">
        <?php foreach (($nav ?? []) as $item): ?>
          <a class="<?=($item['active']??false)?'active':''?>" href="<?=$item['href']?>"><?=\App\Core\Helpers::h($item['title'])?></a>
        <?php endforeach; ?>
      </nav>

      <?php if (!empty($user)): ?>
        <div class="sidebarUser">
          <div class="sidebarUserName"><?=\App\Core\Helpers::h($user['name'] ?? '')?></div>
          <div class="sidebarUserLogin"><?=\App\Core\Helpers::h($user['login'] ?? '')?></div>
          <a class="btn" href="/me">Личный кабинет</a>
          <a class="link" href="/logout">Выход</a>
        </div>
      <?php endif; ?>
    </aside>

    <main>
      <div class="top">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
          <div>
            <div style="font-weight:700;"><?=\App\Core\Helpers::h($title ?? '')?></div>
            <?php if (!empty($subtitle)): ?>
              <div class="muted"><?=\App\Core\Helpers::h($subtitle)?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="content">
        <?=$content ?? ''?>
      </div>
    </main>
  </div>
</body>
</html>
