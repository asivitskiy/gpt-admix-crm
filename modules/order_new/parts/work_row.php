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
        <col style="width:260px">  <!-- title -->
        <col style="width:200px">  <!-- device -->
        <col style="width:85px">   <!-- color -->
        <col style="width:80px">   <!-- opt -->
        <col style="width:55px">   <!-- Ш -->
        <col style="width:55px">   <!-- В -->
        <col style="width:200px">                      <!-- other/material (резиновая) -->
        <col style="width:90px">   <!-- price -->
        <col style="width:95px">   <!-- qty -->
        <col style="width:95px">  <!-- sum -->
      </colgroup>

      <!-- ROW 1 (верхний, низкий) -->
      <tr>
        <td class="onCell">
          <input class="input onBold wr_workname" type="text" data-field="title" value="коробка">
        </td>
        <td class="onCell">
          <select class="input wr_worktech" data-field="device">
            <option value="">—</option>
            <option selected>XEROX</option>
            <option>KONICA</option>
            <option>HP</option>
          </select>
        </td>
        <td class="onCell">
          <select class="input wr_workcolor" data-field="color">
            <option selected>4+0</option>
            <option>4+4</option>
            <option>1+0</option>
            <option>mix</option>
          </select>
        </td>
        <td class="onCell">
          <select class="input wr_worksize" data-field="opt">
            <option value="" selected></option>
            <option>А4</option>
            <option>А5</option>
            <option>А6</option>
          </select>
        </td>
        <td class="onCell ">
          <input type="text" class="input wr_workwidth" data-field="btn_w" placeholder="Ш">
        </td>
        <td class="onCell ">
          <input type="text" class="input wr_workwidth" data-field="btn_w" placeholder="В">
        </td>
        <td class="onCell">
          <select class="input wr_workmedia" data-field="other">
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
          <input class="input taR" type="text" data-field="qty" placeholder="Количество">
        </td>
        <td class="onCell onMoneyCell onSumBold">
          <input class="input taR onBold" type="text" data-field="sum" value="0.00" placeholder="Сумма">
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
