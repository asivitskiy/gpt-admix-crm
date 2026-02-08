<?php
// МАКЕТ: Новый заказ (без логики БД)
// Требования: использовать текущие CSS-переменные темы (layout.css)
// и сохранить многострочные инпуты (авто-рост textarea).
?>

<style>
  /* ===== New Order — scoped styles ===== */
  .gpt_on_wrap{display:flex;flex-direction:column;gap:12px; max-width:1400px}

  .gpt_on_topRow{
    display:grid;
    grid-template-columns: 88px 1fr 220px 120px;
    gap:10px;
    align-items:center;
  }
  @media (max-width: 980px){
    .gpt_on_topRow{grid-template-columns: 1fr 1fr;}
  }

  .gpt_on_id{display:flex;align-items:center;gap:8px;}
  .gpt_on_id .gpt_on_badge{
    display:inline-flex;align-items:center;justify-content:center;
    height:32px;min-width:46px;padding:0 10px;
    border-radius:12px;
    border:1px solid var(--line);
    background: color-mix(in srgb, var(--accent) 10%, transparent);
    font-weight:800;
  }

  .gpt_on_titleInput{width:100%;}
  .gpt_on_deadline{display:flex;gap:8px;align-items:center;justify-content:flex-end;}
  .gpt_on_deadline input{height:32px;}

  .gpt_on_grid{
    display:grid;
    grid-template-columns: 1.2fr 1fr 0.9fr;
    gap:12px;
    align-items:start;
  }
  @media (max-width: 1200px){
    .gpt_on_grid{grid-template-columns: 1fr;}
  }

  .gpt_on_cardHead{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;}
  .gpt_on_cardTitle{font-weight:800;}

  .gpt_on_kv{display:grid;grid-template-columns: 1fr;gap:8px;}
  .gpt_on_kv .muted{font-size:12px;}

  .gpt_on_btnRow{display:flex;gap:8px;flex-wrap:wrap;}
  .gpt_on_btnSmall{
    padding:8px 10px;
    border-radius:12px;
    border:1px solid var(--line);
    background: rgba(127,127,127,.08);
    cursor:pointer;
    color:var(--text);
    font-weight:700;
    white-space:nowrap;
  }
  .gpt_on_btnSmall:hover{background: rgba(127,127,127,.12);}
  .gpt_on_btnSmall.primary{
    border-color: color-mix(in srgb, var(--accent) 35%, var(--line));
    background: color-mix(in srgb, var(--accent) 12%, transparent);
  }

  .gpt_on_inline{
    display:flex;
    gap:8px;
    align-items:center;
    flex-wrap:wrap;
  }

  .gpt_on_statusGrid{display:grid;grid-template-columns:1fr;gap:8px;}
  .gpt_on_statusBtns{display:flex;gap:8px;flex-wrap:wrap;}

  /* ===== Works list (work rows) ===== */
<?php include __DIR__ . '/assets/order_new.css'; ?>

  /* helper: small label above inputs */
  .gpt_on_lbl{font-size:12px;color:var(--muted);margin-bottom:4px;}
  .gpt_on_field{display:flex;flex-direction:column;gap:4px;min-width:0;}

  /* ===== Theme-override for embedded contragent block (gpt_cb_*) =====
     Не трогаем исходник, а перекрываем цвета под переменные темы. */
  .gpt_on_cbWrap .gpt_cb_root{
    width:100%;
    height:auto;
    min-height: 420px;
    background: var(--card);
    border-color: var(--line);
    color: var(--text);
  }
  .gpt_on_cbWrap .gpt_cb_cardTitle{border-bottom-color: var(--line);}
  .gpt_on_cbWrap .gpt_cb_label,
  .gpt_on_cbWrap .gpt_cb_hint{color: var(--muted);}
  .gpt_on_cbWrap .gpt_cb_input,
  .gpt_on_cbWrap .gpt_cb_dd_head,
  .gpt_on_cbWrap .gpt_cb_openBtn,
  .gpt_on_cbWrap .gpt_cb_iconBtn,
  .gpt_on_cbWrap .gpt_cb_openInField,
  .gpt_on_cbWrap .gpt_cb_dumpBtn{
    background: var(--bg);
    border-color: var(--line);
    color: var(--text);
  }
  .gpt_on_cbWrap .gpt_cb_dd_list,
  .gpt_on_cbWrap .gpt_cb_suggest{
    background: var(--card);
    border-color: var(--line);
  }
  .gpt_on_cbWrap .gpt_cb_suggestItem:hover,
  .gpt_on_cbWrap .gpt_cb_dd_item:hover{background: rgba(127,127,127,.12);}
  .gpt_on_cbWrap .gpt_cb_dd_item.active{background: color-mix(in srgb, var(--accent) 14%, transparent);}
  .gpt_on_cbWrap .gpt_cb_noteBox{background: rgba(127,127,127,.08); border-color: var(--line);}
  .gpt_on_cbWrap .gpt_cb_modal{background: rgba(0,0,0,.55);} /* оверлей */
  .gpt_on_cbWrap .gpt_cb_modalBody{background: var(--card);}
  .gpt_on_cbWrap .gpt_cb_modalCol{background: rgba(127,127,127,.08); border-color: var(--line); color: var(--text);}
  .gpt_on_cbWrap .gpt_cb_modalClose,
  .gpt_on_cbWrap .gpt_cb_modalBtn{background: var(--bg); border-color: var(--line); color: var(--text);}
  .gpt_on_cbWrap .gpt_cb_modalText{background: var(--card); color: var(--text);}
