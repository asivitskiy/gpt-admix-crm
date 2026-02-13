<?php
// Ожидает: $nav (array), $user (array|null)
?>
<aside class="sidebar">
  <div class="brand">ADMIX CRM</div>

  <nav class="nav">
    <?php foreach (($nav ?? []) as $item): ?>
      <a class="<?=($item['active']??false)?'active':''?>" href="<?=$item['href']?>">
        <?php if (!empty($item['icon'])): ?>
          <span class="navIcon"><?=$item['icon']?></span>
        <?php endif; ?>
        <span class="navTitle"><?=\App\Core\Helpers::h($item['title'])?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <?php if (!empty($user)): ?>
    <div class="sidebarUser">
      <div class="sidebarUserName"><?=\App\Core\Helpers::h($user['name'] ?? '')?></div>
      <div class="sidebarUserLogin"><?=\App\Core\Helpers::h($user['login'] ?? '')?></div>
      <a class="btn" href="index.php?m=profile">Личный кабинет</a>
      <a class="link" href="logout.php">Выход</a>
    </div>
  <?php endif; ?>
</aside>
