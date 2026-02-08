<?php
// Шаблон одной работы (строки) в заказе — макет без логики БД.
// Ожидаемые переменные: $idx (int)
if (!isset($idx)) { $idx = 0; }
$rowId = 'tmp_' . ($idx+1) . '_' . substr(md5((string)microtime(true)),0,6);
$pos = $idx + 1;
?>

<div class="onWorkRow" data-row-id="<?= htmlspecialchars($rowId, ENT_QUOTES, 'UTF-8') ?>" data-pos="<?= (int)$pos ?>">
  <div class="onWorkHandle" title="Перетащить (сортировка)"></div>

  <div class="onWorkTableWrap">
    <table class="onWorkTable">
      <colgroup>
        <col style="width:320px">  <!-- title -->
        <col style="width:240px">  <!-- device -->
        <col style="width:80px">   <!-- color -->
        <col style="width:70px">   <!-- opt -->
        <col style="width:34px">   <!-- Ш -->
        <col style="width:34px">   <!-- В -->
        <col>                      <!-- other/material (резиновая) -->
        <col style="width:90px">   <!-- price -->
        <col style="width:80px">   <!-- qty -->
        <col style="width:120px">  <!-- sum -->
      </colgroup>

      <!-- ROW 1 (верхний, низкий) -->
      <tr>
        <td class="onCell">
          <input class="input onBold" type="text" data-field="title" value="коробка">
        </td>
        <td class="onCell">
          <select class="input" data-field="device">
            <option value="">—</option>
            <option selected>XEROX</option>
            <option>KONICA</option>
            <option>HP</option>
          </select>
        </td>
        <td class="onCell">
          <select class="input" data-field="color">
            <option selected>4+0</option>
            <option>4+4</option>
            <option>1+0</option>
          </select>
        </td>
        <td class="onCell">
          <select class="input" data-field="opt">
            <option value="" selected></option>
            <option>mix</option>
          </select>
        </td>
        <td class="onCell onTinyBtn">
          <button type="button" class="onMiniBtn" data-field="btn_w">Ш</button>
        </td>
        <td class="onCell onTinyBtn">
          <button type="button" class="onMiniBtn" data-field="btn_h">В</button>
        </td>
        <td class="onCell">
          <select class="input" data-field="other">
            <option value="" selected></option>
            <option>Другое</option>
            <option>80 офсет</option>
            <option>300</option>
          </select>
        </td>
        
        <td class="onCell" rowspan="2">
на лист
        </td>
        

        <!-- Правый блок: верх (3 ячейки) -->
        <td class="onCell onMoneyCell">
          <input class="input taR" type="text" data-field="price" placeholder="цена" value="">
        </td>
        <td class="onCell onMoneyCell">
          <input class="input taR" type="text" data-field="qty" value="0">
        </td>
        <td class="onCell onMoneyCell onSumBold">
          <input class="input taR onBold" type="text" data-field="sum" value="0.00">
        </td>
      </tr>

      <!-- ROW 2 (нижний, высокий) -->
      <tr>
        <td class="onCell" colspan="2">
          <textarea class="input onTA" data-field="desc">описание работы</textarea>
        </td>
        <td class="onCell" colspan="5">
          <textarea class="input onTA" data-field="post">постпечатная обработка</textarea>
        </td>



        <!-- Правый блок: низ (2 ячейки: 0.00 + селект на 2 колонки) -->
        <td class="onCell onMoneyCell">
          <input class="input taR" type="text" data-field="pay" value="0.00">
        </td>
        <td class="onCell onMoneyCell" colspan="2">
          <select class="input" data-field="pay_sel">
            <option value="" selected></option>
          </select>
        </td>
      </tr>
    </table>

    <input type="hidden" class="onWorkSort" name="work_sort[]" value="<?= (int)$pos ?>">
  </div>

  <div class="onWorkActions">
    <button class="onActBtn" type="button" data-action="dup" title="Дублировать">⧉</button>
    <button class="onActBtn" type="button" data-action="gear" title="Настройки">⚙</button>
    <button class="onActBtn onDanger" type="button" data-action="del" title="Удалить">🗑</button>
  </div>
</div>
