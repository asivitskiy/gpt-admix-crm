<?php
$user = $user ?? [];
$theme = $theme ?? 'dark';
?>
<div class="grid">
  <div class="card">
    <div style="font-weight:700;margin-bottom:8px;">Пользователь</div>
    <div><b><?=\App\Core\Helpers::h($user['name'] ?? '')?></b></div>
    <div class="muted"><?=\App\Core\Helpers::h(($user['role'] ?? '').' · '.($user['login'] ?? ''))?></div>
  </div>

  <div class="card">
    <div style="font-weight:700;margin-bottom:8px;">Тема</div>
    <form method="post" action="index.php?m=profile&action=theme">
      <div style="display:grid;gap:10px;max-width:340px;">
        <select name="theme" style="padding:10px 12px;border-radius:12px;border:1px solid var(--line);background:rgba(127,127,127,.08);color:var(--text);">
          <option value="dark"  <?=($theme==='dark'?'selected':'')?>>Тёмная</option>
          <option value="light" <?=($theme==='light'?'selected':'')?>>Светлая</option>
          <option value="auto"  <?=($theme==='auto'?'selected':'')?>>Авто (как в системе)</option>
        </select>
        <button class="btn" type="submit">Сохранить</button>
        <div class="muted">Тема применяется ко всей CRM для этого пользователя.</div>
      </div>
    </form>
  </div>


  <div class="card">
    <div style="font-weight:700;margin-bottom:8px;">Масштаб текста</div>
    <?php $scale = isset($user['text_scale']) ? (float)$user['text_scale'] : (float)($_SESSION['text_scale'] ?? 1.0); ?>
    <?php if ($scale < 0.8) $scale = 0.8; if ($scale > 1.5) $scale = 1.5; ?>
    <?php $percent = (int)round($scale * 100); ?>
    <form method="post" action="index.php?m=profile&action=scale">
      <div style="display:grid;gap:10px;max-width:420px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
          <div class="muted">От 80% до 150%</div>
          <div id="gptScaleLabel" style="font-weight:700;"><?=$percent?>%</div>
        </div>
        <input id="gptScaleRange" type="range" min="80" max="150" step="1" value="<?=$percent?>" />
        <input id="gptScaleHidden" type="hidden" name="text_scale" value="<?=number_format($scale,2,'.','')?>" />
        <button class="btn" type="submit">Сохранить</button>
        <div class="muted">Масштаб применяется ко всей CRM (увеличивает только текст, не масштабируя интерфейс).</div>
      </div>
    </form>

    <script>
      (function(){
        var r=document.getElementById('gptScaleRange');
        var l=document.getElementById('gptScaleLabel');
        var h=document.getElementById('gptScaleHidden');
        if(!r||!l||!h) return;
        r.addEventListener('input', function(){
          var p=parseInt(r.value||'100',10)||100;
          if (p<80) p=80; if (p>150) p=150;
          l.textContent=p+'%';
          h.value=(p/100).toFixed(2);
        });
      })();
    </script>
  </div>
</div>
