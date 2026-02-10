<?php
// МАКЕТ: Новый заказ (без логики БД)
// Требования: использовать текущие CSS-переменные темы (layout.css)
// и сохранить многострочные инпуты (авто-рост textarea).
?>

<style>
  /* ===== New Order — scoped styles ===== */
  /*
    ВАЖНО: тут НЕ делаем адаптив.
    Ширина фиксированная (<=1400px), чтобы при зуме блоки масштабировались синхронно
    и НЕ наезжали друг на друга. Если экран уже — появится горизонтальная прокрутка.
  */
  .content{overflow-x:auto;}
  .gpt_on_wrap{
    display:flex;
    flex-direction:column;
    gap:12px;
    width:1400px;
    min-width:1400px;
    max-width:1400px;
  }
  .rightStatusBar{
    min-height:460px;
  }

  .invoiceNumber{
    resize: vertical;
    height:80px;
    
  }
  .neworder_WABlock{
    height:400px;
  }

  /* верхняя зона: слева (шапка + контрагент + wa), справа (старый статус) */
  .gpt_on_topLayout{
    display:grid;
    /* слева: (625 + 200 + 12 gap) = 837px, справа: 550px */
    grid-template-columns: 837px 550px;
    gap:6px;
    align-items:start;
  }
  .gpt_on_leftCol{display:flex;flex-direction:column;gap:6px;min-width:0;}
  .gpt_on_rightCol{min-width:0;}

  /* левый столбец: контрагент + вацапочная рядом */
  .gpt_on_leftGrid{
    display:grid;
    grid-template-columns: 625px 204px;
    gap:6px;
    align-items:start;
  }

  .gpt_on_topRow{
    display:grid;
    grid-template-columns:100px 1fr 215px;
    align-items:center;
  }

  .gpt_on_id{display:flex;align-items:center;gap:6px; width: 130px;}
  .gpt_on_id .gpt_on_badge{
    display:inline-flex;align-items:center;justify-content:center;
    height:38px;padding:0 6px;
    border-radius:12px;
    border:1px solid var(--line);
    background: color-mix(in srgb, var(--accent) 10%, transparent);
    font-weight:800;
  }

  .gpt_on_titleInput{width:100%;}
  .gpt_on_deadline{display:flex;gap:6px;align-items:center;justify-content:flex-end;}
  .gpt_on_deadline input{height:32px;}

  /* нижние блоки (работы и кнопки) на всю ширину */
  .gpt_on_fullRow{grid-column:1 / -1; min-width:0;}

  .gpt_on_cardHead{display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:10px;}
  .gpt_on_cardTitle{font-weight:800;}

  .gpt_on_kv{display:grid;grid-template-columns: 1fr;gap:6px;}
  .gpt_on_kv .muted{font-size:12px;}

  .gpt_on_btnRow{display:flex;gap:6px;flex-wrap:wrap;}
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

  .gpt_on_statusGrid{display:grid;grid-template-columns:1fr;gap:7px;}
  .gpt_on_statusBtns{display:flex;gap:8px;flex-wrap:wrap;}

  /* ===== Status (right card) — собрать как на схеме ===== */
  .gpt_st_twoCols{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:6px;
    align-items:start;
  }
  .gpt_st_flow{
    display:flex;
    align-items:center;
    gap:6px;
    flex-wrap:wrap;
  }
  .gpt_st_flow .gpt_st_arrow{
    color: var(--muted);
    font-weight:900;
    user-select:none;
  }
  .gpt_st_bigBtn{
    padding:10px 12px;
    border-radius:12px;
    border:1px solid var(--line);
    background: rgba(127,127,127,.08);
    cursor:pointer;
    color:var(--text);
    font-weight:800;
    white-space:nowrap;
  }
  .gpt_st_bigBtn:hover{background: rgba(127,127,127,.12);}
  .gpt_st_bigBtn.primary{
    border-color: color-mix(in srgb, var(--accent) 35%, var(--line));
    background: color-mix(in srgb, var(--accent) 12%, transparent);
  }


    /* центрирование рядов в статусах (кнопки и селекты) */
  .gpt_st_centerRow{
    display:grid;
    grid-template-columns: 1fr 25px 1fr 25px 1fr;
    justify-content:center;
    align-items:center;
    gap:6px;
    flex-wrap:wrap; /* если добавим ещё кнопку/селект — останется по центру */
  }

  /* ===== Payment mockup (as sketch) ===== */
  .gpt_pay_grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:10px;
    align-items:start;
  }
  .gpt_pay_left{
    display:flex;
    flex-direction:column;
    gap:8px;
    min-width:0;
  }
  .gpt_pay_left input,
  .gpt_pay_left select{
    width:100%;
  }
  .gpt_pay_right{
    display:flex;
    flex-direction:column;
    gap:10px;
    align-items:stretch;
  }
  .gpt_pay_totals{
    font-size:16px;
    color: var(--muted);
    line-height:1.6;
  }
  .gpt_pay_totals b{color:var(--text);}

  .gpt_pay_submit{
    height:44px;
    border-radius:12px;
    font-weight:900;
  }

  
  /* ===== Works list (work rows) ===== */
