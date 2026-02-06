<?php
$theme = $theme ?? ($_SESSION['theme'] ?? 'dark');
?>
<!doctype html>
<html lang="ru" data-theme="<?=\App\Core\Helpers::h((string)$theme)?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=\App\Core\Helpers::h($title ?? 'CRM')?></title>

  <!-- Layout CSS -->
  <link rel="stylesheet" href="/assets/css/layout.css?v=1">

  <!-- Widgets -->
  <link rel="stylesheet" href="/assets/widgets/contragents_widget.css?v=1">
  <link rel="stylesheet" href="/assets/widgets/contragents_widget.theme.css?v=1">
</head>
<body>
  <div class="app">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="main">
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
