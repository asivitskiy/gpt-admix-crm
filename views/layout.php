<?php
$theme = $theme ?? ($_SESSION['theme'] ?? 'dark');
?>
<!doctype html>
<html lang="ru" data-theme="<?=\App\Core\Helpers::h((string)$theme)?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=\App\Core\Helpers::h($title ?? 'CRM')?></title>
  <link rel="stylesheet" href="/assets/widgets/contragents_widget.css?v=1">
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
    .nav a{display:flex;align-items:center;gap:10px;padding:10px 10px;border-radius:12px;color:var(--muted);}
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

    /* ===== правая выезжающая панель (пока базовая заглушка) ===== */
    .rightpanel {
      position: fixed;
      top: 0; right: -360px;
      width: 360px; max-width: 92vw;
      height: 100vh;
      background: var(--panel);
      border-left: 1px solid var(--line);
      box-shadow: -20px 0 40px rgba(0,0,0,.25);
      z-index: 50;
      transition: right .18s ease;
      display: flex;
      flex-direction: column;
    }
    .rightpanel.open { right: 0; }
    .rpHead {
      padding: 12px 14px;
      border-bottom: 1px solid var(--line);
      display:flex;align-items:center;justify-content:space-between;gap:10px;
    }
    .rpBody { padding: 12px 14px; overflow:auto; }
    .rpClose {
      border:1px solid var(--line); background: rgba(127,127,127,.08);
      color: var(--text); border-radius: 12px; padding: 6px 10px;
      cursor:pointer;
    }
  
    /* === form controls (fix dark theme select dropdown) === */
    input, select, textarea{
      background: var(--panel);
      color: var(--text);
      border: 1px solid var(--line);
      border-radius: 10px;
      padding: 6px 8px;
      font: inherit;
      outline: none;
    }
    input:focus, select:focus, textarea:focus{
      border-color: color-mix(in srgb, var(--accent) 45%, var(--line));
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 16%, transparent);
    }
    select option{
      background: var(--panel);
      color: var(--text);
    }

</style>
</head>
<body>
  <div class="app">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main>
      <div class="top">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
          <div>
            <div style="font-weight:700;"><?php echo \App\Core\Helpers::h($title ?? ''); ?></div>
            <?php if (!empty($subtitle)): ?>
              <div class="muted"><?php echo \App\Core\Helpers::h($subtitle); ?></div>
            <?php endif; ?>
          </div>
          <div style="display:flex;align-items:center;gap:8px;">
            <?php if (!empty($topRightHtml)) echo $topRightHtml; ?>
            <button class="btn" type="button" onclick="window.GPT_RightPanel && window.GPT_RightPanel.open && window.GPT_RightPanel.open()">Панель</button>
          </div>
        </div>
      </div>

      <div class="content">
        <?php echo $content ?? ''; ?>
      </div>
    </main>
  </div>

  <?php include __DIR__ . '/partials/rightpanel.php'; ?>
  <?php include __DIR__ . '/partials/widgets/contragents_modal.php'; ?>

  <script>
    // host config for contragents widget
    window.GPT_NEWCONTRAGENTS_API = "/api/contragents.php";
  </script>
  <script defer src="/assets/widgets/contragents_widget.js?v=1"></script>

  <script>
  // базовый контрол правой панели (потом расширим)
  window.GPT_RightPanel = {
    open() {
      var el = document.getElementById('gptRightPanel');
      if (el) el.classList.add('open');
    },
    close() {
      var el = document.getElementById('gptRightPanel');
      if (el) el.classList.remove('open');
    },
    toggle() {
      var el = document.getElementById('gptRightPanel');
      if (!el) return;
      el.classList.toggle('open');
    }
  };
  </script>
</body>
</html>
