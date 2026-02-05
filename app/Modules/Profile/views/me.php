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
    <form method="post" action="/me/theme">
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
</div>