<?php include __DIR__ . '/assets/order_new.css'; ?>

  /* helper: small label above inputs */
  .gpt_on_lbl{font-size:12px;color:var(--muted);margin-bottom:0px;}
  .gpt_on_field{display:flex;flex-direction:column;gap:4px;min-width:0;}

  /* ===== Theme-override for embedded contragent block (gpt_cb_*) =====
     Не трогаем исходник, а перекрываем цвета под переменные темы. */
  .gpt_on_cbWrap .gpt_cb_root{
    width:100%;
    height:400px;
    
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

  <div class="gpt_on_topLayout">

    <!-- LEFT COLUMN: header + (contragent + wa) -->
    <div class="gpt_on_leftCol">

      <!-- ===== header row (id / title / deadline) ===== -->
      <div class="card">
        <div class="gpt_on_topRow">
          <div class="gpt_on_id">
            <div class="gpt_on_badge">Ю - 62335</div>
            
          </div>
          <div>
            <input class="gpt_on_titleInput" type="text" placeholder="Название / комментарий заказа" />
          </div>
<div class="gpt_on_deadline">
  <div class="muted" style="white-space:nowrap;"></div>

  <input id="deadlineDate" type="text" style="width:120px" placeholder="Дата">
  <input id="deadlineTime" type="text" class="timeselect" style="width:80px" placeholder="Время">

  <input id="deadlineDT" type="hidden" name="deadline_dt" value="">
</div>


          
        </div>
      </div>

      <!-- ===== contragent + wa (one row) ===== -->
      <div class="gpt_on_leftGrid">

        <!-- LEFT: встроенный обновлённый блок выбора контрагента (без лишней обёртки) -->
        <div class="gpt_on_cbWrap">
          <?php
            // ВАЖНО: файл использует $gpt_pdo (PDO) из inc/bootstrap.php
            $gptcb_embed_mode = true; // на будущее (если понадобится)
            include __DIR__ . '/../../views/partials/_newcontragentblock.php';
          ?>
        </div>

        <!-- RIGHT: "Вацапочная" -->
        <div class="card neworder_WABlock">
          <div class="gpt_on_cardHead">
            <div class="gpt_on_cardTitle">Вацапочная</div>
          </div>

          <div class="gpt_on_btnRow">
            <button type="button" class="gpt_on_btnSmall">WA Открыть чат</button>
            <button type="button" class="gpt_on_btnSmall">WA СБП QR (пустой)</button>
            <button type="button" class="gpt_on_btnSmall">WA QR с суммой</button>
            <button type="button" class="gpt_on_btnSmall primary">WA заказ оформлен</button>
            <button type="button" class="gpt_on_btnSmall">WA заказ готов</button>
          </div>

          <div class="muted" style="margin-top:10px;">
            Тут будут действия по шаблонам сообщений и интеграции.
          </div>
        </div>

      </div>
    </div>

    <!-- RIGHT COLUMN: старый статус от самого верха -->
    <div class="gpt_on_rightCol">
      <div class="card rightStatusBar">
        <!--<div class="gpt_on_cardHead">
          <div class="gpt_on_cardTitle">Статус</div>
        </div>-->

<div class="gpt_on_statusGrid">

  <!-- 2 селекта в одну строку: статус + исполнитель -->
  <div class="gpt_st_twoCols">
    <div class="gpt_on_field">
      <div class="gpt_on_lbl">Статусы</div>
      <select>
        <option>В работе</option>
        <option>Ожидание</option>
        <option>Просчёт</option>
      </select>
    </div>

    <div class="gpt_on_field">
      <div class="gpt_on_lbl">Допечатать / дизайн</div>
      <select>
        <option>Артём</option>
        <option>Не требуется</option>
      </select>
    </div>
  </div>

<div class="gpt_st_twoCols">
    <div class="gpt_on_field">
      <!--<div class="gpt_on_lbl">Печатник</div>!-->
      <select>
        <option>Печатник не выбран</option>
        <option>Илья</option>
        <option>Андрей</option>
        <option>Агафон</option>
      </select>
    </div>

    <div class="gpt_on_field">
      <!--<div class="gpt_on_lbl">Принтер</div>!-->
      <select>
        <option>Принтер не выбран</option>
        <option>konica</option>
        <option>ricoh</option>
      </select>
    </div>
  </div>

  <!-- цепочка кнопок — центр -->
  <div class="gpt_st_centerRow">
    <button type="button" class="gpt_st_bigBtn">Подготовлено</button>
    <span class="gpt_st_arrow">→</span>
    <button type="button" class="gpt_st_bigBtn">Отпечатано</button>
    <span class="gpt_st_arrow">→</span>
    <button type="button" class="gpt_st_bigBtn primary">Готово к выдаче</button>
  </div>

  <!-- селекты выдача/доставка — центр -->
  <div class="gpt_st_twoCols">
    <div class="gpt_on_field">
      <select>
        <option>Самовывоз</option>
        <option>Доставка</option>
      </select>
    </div>
    <div class="gpt_on_field">
      <select>
        <option>Не выдано</option>
        <option>Выдано</option>
      </select>
    </div>
  </div>

   <!-- <div class="gpt_on_cardTitle" style="font-size:13px;">Оплата</div>-->
<div class="gpt_on_lbl">Оплата</div>
  <!-- МАКЕТ оплаты -->
  <div class="gpt_pay_grid" id="gptPayBox">

    <div class="gpt_pay_left">
      <select placeholder="">
        
        <option>выберите тип оплаты</option>
        <option>Наличные/Карта/QR</option>
        <option>Оплата по счету</option>
      </select>

      <textarea type="text" placeholder="№ счета / чека / дата" class="invoiceNumber" ></textarea>

      <input class="gpt_pay_amount" type="number" step="0.01" min="0" inputmode="decimal" placeholder="Сумма" />
      <button type="button" class="gpt_on_btnSmall primary gpt_pay_submit">Внести</button>
    </div>

    <div class="gpt_pay_right">
      <div class="gpt_pay_totals">
        <div>Оплачено: <b>0</b></div>
        <div><b>Общая сумма:</b> 0.00</div>
        <div>Доплатить: <b>0.00</b></div>
      </div>

      
    </div>

  </div>

</div>

      </div>
    </div>


    <!-- ===== works + buttons (full width) ===== -->
    <div class="gpt_on_fullRow">

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

  </div><!-- topLayout -->

<script><?php include __DIR__ . '/assets/work_row.js'; ?></script>

</div><!-- gpt_on_wrap -->
<script>
(function(){
  var box = document.getElementById('gptPayBox');
  if (!box) return;

  var amount = box.querySelector('.gpt_pay_amount');
  if (!amount) return;

  amount.addEventListener('blur', function(){
    var v = (amount.value || '').replace(',', '.');
    if (!v) return;
    var n = parseFloat(v);
    if (isNaN(n)) { amount.value = ''; return; }
    amount.value = n.toFixed(2);
  });
})();
</script>


<script>
  $(function () {
    // datepicker
    $('#deadlineDate').datepicker({
      dateFormat: 'dd.mm.yy',   // 10.02.2026
      firstDay: 1,              // понедельник
      changeMonth: true,
      changeYear: true
    });

    // timepicker (твой, уже подключён)
    $('#deadlineTime').timepicker({
      timeFormat: 'H:i',
      step: 30,
      dropdown: true,
              interval: 30,
        minTime: '10',
        maxTime: '7:00pm',
/*        defaultTime: '10',*/
        startTime: '10:00',
      scrollbar: true
    });

    function syncDeadline(){
      var d = $('#deadlineDate').val(); // dd.mm.yy
      var t = $('#deadlineTime').val(); // HH:mm
      $('#deadlineDT').val(d && t ? (d + ' ' + t) : '');
    }

    $('#deadlineDate, #deadlineTime').on('change', syncDeadline);
  });
</script>
