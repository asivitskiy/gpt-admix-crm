<?php
/*
  Statusbar (draft v3)
  - Внутри шага: только заголовок + select
  - Между шагами: кнопка "передать дальше" (50x50, без текста, стрелка вправо)
  - Без App/классов. Только htmlspecialchars.
*/

if (!function_exists('gpt_sb_h')) {
  function gpt_sb_h($s){
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  }
}

$gpt_sb_id = isset($gpt_sb_id) && $gpt_sb_id ? $gpt_sb_id : ('gpt_sb_' . substr(md5((string)microtime(true)), 0, 8));

$gpt_sb_steps = isset($gpt_sb_steps) && is_array($gpt_sb_steps) ? $gpt_sb_steps : [
  ['title' => 'Прием заказ',       'options' => ['В работе','Ожидание','Просчёт']],
  ['title' => 'Допечать',  'options' => ['Не требуется','Артем','Даша','Илья']],
  ['title' => 'Печать',          'options' => ['Выполняется','Частично готово','Готово']],
  ['title' => 'Контроль',             'options' => ['Готово к выдаче','Возврат на доработку']],
  ['title' => 'Выдача/Доставка',             'options' => ['Готово к выдаче','Возврат на доработку']],
];

$gpt_sb_values = isset($gpt_sb_values) && is_array($gpt_sb_values) ? $gpt_sb_values : [];
?>

<div class="gpt_statusbar" id="<?=gpt_sb_h($gpt_sb_id)?>" data-gpt-sb="1">
  <?php foreach ($gpt_sb_steps as $i => $st):

    $opts  = (isset($st['options']) && is_array($st['options'])) ? $st['options'] : [];
    $title = isset($st['title']) ? $st['title'] : ('Шаг '.($i+1));

    $val = isset($gpt_sb_values[$i]) ? $gpt_sb_values[$i] : (isset($opts[0]) ? $opts[0] : '');
  ?>

    <div class="gpt_sb_step" data-step="<?=$i?>">
      <div class="gpt_sb_title"><?=gpt_sb_h($title)?></div>
      <div class="gpt_sb_row">
        <select>
          <?php foreach ($opts as $opt): ?>
            <option value="<?=gpt_sb_h($opt)?>" <?=$opt===$val?'selected':''?>><?=gpt_sb_h($opt)?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <?php if ($i < count($gpt_sb_steps)-1): ?>
      <div class="gpt_sb_conn">
        <div class="gpt_sb_connLine"></div>

        <button type="button" class="gpt_sb_ok" data-from="<?=$i?>" title="Передать дальше" aria-label="Передать дальше">
          <!-- стрелка/галка вправо почти на весь размер -->
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M5 12h12" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
            <path d="M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <div class="gpt_sb_connLine"></div>
      </div>
    <?php endif; ?>

  <?php endforeach; ?>
</div>

<script>
(function(){
  var root = document.getElementById(<?=json_encode($gpt_sb_id)?>);
  if (!root) return;
  if (root.__gptInited) return;
  root.__gptInited = true;

  root.addEventListener('click', function(e){
    var btn = e.target.closest ? e.target.closest('.gpt_sb_ok') : null;
    if (!btn) return;

    // короткая визуальная реакция (пока без логики)
    btn.classList.add('isPressed');
    setTimeout(function(){ btn.classList.remove('isPressed'); }, 160);

    // позже сюда привяжем реальную "передачу" по этапам
    // console.log('NEXT from step', btn.getAttribute('data-from'));
  });
})();
</script>