</style>

<div class="gpt_on_wrap">

  <!-- ===== header row (id / title / deadline) ===== -->
  <div class="card">
    <div class="gpt_on_topRow">
      <div class="gpt_on_id">
        <div class="gpt_on_badge">ЮО</div>
        <input type="text" value="-" placeholder="№" style="width:100%;height:32px;" />
      </div>

      <input class="gpt_on_titleInput" type="text" placeholder="Название / комментарий заказа (инпут1)" />

      <div class="gpt_on_deadline">
        <div class="muted" style="white-space:nowrap;">Сдача:</div>
        <input type="date" value="" />
      </div>

      <input type="time" value="" />
    </div>
  </div>


  <!-- ===== main 3 columns ===== -->
  <div class="gpt_on_grid">

    <!-- LEFT: встроенный обновлённый блок выбора контрагента -->
    <div class="card" style="padding:12px;">
      <div class="gpt_on_cardHead" style="margin-bottom:8px;">
        <div class="gpt_on_cardTitle">Контрагент</div>
      </div>

      <div class="gpt_on_cbWrap">
        <?php
          // ВАЖНО: файл использует $gpt_pdo (PDO) из inc/bootstrap.php
          // и умеет жить без БД на этапе макета.
          $gptcb_embed_mode = true; // на будущее (если понадобится)
          include __DIR__ . '/../../views/partials/_newcontragentblock.php';
        ?>
      </div>
    </div>


    <!-- MIDDLE: "Вацапочная" -->
    <div class="card">
      <div class="gpt_on_cardHead">
        <div class="gpt_on_cardTitle">Вацапочная</div>
      </div>

      <div class="gpt_on_btnRow">
        <button type="button" class="gpt_on_btnSmall">WA → Открыть чат</button>
        <button type="button" class="gpt_on_btnSmall">WA → СБП QR (пустой)</button>
        <button type="button" class="gpt_on_btnSmall">WA → QR с суммой</button>
        <button type="button" class="gpt_on_btnSmall primary">WA → заказ оформлен</button>
        <button type="button" class="gpt_on_btnSmall">WA → заказ готов</button>
      </div>

      <div class="muted" style="margin-top:10px;">
        Тут будут действия по шаблонам сообщений и интеграции.
      </div>
    </div>


    <!-- RIGHT: status + payment -->
    <div class="card">
      <div class="gpt_on_cardHead">
        <div class="gpt_on_cardTitle">Статус</div>
      </div>

      <div class="gpt_on_statusGrid">
        <select>
          <option>В работе</option>
          <option>Подготовлено</option>
          <option>Отпечатано</option>
        </select>

        <select>
          <option>Допечатать Артём</option>
          <option>Не требуется</option>
        </select>

        <div class="gpt_on_statusBtns">
          <button type="button" class="gpt_on_btnSmall">Подготовлено</button>
          <button type="button" class="gpt_on_btnSmall">Отпечатано</button>
        </div>

        <div class="gpt_on_statusBtns">
          <button type="button" class="gpt_on_btnSmall primary">Запросить счет</button>
          <button type="button" class="gpt_on_btnSmall">Счет запрошен</button>
        </div>

        <hr style="border:none;border-top:1px solid var(--line);margin:6px 0;">

        <div class="gpt_on_cardTitle" style="font-size:13px;">Оплата</div>

        <select>
          <option>—</option>
          <option>Наличные</option>
          <option>Карта</option>
          <option>Счет</option>
        </select>

        <textarea class="gpt_autogrow" placeholder="Номер счета/чека/дата"></textarea>

        <div class="gpt_on_inline">
          <input type="text" placeholder="сумма" style="width:110px;text-align:right;" />
          <button type="button" class="gpt_on_btnSmall">записать</button>
        </div>

        <div class="muted">Оплачено: 0</div>
        <div><b>Общая сумма:</b> 0.00</div>
        <div class="muted">Доплатить: 0.00</div>

        <hr style="border:none;border-top:1px solid var(--line);margin:6px 0;">

        <select>
          <option>Самовывоз</option>
          <option>Доставка</option>
        </select>

        <select>
          <option>Не выдано</option>
          <option>Выдано</option>
        </select>

      </div>
    </div>

  </div><!-- grid -->


  <!-- ===== works ===== -->
  
    <div class="onWorks" id="orderWorks">
      <?php for($i=0;$i<3;$i++): $idx=$i; include __DIR__ . '/parts/work_row.php'; endfor; ?>
    </div>

    <!-- confirm delete modal -->
    <div class="onModal" id="onWorkDelModal" hidden>
      <div class="onModalBox" role="dialog" aria-modal="true" aria-labelledby="onWorkDelTitle">
        <div class="onModalHead" id="onWorkDelTitle">Удалить работу?</div>
        <div class="onModalBody">Эта работа будет удалена из заказа. Отменить после удаления нельзя.</div>
        <div class="onModalFoot">
          <button type="button" class="onModalBtn" data-action="del_cancel">Отмена</button>
          <button type="button" class="onModalBtn danger" data-action="del_confirm">Удалить</button>
        </div>
      </div>
    </div>


<div class="card" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
      <button type="button" class="gpt_on_btnSmall primary">+ Добавить изделие</button>
      <button type="button" class="gpt_on_btnSmall">Скопировать изделие</button>
      <button type="button" class="gpt_on_btnSmall">Удалить выбранное</button>
      <span class="muted">(кнопки пока заглушки)</span>
    </div>

  </div>

</div>

<script><?php include __DIR__ . '/assets/work_row.js'; ?></script>
