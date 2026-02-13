<style>
  .table{width:100%;border-collapse:collapse;font-size:13px;}
  .table th,.table td{padding:8px 10px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top;}
  .table th{color:var(--muted);font-weight:600;}
  .badge{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid var(--line);font-size:12px;}
  .badge.on{border-color:color-mix(in srgb, var(--accent) 40%, transparent);}
  .badge.warn{border-color:rgba(255,210,120,.35);background:rgba(255,210,120,.08);}
  .toolbar{display:flex;gap:8px;align-items:flex-start;justify-content:space-between;margin-bottom:10px;}
  .btn.small{width:auto;padding:6px 10px;border-radius:10px;font-size:12px;}
  .flash{margin-bottom:10px;padding:10px 12px;border-radius:14px;border:1px solid var(--line);}
  .flash.ok{border-color:rgba(120,255,170,.28);background:rgba(120,255,170,.08);}
  .flash.err{border-color:rgba(255,120,120,.28);background:rgba(255,120,120,.08);}
  .flash .token{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-weight:700; letter-spacing:1px; font-size:14px; padding:3px 8px; border-radius:10px;
    border:1px dashed var(--line); display:inline-block; margin-left:6px;}
  .grid2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;}
  .field label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px;}
  .field input,.field select{width:100%;padding:10px 12px;border-radius:12px;border:1px solid var(--line);background:rgba(0,0,0,.10);color:var(--text);outline:none;}
  details{border:1px solid var(--line);border-radius:14px;padding:10px 12px;margin-bottom:12px;}
  summary{cursor:pointer;font-weight:700;}
  .actions{display:flex;gap:6px;flex-wrap:wrap;}
</style>

<div class="card">

  <div class="toolbar">
    <div>
      <div style="font-weight:700;">Пользователи</div>
      <div class="muted">Создание, активация/деактивация и сброс пароля (токен для входа).</div>
    </div>
    <a class="btn small" href="index.php?m=admin" style="text-decoration:none;">Назад</a>
  </div>

  <?php if (!empty($flash)): ?>
    <div class="flash <?=($flash['type']??'')==='err'?'err':'ok'?>">
      <div style="font-weight:700;margin-bottom:4px;">
        <?=\App\Core\Helpers::h((string)($flash['msg']??''))?>
      </div>
      <?php if (!empty($flash['login'])): ?>
        <div class="muted">Логин: <b><?=\App\Core\Helpers::h((string)$flash['login'])?></b></div>
      <?php endif; ?>
      <?php if (!empty($flash['token'])): ?>
        <div style="margin-top:6px;">
          Токен:
          <span class="token" id="gptAdminToken"><?=\App\Core\Helpers::h((string)$flash['token'])?></span>
          <button class="btn small" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(document.getElementById('gptAdminToken').innerText)">Копировать</button>
        </div>
        <div class="muted" style="margin-top:6px;">Пользователь входит с логином и токеном, затем устанавливает новый пароль.</div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (empty($is_admin)): ?>
    <div class="muted">Доступно только администратору.</div>
  <?php else: ?>

    <details>
      <summary>Создать пользователя</summary>
      <form method="post" action="index.php?m=admin&p=users" style="margin-top:12px;">
        <input type="hidden" name="act" value="create_user">

        <div class="grid2">
          <div class="field">
            <label>Имя (ФИО / как отображать)</label>
            <input name="name" required>
          </div>
          <div class="field">
            <label>Логин</label>
            <input name="login" required>
          </div>
          <div class="field">
            <label>Буква/код для бланков (необязательно)</label>
            <input name="letter" maxlength="64" placeholder="Напр. А / М / Склад">
          </div>
          <div class="field">
            <label>Роль</label>
            <select name="role">
              <option value="manager">manager</option>
              <option value="printer">printer</option>
              <option value="preprinter">preprinter</option>
              <option value="handwork">handwork</option>
              <option value="storage">storage</option>
              <option value="admin">admin</option>
            </select>
          </div>
          <div class="field">
            <label>Тема по умолчанию</label>
            <select name="theme">
              <option value="dark">dark</option>
              <option value="light">light</option>
            </select>
          </div>
        </div>

        <div style="margin-top:10px;">
          <button class="btn small" type="submit">Создать и получить токен</button>
        </div>
      </form>
    </details>

    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Имя</th>
          <th>Буква</th>
          <th>Логин</th>
          <th>Роль</th>
          <th>Статус</th>
          <th>Пароль</th>
          <th>Создан</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr><td colspan="9" class="muted">Пользователей нет.</td></tr>
        <?php else: ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?=\App\Core\Helpers::h((string)$u['id'])?></td>
              <td><?=\App\Core\Helpers::h((string)$u['name'])?></td>
              <td><?=\App\Core\Helpers::h((string)($u['letter'] ?? ''))?></td>
              <td><?=\App\Core\Helpers::h((string)$u['login'])?></td>
              <td>
  <form method="post" action="index.php?m=admin&p=users" style="margin:0;">
    <input type="hidden" name="act" value="change_role">
    <input type="hidden" name="uid" value="<?=\App\Core\Helpers::h((string)$u['id'])?>">
    <select name="role" onchange="this.form.submit()" style="padding:6px 10px;border-radius:10px;border:1px solid var(--line);background:rgba(0,0,0,.10);color:var(--text);">
      <?php
        $roles = ['admin','manager','printer','preprinter','handwork','storage'];
        foreach ($roles as $r):
          $sel = ((string)$u['role'] === $r) ? 'selected' : '';
      ?>
        <option value="<?=\App\Core\Helpers::h($r)?>" <?=$sel?>><?=\App\Core\Helpers::h($r)?></option>
      <?php endforeach; ?>
    </select>
  </form>
</td>
              <td>
                <?php if ((int)$u['is_active'] === 1): ?>
                  <span class="badge on">active</span>
                <?php else: ?>
                  <span class="badge">inactive</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ((int)($u['must_set_password'] ?? 0) === 1): ?>
                  <span class="badge warn">needs set</span>
                <?php else: ?>
                  <span class="badge">ok</span>
                <?php endif; ?>
              </td>
              <td><?=\App\Core\Helpers::h((string)$u['created_at'])?></td>
              <td>
                <div class="actions">
                  <form method="post" action="index.php?m=admin&p=users" style="margin:0;">
                    <input type="hidden" name="act" value="reset_password">
                    <input type="hidden" name="uid" value="<?=\App\Core\Helpers::h((string)$u['id'])?>">
                    <button class="btn small" type="submit">Сбросить пароль</button>
                  </form>

                  <?php if ((int)$u['is_active'] === 1): ?>
                    <form method="post" action="index.php?m=admin&p=users" style="margin:0;">
                      <input type="hidden" name="act" value="toggle_active">
                      <input type="hidden" name="uid" value="<?=\App\Core\Helpers::h((string)$u['id'])?>">
                      <input type="hidden" name="to" value="0">
                      <button class="btn small" type="submit">Деактивировать</button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="index.php?m=admin&p=users" style="margin:0;">
                      <input type="hidden" name="act" value="toggle_active">
                      <input type="hidden" name="uid" value="<?=\App\Core\Helpers::h((string)$u['id'])?>">
                      <input type="hidden" name="to" value="1">
                      <button class="btn small" type="submit">Активировать</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
