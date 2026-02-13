<?php
/* ====== GPT: Order Contragent Block (dynamic load) ======
   Портирован из старой CRM.
   ВАЖНО: исходник был под mysql_* (PHP5). В новой CRM (PHP7.4) используем PDO.
   Требует: на странице уже есть $gpt_pdo (PDO) из inc/bootstrap.php.
*/

function gptcb_h($s){
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Текущий контрагент, если уже задан на странице
$gptcb_contragent_id = 0;
if (isset($contragent_id)) $gptcb_contragent_id = intval($contragent_id);
if ($gptcb_contragent_id <= 0 && isset($_GET['contragent_id'])) $gptcb_contragent_id = intval($_GET['contragent_id']);
if ($gptcb_contragent_id <= 0 && isset($_POST['contragent_id'])) $gptcb_contragent_id = intval($_POST['contragent_id']);

$gptcb_contragent_name = '';
if ($gptcb_contragent_id > 0 && isset($gpt_pdo) && ($gpt_pdo instanceof \PDO)){
  try {
    $st = $gpt_pdo->prepare('SELECT id, name FROM contragents WHERE id = :id LIMIT 1');
    $st->execute([':id' => $gptcb_contragent_id]);
    $r = $st->fetch();
    if ($r){
      $gptcb_contragent_id = (int)$r['id'];
      $gptcb_contragent_name = (string)$r['name'];
    }
  } catch (\Throwable $e) {
    // Тихо игнорируем: макет может жить без БД на первых этапах.
  }
}
?>

<style>

  .gpt_cb_warn{
  display:none;
  margin: 6px 0 8px 0;
  padding: 6px 10px;
  border-radius: 10px;
  background: #ffe6ea;
  border: 1px solid rgba(200,0,0,0.18);
  color: #b00020;
  font-size: 12px;
  line-height: 1.2;
}

.gpt_cb_dirty{
  margin-top:10px;
  padding:8px 10px;
  border-radius:10px;
  font-size:13px;
  line-height:1.25;
  background:#fff3cd;
  border:1px solid #ffeeba;
  color:#6b4e00;
}


/* ====== scoped styles: GPT Contragent Block ====== */
.gpt_cb_modalBody{
  flex: 1 1 auto;
  padding: 10px 12px;
  overflow: auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  border-radius:10px;
  background: #fff;
  box-sizing: border-box;
}

.gpt_cb_modalCol{
  margin: 0;
  padding: 8px 8px;
  border: 1px solid rgba(0,0,0,0.10);
  border-radius: 12px;
  background: rgba(0,0,0,0.02);
  font-size: 15px;
  line-height: 1.35;
  font-family: Consolas, Menlo, Monaco, "Courier New", monospace;
  white-space: pre-wrap;
  word-break: break-word;
  min-width: 0;
}

.gpt_cb_root{
  width: 610px;
  height: 460px;
  box-sizing: border-box;
  border: 1px solid rgba(0,0,0,0.14);
  border-radius: 14px;
  background: #fff;
  padding: 12px;
  overflow: visible; /* важно: чтобы выпадашки не резались */
  font-family: Arial, sans-serif;
  position: relative;
}

.gpt_cb_grid{
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  height: 100%;
  align-items: start;
}

.gpt_cb_col{
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-height: 0;
  min-width: 0;
}

.gpt_cb_cardTitle{
  font-weight: 700;
  font-size: 14px;
  padding-bottom: 6px;
  border-bottom: 1px solid rgba(0,0,0,0.10);
}

.gpt_cb_field{
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}

.gpt_cb_label{
  font-size: 12px;
  color: rgba(0,0,0,0.62);
  user-select: none;
}

.gpt_cb_hint{
  font-size: 11px;
  color: rgba(0,0,0,0.45);
  line-height: 1.15;
}

.gpt_cb_inputWrap{
  position: relative;
  display: block;
  min-width: 0;
}

.gpt_cb_input{
  width: 100%;
  height: 40px !important;
  border-radius: 12px !important;
  border: 1px solid rgba(0,0,0,0.14);
  padding: 8px 44px 8px 10px;   /* справа место под кнопку */
  font-size: 15px;
  box-sizing: border-box;
  background: #fff;
  outline: none;
}

/* красивый фокус как у единого поля */
.gpt_cb_input:focus{
  border-color: rgba(60,120,255,0.45);
}
.gpt_cb_input:focus + .gpt_cb_openBtn{
  border-color: rgba(60,120,255,0.45);
}

.gpt_cb_openBtn{
  width: 44px;
  height: 40px;
  border-radius: 0 12px 12px 0;
  border: 1px solid rgba(0,0,0,0.14);
  background: #fff;
  cursor: pointer;
  font-size: 16px;
  display:flex;
  align-items:center;
  justify-content:center;
  box-sizing: border-box;
  padding: 0;
}

.gpt_cb_iconBtn{
  width: 40px;
  height: 38px;
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,0.14);
  background: #fff;
  cursor: pointer;
  font-size: 16px;
  line-height: 38px;
  text-align: center;
}
.gpt_cb_iconBtn:hover{ background: #f6f6f6; }

/* suggestions for contragent search */
.gpt_cb_suggest{
  position: absolute;
  left: 0;
  right: 48px; /* не залезаем под кнопку ↗ */
  top: calc(100% + 6px);
  border: 1px solid rgba(0,0,0,0.14);
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 14px 40px rgba(0,0,0,0.18);
  z-index: 2147483647;
  max-height: 240px;
  overflow-y: auto;
  display: none;
}
.gpt_cb_suggest.open{ display:block; }
.gpt_cb_suggestItem{
  padding: 9px 10px;
  border-bottom: 1px solid rgba(0,0,0,0.08);
  cursor: pointer;
}
.gpt_cb_suggestItem:last-child{ border-bottom:none; }
.gpt_cb_suggestItem:hover{ background: #f3f6ff; }
.gpt_cb_suggestName{ font-size: 13px; font-weight: 700; color: rgba(0,0,0,0.86); }
.gpt_cb_suggestId{ font-size: 11px; color: rgba(0,0,0,0.50); margin-top: 2px; }

/* Dropdown base */
.gpt_cb_dd{ position: relative; min-width:0; }

.gpt_cb_dd_head{
  border: 1px solid rgba(0,0,0,0.14);
  border-radius: 12px;
  background: #fff;
  padding: 8px 10px;
  cursor: pointer;
  display: grid;
  grid-template-columns: 1fr 18px;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.gpt_cb_openInField{
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  width: 32px;
  height: 32px;
  border-radius: 10px;
  border: 1px solid rgba(0,0,0,0.14);
  background: #fff;
  padding: 0 !important;
  margin: 0 !important;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  line-height: 1 !important;
  box-sizing: border-box;
}

.gpt_cb_openInField:hover{ background:#f6f6f6; }

.gpt_cb_input:focus{
  border-color: rgba(60,120,255,0.45);
}
.gpt_cb_input:focus + .gpt_cb_openInField{
  border-color: rgba(60,120,255,0.45);
}

.gpt_cb_dd_arrow{
  text-align: right;
  color: rgba(0,0,0,0.55);
  font-size: 14px;
}

.gpt_cb_dd_lines{
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.gpt_cb_line1{ font-size: 16px; font-weight: 700; color: rgba(0,0,0,0.86); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gpt_cb_line2,.gpt_cb_line3,.gpt_cb_line4{ font-size: 15px; color: rgba(0,0,0,0.62); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gpt_cb_line4{ color: rgba(0,0,0,0.50); }

.gpt_cb_dd_list{
  position: absolute;
  left: 0;
  right: 0;
  top: calc(100% + 6px);
  border: 1px solid rgba(0,0,0,0.14);
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 14px 40px rgba(0,0,0,0.18);
  z-index: 2147483647;
  max-height: 260px;
  overflow-y: auto;
  display: none;
}
.gpt_cb_dd.open .gpt_cb_dd_list{ display:block; }

.gpt_cb_dd_item{
  padding: 9px 10px;
  border-bottom: 1px solid rgba(0,0,0,0.08);
  cursor: pointer;
}
.gpt_cb_dd_item:last-child{ border-bottom: none; }
.gpt_cb_dd_item:hover{ background: #f3f6ff; }
.gpt_cb_dd_item.active{ background: #e9efff; }

.gpt_cb_dd--2 .gpt_cb_dd_head{ min-height: 52px; }   /* контакты/адрес */
.gpt_cb_dd--4 .gpt_cb_dd_head{ min-height: 92px; }   /* реквизиты */

.gpt_cb_badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 999px;
  border: 1px solid rgba(0,0,0,0.14);
  color: rgba(0,0,0,0.62);
  margin-left: 8px;
}

/* spacer чтобы "Реквизиты" и "Номер для связи" совпали по верху */
.gpt_cb_syncSpacer{ height: 62px; }



/* вкладка-глаз в правом нижнем углу */
.gpt_cb_dumpBtn{
  position: absolute;
  right: 10px;
  bottom: 10px;
  width: 46px;
  height: 34px;
  border-radius: 12px 12px 14px 14px;
  border: 1px solid rgba(0,0,0,0.14);
  background: #fff;
  cursor: pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  padding: 0;
  z-index: 20;
  box-shadow: 0 8px 18px rgba(0,0,0,0.10);
}
.gpt_cb_dumpBtn:hover{ background:#f6f6f6; }
.gpt_cb_dumpBtn:active{ transform: translateY(1px); }

.gpt_cb_dumpIcon{
  width: 22px;
  height: 22px;
  fill: rgba(0,0,0,0.62);
}

/* модалка */
.gpt_cb_modal{
  position: absolute;
  /*inset: 0;*/
  top: 30px;
  left: 30px;
  padding-top:5px;
  border-radius:10px;
  background: rgba(83, 83, 83, 0.85);
  display:flex;
  align-items:center;
  justify-content:center;
  z-index: 2147483647;
-webkit-box-shadow: 8px 11px 5px -3px rgba(34, 60, 80, 0.39);
-moz-box-shadow: 8px 11px 5px -3px rgba(34, 60, 80, 0.39);
box-shadow: 8px 11px 5px -3px rgba(34, 60, 80, 0.39);
  
}
.gpt_cb_modalHidden{ display:none; }

.gpt_cb_modalBox{
  width: min(980px, calc(100% - 16px));
  height: calc(100% - 16px);
  max-height: calc(100% - 16px);
}

.gpt_cb_modalHead{
  display:flex;
  align-items:center;
  justify-content: space-between;
  
  border-bottom: 1px solid rgba(0,0,0,0.10);
}
.gpt_cb_modalTitle{
  font-size: 13px;
  font-weight: 700;
  color: rgba(0,0,0,0.82);
}
.gpt_cb_modalClose{
  width: 34px;
  height: 30px;
  border-radius: 10px;
  border: 1px solid rgba(0,0,0,0.14);
  background:#fff;
  cursor:pointer;
  font-size: 18px;
  line-height: 28px;
  padding: 0;
}
.gpt_cb_modalClose:hover{ background:#f6f6f6; }

.gpt_cb_modalText{
  width: 100%;
  height: 320px;
  resize: none;
  border: none;
  outline: none;
  padding: 10px 12px;
  font-size: 12px;
  line-height: 1.35;
  box-sizing: border-box;
  font-family: Consolas, Menlo, Monaco, "Courier New", monospace;
  background: #fff;
}

.gpt_cb_modalFoot{
  display:flex;
  gap: 8px;
  justify-content: flex-end;
  padding: 10px 12px;
  border-top: 1px solid rgba(0,0,0,0.10);
}
.gpt_cb_modalBtn{
  height: 34px;
  padding: 0 12px;
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,0.14);
  background:#fff;
  cursor:pointer;
}
.gpt_cb_modalBtn:hover{ background:#f6f6f6; }
.gpt_cb_modalBtnGhost{
  color: rgba(0,0,0,0.70);
}

.gpt_cb_modalText{
  width: 100%;
  flex: 1 1 auto;            /* занимает всё доступное место */
  height: auto;              /* вместо фиксированной высоты */
  min-height: 260px;
  resize: none;
  border: none;
  outline: none;
  padding: 10px 12px;
  font-size: 13px;
  line-height: 1.35;
  box-sizing: border-box;
  font-family: Consolas, Menlo, Monaco, "Courier New", monospace;
  background: #fff;

  white-space: pre-wrap;     /* чтобы длинные строки переносились */
  word-break: break-word;
  overflow: auto;
}


.gpt_cb_modalFoot{
  display:flex;
  gap: 8px;
  justify-content: flex-end;
  padding: 8px 12px;
  border-top: 1px solid rgba(0,0,0,0.10);
}
.gpt_cb_modalBtn{
  height: 32px;
  padding: 0 12px;
  border-radius: 12px;
}

.gpt_cb_line3{
  font-size: 12px;
  line-height: 1.15;
  opacity: 0.75;
  margin-top: 2px;
}

/* чтобы head влезал под 3 строки (если у тебя есть фиксированные высоты) */
.gpt_cb_dd--3 .gpt_cb_dd_head{
  min-height: 62px;
  align-items: flex-start;
  padding-top: 8px;
  padding-bottom: 8px;
}

/* примечание контрагента (внизу левой колонки) */
.gpt_cb_noteField{ margin-top: auto; }

.gpt_cb_noteBox{
  border: 1px solid rgba(0,0,0,0.14);
  border-radius: 12px;
  padding: 10px 10px;
  background: rgba(0,0,0,0.02);
  font-size: 13px;
  line-height: 1.25;
  white-space: pre-wrap;
  word-break: break-word;
  max-height: 120px;
  overflow: auto;
}

.gpt_cb_noteBox.empty{
  color: rgba(0,0,0,0.45);
}



</style>

<div class="gpt_cb_root" id="gpt_cb_root">
  <div class="gpt_cb_grid">

    <!-- LEFT -->
    <div class="gpt_cb_col">
      <div class="gpt_cb_cardTitle">Контрагент</div>

      <div class="gpt_cb_field">
        <div class="gpt_cb_label">Заказчик</div>

        <div class="gpt_cb_inputWrap">
          <input type="hidden" name="contragent_id" id="gpt_cb_contragent_id" value="...">

          <input type="text" name="contragent_name" autocomplete="off"
                 id="gpt_cb_contragent_name" class="gpt_cb_input"
                 placeholder="Начни вводить имя контрагента...">

          <button type="button" class="gpt_cb_openInField" id="gpt_cb_openCtorBtn" title="Открыть конструктор">↗</button>

          <div class="gpt_cb_suggest" id="gpt_cb_suggest"></div>
        </div>

        <div class="gpt_cb_hint" id="gpt_cb_idhint">ID: #<?php echo intval($gptcb_contragent_id); ?></div>
      </div>

      <div class="gpt_cb_field">
        <div class="gpt_cb_label">Реквизиты</div>

        <div class="gpt_cb_dd gpt_cb_dd--4" data-dd="req" id="gpt_cb_dd_req">
          <div class="gpt_cb_dd_head" tabindex="0" id="gpt_cb_req_head">
            <div class="gpt_cb_dd_lines" data-sel-id="0">
              <div class="gpt_cb_line1">—</div>
              <div class="gpt_cb_line2">&nbsp;</div>
              <div class="gpt_cb_line3">&nbsp;</div>
              <div class="gpt_cb_line4">&nbsp;</div>
            </div>
            <div class="gpt_cb_dd_arrow">▾</div>
          </div>
          <div class="gpt_cb_dd_list" id="gpt_cb_req_list"></div>
        </div>
      </div>

      <div class="gpt_cb_field">
        <div class="gpt_cb_label">Почта для счетов</div>

        <div class="gpt_cb_dd gpt_cb_dd--2" data-dd="invoice" id="gpt_cb_dd_invoice">
          <div class="gpt_cb_dd_head" tabindex="0" id="gpt_cb_invoice_head">
            <div class="gpt_cb_dd_lines" data-sel-id="0" data-email="">
              <div class="gpt_cb_line1">—</div>
              <div class="gpt_cb_line2">&nbsp;</div>
            </div>
            <div class="gpt_cb_dd_arrow">▾</div>
          </div>
          <div class="gpt_cb_dd_list" id="gpt_cb_invoice_list"></div>
        </div>
      </div>

      <div id="gpt_cb_dirtyBadge" class="gpt_cb_dirty" style="display:none;">
  Данные изменены — сохраните заказ
</div>

   <?   //<div class="gpt_cb_field gpt_cb_noteField">
        //  <div class="gpt_cb_label">Примечание</div>
        //  <div class="gpt_cb_noteBox empty" id="gpt_cb_noteBox">— нет примечания —</div>
        //</div>
    ?>
    </div>

    <!-- RIGHT -->
    <div class="gpt_cb_col">
      <div class="gpt_cb_cardTitle">Контакты</div>
      <div class="gpt_cb_warn" id="gpt_cb_contacts_warn">Не выбраны</div>

      <!-- сохраняем в заказе -->
      <input type="hidden" name="notification_number" id="gpt_cb_notification_number" value="">
      <input type="hidden" name="gpt_requisite_id"      id="gpt_cb_gpt_requisite_id" value="0">
      <input type="hidden" name="gpt_delivery_id"       id="gpt_cb_gpt_delivery_id" value="0">
      <input type="hidden" name="gpt_contact_call_id"   id="gpt_cb_gpt_contact_call_id" value="0">
      <input type="hidden" name="gpt_contact_notify_id" id="gpt_cb_gpt_contact_notify_id" value="0">
      <input type="hidden" name="gpt_invoice_email"     id="gpt_cb_gpt_invoice_email" value="0">

      <div class="gpt_cb_field">
        <div class="gpt_cb_label">Номер для оповещений</div>

        <div class="gpt_cb_dd gpt_cb_dd--2" data-dd="notify" id="gpt_cb_dd_notify">
          <div class="gpt_cb_dd_head" tabindex="0" id="gpt_cb_notify_head">
            <div class="gpt_cb_dd_lines" data-sel-id="0" data-phone="">
              <div class="gpt_cb_line1">—</div>
              <div class="gpt_cb_line2">&nbsp;</div>
            </div>
            <div class="gpt_cb_dd_arrow">▾</div>
          </div>
          <div class="gpt_cb_dd_list" id="gpt_cb_notify_list"></div>
        </div>
      </div>

      <div class="gpt_cb_field">
        <div class="gpt_cb_label">Номер для связи</div>

        <div class="gpt_cb_dd gpt_cb_dd--2" data-dd="call" id="gpt_cb_dd_call">
          <div class="gpt_cb_dd_head" tabindex="0" id="gpt_cb_call_head">
            <div class="gpt_cb_dd_lines" data-sel-id="0" data-req-id="0" data-phone="">
              <div class="gpt_cb_line1">—</div>
              <div class="gpt_cb_line2">&nbsp;</div>
            </div>
            <div class="gpt_cb_dd_arrow">▾</div>
          </div>
          <div class="gpt_cb_dd_list" id="gpt_cb_call_list"></div>
        </div>
      </div>

      <div class="gpt_cb_field">
        <div class="gpt_cb_label">Адрес доставки</div>

        <div class="gpt_cb_dd gpt_cb_dd--2" data-dd="addr" id="gpt_cb_dd_addr">
          <div class="gpt_cb_dd_head" tabindex="0" id="gpt_cb_addr_head">
            <div class="gpt_cb_dd_lines" data-sel-id="0">
              <div class="gpt_cb_line1">—</div>
              <div class="gpt_cb_line2">&nbsp;</div>
            </div>
            <div class="gpt_cb_dd_arrow">▾</div>
          </div>
          <div class="gpt_cb_dd_list" id="gpt_cb_addr_list"></div>
        </div>
      </div>

      
    </div>
    <!-- Кнопка "глаз" в правом нижнем углу -->
<button type="button" class="gpt_cb_dumpBtn" id="gpt_cb_dumpBtn" title="Показать все данные контрагента">
  <!-- простой глаз SVG -->
  <svg class="gpt_cb_dumpIcon" viewBox="0 0 24 24" aria-hidden="true">
    <path d="M12 5c5.5 0 9.8 4.6 10.8 6.1.3.5.3 1.3 0 1.8C21.8 14.4 17.5 19 12 19S2.2 14.4 1.2 12.9c-.3-.5-.3-1.3 0-1.8C2.2 9.6 6.5 5 12 5zm0 2C8 7 4.6 10.2 3.4 12c1.2 1.8 4.6 5 8.6 5s7.4-3.2 8.6-5C19.4 10.2 16 7 12 7zm0 2.2A2.8 2.8 0 1 1 12 15a2.8 2.8 0 0 1 0-5.8zm0 1.8A1 1 0 1 0 12 13a1 1 0 0 0 0-2z"/>
  </svg>
</button>

<!-- Модалка "текстовый режим" -->
<div class="gpt_cb_modal gpt_cb_modalHidden" id="gpt_cb_dumpModal" role="dialog" aria-modal="true">
  <div class="gpt_cb_modalBox">
    <div class="gpt_cb_modalHead">
      <div class="gpt_cb_modalTitle"></div>
      
    </div>

    <div class="gpt_cb_modalBody" id="gpt_cb_dumpBody">
      <pre class="gpt_cb_modalCol" id="gpt_cb_dumpColL"></pre>
      <pre class="gpt_cb_modalCol" id="gpt_cb_dumpColR"></pre>
    </div>

    <div class="gpt_cb_modalFoot">
      <button type="button" class="gpt_cb_modalBtn" id="gpt_cb_dumpCopy">Копировать</button>
      <button type="button" class="gpt_cb_modalBtn gpt_cb_modalBtnGhost" id="gpt_cb_dumpClose2">Закрыть</button>
    </div>
  </div>
</div>

  </div>



</div>

<script>
(function(){
  var root = document.getElementById('gpt_cb_root');
  if (!root) return;

  var API = '/_newcontragents/phpscripts.php';
  var gptCbPreserve = null; // { reqId, addrId, invoiceId, notifyId, callId }

  var inputName = document.getElementById('gpt_cb_contragent_name');
  var inputId   = document.getElementById('gpt_cb_contragent_id');
  var idHint    = document.getElementById('gpt_cb_idhint');
  var suggest   = document.getElementById('gpt_cb_suggest');
  var openCtorBtn = document.getElementById('gpt_cb_openCtorBtn');

  // ====== Dump modal (текстовый режим) ======
  var dumpBtn   = document.getElementById('gpt_cb_dumpBtn');
  var dumpModal = document.getElementById('gpt_cb_dumpModal');
  var dumpColL = document.getElementById('gpt_cb_dumpColL');
  var dumpColR = document.getElementById('gpt_cb_dumpColR');
  var dumpClose = document.getElementById('gpt_cb_dumpClose');
  var dumpClose2= document.getElementById('gpt_cb_dumpClose2');
  var dumpCopy  = document.getElementById('gpt_cb_dumpCopy');


  var reqList    = document.getElementById('gpt_cb_req_list');
  var invoiceList= document.getElementById('gpt_cb_invoice_list');
  var notifyList = document.getElementById('gpt_cb_notify_list');
  var callList   = document.getElementById('gpt_cb_call_list');
  var addrList   = document.getElementById('gpt_cb_addr_list');
  var noteBox = document.getElementById('gpt_cb_noteBox');


  var reqHead    = document.querySelector('#gpt_cb_req_head .gpt_cb_dd_lines');
  var invoiceHead= document.querySelector('#gpt_cb_invoice_head .gpt_cb_dd_lines');
  var notifyHead = document.querySelector('#gpt_cb_notify_head .gpt_cb_dd_lines');
  var callHead   = document.querySelector('#gpt_cb_call_head .gpt_cb_dd_lines');
  var addrHead   = document.querySelector('#gpt_cb_addr_head .gpt_cb_dd_lines');

  var notifHidden = document.getElementById('gpt_cb_notification_number');
  
  var hidReq    = document.getElementById('gpt_cb_gpt_requisite_id');
  var hidAddr   = document.getElementById('gpt_cb_gpt_delivery_id');
  var hidCall   = document.getElementById('gpt_cb_gpt_contact_call_id');
  var hidNotify = document.getElementById('gpt_cb_gpt_contact_notify_id');
  var hidInvoice= document.getElementById('gpt_cb_gpt_invoice_email');

  var warnContacts = document.getElementById('gpt_cb_contacts_warn');
    function showWarnContacts(){ if (warnContacts) warnContacts.style.display = 'block'; }
    function hideWarnContacts(){ if (warnContacts) warnContacts.style.display = 'none'; }
    function userTouchedBlock(){ hideWarnContacts(); }




  // если юзер руками выбрал "Контакт для связи" — больше не автопереключаем,
  // пока не поменяют реквизит или контрагента
  var gptCbCallManual = false;
  var gptCbCallManualId = 0;

  var state = {
    contragentId: parseInt(inputId.value || '0', 10) || 0,
    contragentName: inputName.value || '',
    contragentNote: '',
    requisites: [],
    contacts: [],
    delivery: []
  };
  
  function getSelectedById(arr, id){
  id = parseInt(id || 0, 10) || 0;
  if (!id || !arr) return null;
  for (var i=0;i<arr.length;i++){
    if ((arr[i].gpt_id||0) === id) return arr[i];
  }
  return null;
}

function setActiveInList(listEl, id){
  if (!listEl) return;
  var items = listEl.querySelectorAll('.gpt_cb_dd_item');
  for (var i=0;i<items.length;i++){
    items[i].classList.remove('active');
    var x = parseInt(items[i].getAttribute('data-id')||'0',10) || 0;
    if (x && x === id) items[i].classList.add('active');
  }
}

function applyInitSelections(){
  if (initApplied) return;
  initApplied = true;

  // 1) реквизит
  if (initReqId > 0){
    var r = getSelectedById(state.requisites, initReqId);
    if (r){
      // имя привязанного контакта (как у тебя в buildReqList)
      var cname = '—';
      for (var i=0;i<(state.contacts||[]).length;i++){
        if ((state.contacts[i].gpt_requisite_id||0) === initReqId){
          cname = state.contacts[i].gpt_name || '—';
          break;
        }
      }
      setReqHead(r, cname);
      setActiveInList(reqList, initReqId);

      // важно: чтобы авто-выбор CALL учитывал этот реквизит
      buildContactsLists(state.contacts || []);
    }
  }

  // 2) адрес доставки
  if (initAddrId > 0){
    var a = getSelectedById(state.delivery, initAddrId);
    if (a){
      setAddrHead(a);
      setActiveInList(addrList, initAddrId);
    }
  }

  // 3) почта для счетов
  if (initInvoiceId > 0){
    var cI = getSelectedById(state.contacts, initInvoiceId);
    if (cI){
      setContactHead(invoiceHead, cI);
      setActiveInList(invoiceList, initInvoiceId);
    }
  }

  // 3) контакт для оповещений
  if (initNotifyId > 0){
    var cN = getSelectedById(state.contacts, initNotifyId);
    if (cN){
      setContactHead(notifyHead, cN);
      setActiveInList(notifyList, initNotifyId);
      if (notifHidden) notifHidden.value = cN.gpt_phone ? cN.gpt_phone : '';
    }
  }

  // 4) контакт для связи
  if (initCallId > 0){
    var cC = getSelectedById(state.contacts, initCallId);
    if (cC){
      setContactHead(callHead, cC);
      setActiveInList(callList, initCallId);

      // фиксируем как "выбрано руками", чтобы автоподбор не трогал
      gptCbCallManual = true;
      gptCbCallManualId = initCallId;
    }
  }
}


var init = (window.crmOrderInit && window.crmOrderInit.contragent) ? window.crmOrderInit.contragent : null;
var initId = init ? (parseInt(init.id, 10) || 0) : 0;

var initReqId    = init ? (parseInt(init.gpt_requisite_id,10) || 0) : 0;
var initAddrId   = init ? (parseInt(init.gpt_delivery_id,10) || 0) : 0;
var initNotifyId  = init ? (parseInt(init.gpt_contact_notify_id,10) || 0) : 0;
var initCallId    = init ? (parseInt(init.gpt_contact_call_id,10) || 0) : 0;
var initInvoiceId = init ? (parseInt(init.gpt_invoice_email,10) || 0) : 0;

var initApplied = false;

// если контактов в заказе нет (или заказ новый) — показать "Не выбраны"
if ((initNotifyId <= 0) || (initCallId <= 0)) {
  showWarnContacts();
}


if (initId > 0) {
  inputId.value = initId;
  if (init && typeof init.name === 'string') inputName.value = init.name;

  // подтянуть реквизиты/контакты/адреса
  loadAllForContragent(initId);
}

function buildContragentDumpCols(){
  var cid = parseInt(inputId.value || '0', 10) || 0;
  var cname = (inputName.value || '').trim();

  var reqId    = reqHead ? parseInt(reqHead.getAttribute('data-sel-id')||'0',10) : 0;
  var addrId   = addrHead ? parseInt(addrHead.getAttribute('data-sel-id')||'0',10) : 0;
  var notifyId = notifyHead ? parseInt(notifyHead.getAttribute('data-sel-id')||'0',10) : 0;
  var callId   = callHead ? parseInt(callHead.getAttribute('data-sel-id')||'0',10) : 0;

  var req  = getSelectedById(state.requisites, reqId);
  var addr = getSelectedById(state.delivery, addrId);
  var cN   = getSelectedById(state.contacts, notifyId);
  var cC   = getSelectedById(state.contacts, callId);

  var reqContactName = '—';
  if (req && state.contacts && state.contacts.length){
    for (var i=0;i<state.contacts.length;i++){
      if ((state.contacts[i].gpt_requisite_id||0) === (req.gpt_id||0)){
        reqContactName = state.contacts[i].gpt_name || '—';
        break;
      }
    }
  }

  // левая колонка: контрагент + реквизиты
  var L = [];
  L.push('КОНТРАГЕНТ');
  L.push('ID: ' + (cid ? ('#'+cid) : '—'));
  L.push('Имя: ' + (cname || '—'));
  L.push('');
  L.push('РЕКВИЗИТЫ');
  L.push('ID: ' + (req ? ('#'+(req.gpt_id||0)) : '—'));
  L.push('Название: ' + (req ? (req.gpt_legal_name||'—') : '—'));
  L.push('ИНН: ' + (req ? (req.gpt_inn||'—') : '—'));
  L.push('Р/с: ' + (req ? (req.gpt_rs||'—') : '—'));
  L.push('Контакт (привязан): ' + (reqContactName || '—'));

  // правая колонка: контакты + доставка
  var R = [];
  R.push('КОНТАКТЫ');
R.push('Оповещения: ' + (cN ? ((cN.gpt_name||'-') + ' / ' + (cN.gpt_phone||'-') + (cN.gpt_email ? (' / ' + cN.gpt_email) : '')) : '-'));
R.push('Связь: '      + (cC ? ((cC.gpt_name||'-') + ' / ' + (cC.gpt_phone||'-') + (cC.gpt_email ? (' / ' + cC.gpt_email) : '')) : '-'));
R.push('TG chat_id (оповещ.): ' + (cN ? (cN.gpt_chat_id || '-') : '-'));
R.push('TG chat_id (связь): '   + (cC ? (cC.gpt_chat_id || '-') : '-'));
  R.push('Оповещение: ' + ((notifHidden && notifHidden.value) ? notifHidden.value : '—'));
  R.push('');
  R.push('АДРЕС ДОСТАВКИ');
  R.push('ID: ' + (addr ? ('#'+(addr.gpt_id||0)) : '—'));
  R.push('Название: ' + (addr ? (addr.gpt_title||'—') : '—'));
  R.push('Адрес: ' + (addr ? (addr.gpt_address||'—') : '—'));

  return { left: L.join('\n'), right: R.join('\n') };
}


function openDumpModal(){
  if (!dumpModal || !dumpColL || !dumpColR) return;
  var c = buildContragentDumpCols();
  dumpColL.textContent = c.left;
  dumpColR.textContent = c.right;
  dumpModal.classList.remove('gpt_cb_modalHidden');

  // скролл в начало
  var body = document.getElementById('gpt_cb_dumpBody');
  if (body) body.scrollTop = 0;
}

function closeDumpModal(){
  if (!dumpModal) return;
  dumpModal.classList.add('gpt_cb_modalHidden');
}

if (dumpBtn) dumpBtn.addEventListener('click', function(e){
  e.preventDefault();
  e.stopPropagation();
  openDumpModal();
});

if (dumpClose) dumpClose.addEventListener('click', closeDumpModal);
if (dumpClose2) dumpClose2.addEventListener('click', closeDumpModal);

// клик по фону модалки — закрыть
if (dumpModal) dumpModal.addEventListener('click', function(e){
  if (e.target === dumpModal) closeDumpModal();
});

// ESC — закрыть
document.addEventListener('keydown', function(e){
  if (!dumpModal || dumpModal.classList.contains('gpt_cb_modalHidden')) return;
  if (e && e.keyCode === 27) closeDumpModal();
});

// копировать
if (dumpCopy) dumpCopy.addEventListener('click', function(){
  var c = buildContragentDumpCols();
  var all = c.left + '\n\n' + c.right;

  // современный способ
  if (navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(all);
    return;
  }

  // fallback
  try{
    var ta = document.createElement('textarea');
    ta.value = all;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
  } catch(e){}
});


  

  
  function escapeHtml(s){
    return String(s)
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }

  function safeStr(x){ return (x == null) ? '' : String(x); }

function contact3Lines(c){
  var name  = (safeStr(c && c.gpt_name)).trim() || '—';
  var phone = (safeStr(c && c.gpt_phone)).trim() || '—';
  var email = (safeStr(c && c.gpt_email)).trim() || '—';
  return { name:name, phone:phone, email:email };
}

  function fetchJson(url){
    return fetch(url, { credentials: 'same-origin' }).then(function(r){ return r.json(); });
  }

  function closeAllDropdowns(){
    var dds = root.querySelectorAll('.gpt_cb_dd.open');
    for (var i=0;i<dds.length;i++) dds[i].classList.remove('open');
  }

  function openSuggest(items){
    var html = '';
    for (var i=0;i<items.length;i++){
      var it = items[i];
      html += ''
        + '<div class="gpt_cb_suggestItem" data-id="'+it.id+'" data-name="'+escapeHtml(it.name)+'">'
        + '  <div class="gpt_cb_suggestName">'+escapeHtml(it.name)+'</div>'
        + '  <div class="gpt_cb_suggestId">#'+it.id+'</div>'
        + '</div>';
    }
    if (!html) {
      suggest.innerHTML = '<div class="gpt_cb_suggestItem" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_suggestName">Ничего не найдено</div></div>';
    } else {
      suggest.innerHTML = html;
    }
    suggest.classList.add('open');
  }

  function closeSuggest(){
    suggest.classList.remove('open');
  }

  // адрес: 2 строки по символам (как ты просил)
  function split2(text, n1, n2){
    text = (text || '').trim();
    if (!text) return ['—', ' '];
    var a = text.slice(0, n1);
    var b = text.slice(n1, n1 + n2);
    if (text.length > n1 + n2) b = (b.trim() + '…');
    return [a || '—', b || ' '];
  }

  //function setContragent(id, name){
  //  state.contragentId = id;
  //  state.contragentName = name;
//
  //  inputId.value = id;
  //  inputName.value = name;
  //  if (idHint) idHint.textContent = 'ID: #' + id;
//
  //  closeSuggest();
  //  loadAllForContragent(id);
  //}
function gptCbCaptureSelection(){
  function selId(head){
    if (!head || !head.getAttribute) return 0;
    return parseInt(head.getAttribute('data-sel-id') || '0', 10) || 0;
  }
  return {
    reqId: selId(reqHead),
    notifyId: selId(notifyHead),
    callId: selId(callHead),
    addrId: selId(addrHead)
  };
}

  function setReqHead(req, contactName){
    if (!reqHead) return;
    reqHead.setAttribute('data-sel-id', req ? (req.gpt_id || 0) : 0);
    if (hidReq) hidReq.value = req ? (req.gpt_id || 0) : 0;

    reqHead.innerHTML =
      '<div class="gpt_cb_line1">'+escapeHtml(req ? (req.gpt_legal_name || '—') : '—')+'</div>' +
      '<div class="gpt_cb_line2">ИНН: '+escapeHtml(req ? (req.gpt_inn || '—') : '—')+'</div>' +
      '<div class="gpt_cb_line3">Р/с: '+escapeHtml(req ? (req.gpt_rs || '—') : '—')+'</div>' +
      '<div class="gpt_cb_line4">Контакт: '+escapeHtml(contactName || '—')+'</div>';
  }

function setContactHead(headEl, c){
  if (!headEl) return;
  var t = contact3Lines(c);
  headEl.setAttribute('data-sel-id', c ? (c.gpt_id||0) : 0);
  if (headEl === notifyHead && hidNotify) hidNotify.value = c ? (c.gpt_id || 0) : 0;
  if (headEl === callHead   && hidCall)   hidCall.value   = c ? (c.gpt_id || 0) : 0;
  if (headEl === invoiceHead&& hidInvoice) hidInvoice.value = c ? (c.gpt_id || 0) : 0;

  headEl.innerHTML =
    '<div class="gpt_cb_line1">'+escapeHtml(t.name)+'</div>' +
    '<div class="gpt_cb_line2">'+escapeHtml(t.phone)+'</div>' +
    '<div class="gpt_cb_line3">'+escapeHtml(t.email)+'</div>';
}
function cutText(text, max){
  text = (text || '').trim();
  if (!text) return '—';
  return (text.length > max) ? (text.slice(0, max).trim() + '…') : text;
}

function setContragentNote(note){
  state.contragentNote = note || '';
  if (!noteBox) return;

  var t = (note || '').replace(/\r\n/g, '\n').trim();
  if (!t){
    noteBox.classList.add('empty');
    noteBox.textContent = '— нет примечания —';
    return;
  }

  noteBox.classList.remove('empty');
  noteBox.textContent = t;
}


var gptCbDirty = false;
var gptCbSuppressDirty = 0; // чтобы гасить "авто" изменения при reload/auto-pick

function gptCbSetDirty(on){
  gptCbDirty = !!on;
  var b = document.getElementById('gpt_cb_dirtyBadge');
  if (b) b.style.display = gptCbDirty ? '' : 'none';
}

function gptCbMarkDirty(){
  if (gptCbSuppressDirty) return;
  if (!gptCbDirty) gptCbSetDirty(true);
}

// если надо временно запретить dirty (на авто-подстановках)
function gptCbWithSuppressDirty(fn){
  gptCbSuppressDirty++;
  try { fn && fn(); } finally { gptCbSuppressDirty--; }
}



function setAddrHead(a){
  if (!addrHead) return;
  var title = a ? (a.gpt_title || '—') : '—';
  var addr  = a ? (a.gpt_address || '') : '';
  addrHead.setAttribute('data-sel-id', a ? (a.gpt_id||0) : 0);
  if (hidAddr) hidAddr.value = a ? (a.gpt_id || 0) : 0;

  addrHead.innerHTML =
    '<div class="gpt_cb_line1">'+escapeHtml(cutText(title, 60))+'</div>' +
    '<div class="gpt_cb_line2">'+escapeHtml(cutText(addr, 80))+'</div>';
}

  function buildReqList(items, contacts, preferId){
    if (!reqList) return;

    if (!items || !items.length){
      reqList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">— нет реквизитов —</div></div>';
      setReqHead(null, null);
      showWarnContacts();
      return;
    }
    hideWarnContacts();

    // 1) пробуем сохранить выбранное (если передали preferId), иначе — текущее в head
    var prevId = 0;
    if (preferId) prevId = parseInt(preferId,10) || 0;
    if (!prevId && reqHead){
      prevId = parseInt(reqHead.getAttribute('data-sel-id') || '0', 10) || 0;
    }

    function findById(id){
      id = parseInt(id,10) || 0;
      if (!id) return null;
      for (var j=0;j<items.length;j++){
        if (parseInt(items[j].gpt_id||0,10) === id) return items[j];
      }
      return null;
    }

    var sel = findById(prevId);

    // 2) если не нашли — выбираем default/первый
    if (!sel){
      sel = items[0];
      for (var i=0;i<items.length;i++){
        if (parseInt(items[i].gpt_is_default||0,10) === 1){ sel = items[i]; break; }
      }
    }

// имя связанного контакта (для шапки)
var reqContactName = '';
if (sel && contacts && contacts.length){
  for (var k=0;k<contacts.length;k++){
    if (parseInt(contacts[k].gpt_requisite_id||0,10) === parseInt(sel.gpt_id||0,10)){
      reqContactName =
        (contacts[k].gpt_name  || '') ||
        (contacts[k].gpt_phone || '') ||
        (contacts[k].gpt_email || '');
      break;
    }
  }
}
setReqHead(sel, reqContactName);



    var html = '';
    for (var i=0;i<items.length;i++){
      var r = items[i];
      var id = parseInt(r.gpt_id||0,10) || 0;

      var t1 = (r.gpt_legal_name||'—');
      var t2 = 'ИНН: ' + (r.gpt_inn||'—');
      var t3 = '';
      // показать связанный контакт в списке (если есть)
      if (contacts && contacts.length){
        for (var j=0;j<contacts.length;j++){
          if (parseInt(contacts[j].gpt_requisite_id||0,10) === id){
            t3 = 'Контакт: ' + (contacts[j].gpt_name||'—');
            break;
          }
        }
      }

      html += ''
        + '<div class="gpt_cb_dd_item'+((sel && id===parseInt(sel.gpt_id||0,10))?' active':'')+'" data-id="'+id+'">'
        + '  <div class="gpt_cb_line1">'+escapeHtml(t1)+'</div>'
        + '  <div class="gpt_cb_line2">'+escapeHtml(t2)+'</div>'
        + (t3 ? '  <div class="gpt_cb_line3">'+escapeHtml(t3)+'</div>' : '')
        + '</div>';
    }
    reqList.innerHTML = html;
  }

function buildContactsLists(contacts){

  function makeNotifyNone(){
    return { gpt_id: 0, gpt_name: '— не беспокоить —', gpt_phone: '', gpt_email: '' };
  }


  function makeInvoiceNone(){
    return { gpt_id: 0, gpt_name: '— не беспокоить —', gpt_phone: '', gpt_email: '' };
  }

  function pickInvoice(){
    for (var i=0;i<contacts.length;i++){
      if (parseInt(contacts[i].gpt_is_invoice_default||0,10) === 1) return contacts[i];
    }
    return null; // вместо contacts[0]
  }

function pickNotify(){
  for (var i=0;i<contacts.length;i++){
    if (parseInt(contacts[i].gpt_is_notify_default||0,10) === 1) return contacts[i];
  }
  return null; // вместо contacts[0]
}

  function pickCall(){
    for (var i=0;i<contacts.length;i++){
      if (parseInt(contacts[i].gpt_is_default||0,10) === 1) return contacts[i];
    }
    return contacts[0] || null;
  }

  if (!contacts || !contacts.length){
    if (invoiceList) invoiceList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">— нет контактов —</div></div>';
    notifyList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">— нет контактов —</div></div>';
    callList.innerHTML   = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">— нет контактов —</div></div>';

    setContactHead(invoiceHead, makeInvoiceNone());
    setContactHead(notifyHead, makeNotifyNone());
    setContactHead(callHead, null);

    if (notifHidden) notifHidden.value = '';
    if (hidInvoice) hidInvoice.value = 0;
    return;
  }

  var invoiceSel= pickInvoice(); // может быть null => "не беспокоить"
  var notifySel = pickNotify(); // может быть null => "не беспокоить"
  var callSel   = pickCall();

  // шапки
  setContactHead(invoiceHead, invoiceSel ? invoiceSel : makeInvoiceNone());
  setContactHead(notifyHead, notifySel ? notifySel : makeNotifyNone());
  setContactHead(callHead, callSel);

  // hidden notification_number (в заказ)
  if (notifHidden) notifHidden.value = (notifySel && notifySel.gpt_phone) ? notifySel.gpt_phone : '';


  // список invoice: сначала "не беспокоить", потом контакты
  var htmlInvoice = '';
  htmlInvoice += ''
    + '<div class="gpt_cb_dd_item'+(!invoiceSel ? ' active':'')+'" data-id="0" data-email="">'
    + '  <div class="gpt_cb_line1">— не беспокоить —</div>'
    + '  <div class="gpt_cb_line2"></div>'
    + '  <div class="gpt_cb_line3"></div>'
    + '</div>';

  // список notify: сначала "не беспокоить", потом контакты
  var htmlNotify = '';
  htmlNotify += ''
    + '<div class="gpt_cb_dd_item'+(!notifySel ? ' active':'')+'" data-id="0" data-phone="">'
    + '  <div class="gpt_cb_line1">— не беспокоить —</div>'
    + '  <div class="gpt_cb_line2"></div>'
    + '  <div class="gpt_cb_line3"></div>'
    + '</div>';

  var htmlCall = '';

  for (var i=0;i<contacts.length;i++){
    var c = contacts[i];
    var id = c.gpt_id || 0;

    var badgeNotify = (parseInt(c.gpt_is_notify_default||0,10)===1) ? '<span class="gpt_cb_badge">по умолч.</span>' : '';
    var badgeInvoice = (parseInt(c.gpt_is_invoice_default||0,10)===1) ? '<span class="gpt_cb_badge">по умолч.</span>' : '';
    var badgeCall   = (parseInt(c.gpt_is_default||0,10)===1) ? '<span class="gpt_cb_badge">по умолч.</span>' : '';
    var badgeReq    = (parseInt(c.gpt_requisite_id||0,10)>0) ? '<span class="gpt_cb_badge">🔗 рекв.</span>' : '';

    htmlNotify += ''
      + '<div class="gpt_cb_dd_item'+(notifySel && id===notifySel.gpt_id ? ' active':'')+'" data-id="'+id+'" data-phone="'+escapeHtml(c.gpt_phone||'')+'">'
      + '  <div class="gpt_cb_line1">'+escapeHtml(c.gpt_name||'—')+badgeNotify+'</div>'
      + '  <div class="gpt_cb_line2">'+escapeHtml(c.gpt_phone||'—')+'</div>'
      + '  <div class="gpt_cb_line3">'+escapeHtml(c.gpt_email||'—')+'</div>'
      + '</div>';

    htmlInvoice += ''
      + '<div class="gpt_cb_dd_item'+(invoiceSel && id===invoiceSel.gpt_id ? ' active':'')+'" data-id="'+id+'" data-email="'+escapeHtml(c.gpt_email||'')+'">'
      + '  <div class="gpt_cb_line1">'+escapeHtml(c.gpt_name||'—')+badgeInvoice+'</div>'
      + '  <div class="gpt_cb_line2">'+escapeHtml(c.gpt_email||'—')+'</div>'
      + '  <div class="gpt_cb_line3">'+escapeHtml(c.gpt_phone||'—')+'</div>'
      + '</div>';

    htmlCall += ''
      + '<div class="gpt_cb_dd_item'+(callSel && id===callSel.gpt_id ? ' active':'')+'" data-id="'+id+'" data-phone="'+escapeHtml(c.gpt_phone||'')+'" data-req-id="'+(c.gpt_requisite_id||0)+'">'
      + '  <div class="gpt_cb_line1">'+escapeHtml(c.gpt_name||'—')+badgeCall+badgeReq+'</div>'
      + '  <div class="gpt_cb_line2">'+escapeHtml(c.gpt_phone||'—')+'</div>'
      + '  <div class="gpt_cb_line3">'+escapeHtml(c.gpt_email||'—')+'</div>'
      + '</div>';
  }

  if (invoiceList) invoiceList.innerHTML = htmlInvoice;
  notifyList.innerHTML = htmlNotify;
  callList.innerHTML   = htmlCall;
}


function autoPickCallContactByRequisite(reqId){
  reqId = parseInt(reqId || 0, 10) || 0;
  if (!reqId) return;
  if (!state || !state.contacts || !state.contacts.length) return;
  if (!callHead || !callList) return;

  // найти связанный контакт: предпочтение contact.gpt_is_default==1, иначе первый попавшийся
  var linked = null;
  for (var i=0;i<state.contacts.length;i++){
    if (parseInt(state.contacts[i].gpt_requisite_id||0,10) === reqId){
      linked = state.contacts[i];
      if (parseInt(linked.gpt_is_default||0,10) === 1) break;
    }
  }
  if (!linked) return; // нет связанного — не трогаем текущий выбор

  // поставить в head "Контакт для связи"
  setContactHead(callHead, linked);

  // подсветить active в списке "call"
  var items = callList.querySelectorAll('.gpt_cb_dd_item');
  for (var k=0;k<items.length;k++){
    items[k].classList.remove('active');
    var id = parseInt(items[k].getAttribute('data-id')||'0',10) || 0;
    if (id && id === (linked.gpt_id||0)) items[k].classList.add('active');
  }
}




function buildAddrList(items, preferId){
    if (!addrList) return;

    if (!items || !items.length){
      addrList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">— нет адресов —</div></div>';
      setAddrHead(null);
      return;
    }

    var prevId = 0;
    if (preferId) prevId = parseInt(preferId,10) || 0;
    if (!prevId && addrHead){
      prevId = parseInt(addrHead.getAttribute('data-sel-id')||'0',10) || 0;
    }

    function findById(id){
      id = parseInt(id,10) || 0;
      if (!id) return null;
      for (var j=0;j<items.length;j++){
        if (parseInt(items[j].gpt_id||0,10) === id) return items[j];
      }
      return null;
    }

    var sel = findById(prevId);

    if (!sel){
      sel = items[0];
      for (var i=0;i<items.length;i++){
        if (parseInt(items[i].gpt_is_default||0,10) === 1){ sel = items[i]; break; }
      }
    }

    setAddrHead(sel);

    var html = '';
    for (var i=0;i<items.length;i++){
      var a = items[i];
      var title = cutText(a.gpt_title || '—', 60);
      var addr  = cutText(a.gpt_address || '', 120);

      html += ''
        + '<div class="gpt_cb_dd_item'+((sel && parseInt(a.gpt_id||0,10)===parseInt(sel.gpt_id||0,10))?' active':'')+'" data-id="'+(a.gpt_id||0)+'">'
        + '  <div class="gpt_cb_line1">'+escapeHtml(title)+'</div>'
        + '  <div class="gpt_cb_line2">'+escapeHtml(addr)+'</div>'
        + '</div>';
    }
    addrList.innerHTML = html;
  }


  function bindDropdown(dd){
    var head = dd.querySelector('.gpt_cb_dd_head');
    var list = dd.querySelector('.gpt_cb_dd_list');
    if (!head || !list) return;

    head.onclick = function(e){
      userTouchedBlock();
      e.preventDefault(); e.stopPropagation();
      var isOpen = dd.classList.contains('open');
      closeAllDropdowns();
      if (!isOpen) dd.classList.add('open');
    };

    list.onclick = function(e){
      userTouchedBlock();

      var t = e.target;
      while (t && t !== list && !t.classList.contains('gpt_cb_dd_item')) t = t.parentNode;
      if (!t || t === list) return;
      if (!t.getAttribute('data-id')) return;

      // active highlight
      var all = list.querySelectorAll('.gpt_cb_dd_item');
      for (var i=0;i<all.length;i++) all[i].classList.remove('active');
      t.classList.add('active');

      var ddType = dd.getAttribute('data-dd');
      var id = parseInt(t.getAttribute('data-id')||'0',10) || 0;
      var phone = t.getAttribute('data-phone') || '';

      if (ddType === 'notify'){
        hideWarnContacts();

        var c = null;
        for (var i=0;i<state.contacts.length;i++){
          if ((state.contacts[i].gpt_id||0) === id) { c = state.contacts[i]; break; }
        }
        setContactHead(notifyHead, c);
        if (notifHidden) notifHidden.value = phone;
      } else if (ddType === 'invoice'){
        var cI = null;
        for (var i=0;i<state.contacts.length;i++){
          if ((state.contacts[i].gpt_id||0) === id) { cI = state.contacts[i]; break; }
        }
        // id==0 => "не беспокоить"
        if (id === 0) cI = { gpt_id:0, gpt_name:'— не беспокоить —', gpt_phone:'', gpt_email:'' };
        setContactHead(invoiceHead, cI);
      } else if (ddType === 'call'){
  hideWarnContacts();
  // allow "no notifications"
if (id === 0){
  setContactHead(notifyHead, { gpt_id:0, gpt_name:'— не беспокоить —', gpt_phone:'', gpt_email:'' });
  if (notifHidden) notifHidden.value = '';
  gptCbMarkDirty();
  dd.classList.remove('open');
  return;
}
  var c2 = null;
  for (var i=0;i<state.contacts.length;i++){
    if ((state.contacts[i].gpt_id||0) === id) { c2 = state.contacts[i]; break; }
  }
  setContactHead(callHead, c2);

  // >>> ДОБАВИТЬ:
  gptCbCallManual = true;
  gptCbCallManualId = id;
} else if (ddType === 'req'){
        var r = null;
        for (var i=0;i<state.requisites.length;i++){
          if ((state.requisites[i].gpt_id||0) === id) { r = state.requisites[i]; break; }
        }
        var cname = '—';
        for (var i=0;i<state.contacts.length;i++){
          if ((state.contacts[i].gpt_requisite_id||0) === id){
            cname = state.contacts[i].gpt_name || '—';
            break;
          }
        }
        setReqHead(r, cname);
        gptCbCallManual = false;
        gptCbCallManualId = 0;
        autoPickCallContactByRequisite(id);
      } else if (ddType === 'addr'){
        var a = null;
        for (var i=0;i<(state.delivery||[]).length;i++){
          if ((state.delivery[i].gpt_id||0) === id){ a = state.delivery[i]; break; }
        }
        setAddrHead(a);
      }
      gptCbMarkDirty();
      dd.classList.remove('open');
    };
  }

  function setLoading(){
    if (reqList) reqList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Загрузка...</div></div>';
    if (invoiceList) invoiceList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Загрузка...</div></div>';
    if (notifyList) notifyList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Загрузка...</div></div>';
    if (callList) callList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Загрузка...</div></div>';
    if (addrList) addrList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Загрузка...</div></div>';
  }

  function loadAllForContragent(cid, opts){
    opts = opts || {};
    setLoading();

    var reqUrl  = API + '?gpt_action=list_requisites&gpt_contragent_id=' + encodeURIComponent(cid);
    var conUrl  = API + '?gpt_action=list_contacts&gpt_contragent_id='   + encodeURIComponent(cid);
    var adrUrl  = API + '?gpt_action=list_delivery&gpt_contragent_id='   + encodeURIComponent(cid);
    var infoUrl = API + '?gpt_action=get_contragent&gpt_contragent_id='  + encodeURIComponent(cid);

    Promise.all([
      fetchJson(reqUrl),
      fetchJson(conUrl),
      fetchJson(adrUrl),
      fetchJson(infoUrl)
    ]).then(function(arr){
      var jr = arr[0], jc = arr[1], ja = arr[2], ji = arr[3];

      var reqs  = (jr && jr.ok && jr.data && jr.data.items) ? jr.data.items : [];
      var cons  = (jc && jc.ok && jc.data && jc.data.items) ? jc.data.items : [];
      var addrs = (ja && ja.ok && ja.data && ja.data.items) ? ja.data.items : [];

      state.requisites = reqs;
      state.contacts   = cons;
      state.delivery   = addrs;

      // prefer выбранные значения (для refresh, чтобы не сбрасывать на дефолты)
      var pref = opts.pref || {};
      var preferReq   = opts.preserve ? (parseInt(pref.reqId||0,10)||0)   : 0;
      var preferAddr  = opts.preserve ? (parseInt(pref.addrId||0,10)||0)  : 0;

      buildReqList(reqs, cons, preferReq);
      buildContactsLists(cons, opts.preserve ? {
        notifyId: pref.notifyId,
        callId: pref.callId,
        callManual: pref.callManual,
        callManualId: pref.callManualId
      } : null);
      buildAddrList(addrs, preferAddr);

      // примечание (gpt_note)
      var note = '';
      if (ji && ji.ok && ji.data){
        if (ji.data.gpt_note !== undefined && ji.data.gpt_note !== null) note = ji.data.gpt_note;
        else if (ji.data.note !== undefined && ji.data.note !== null) note = ji.data.note;
      }
      setContragentNote(note);
      // === preserve selected values (including notifyId=0 "не беспокоить") ===
if (gptCbPreserve){
  var p = gptCbPreserve;
  gptCbPreserve = null;

  // 1) req
  if (p.reqId > 0){
    var r = getSelectedById(state.requisites, p.reqId);
    if (r){
      var cname = '—';
      for (var i=0;i<(state.contacts||[]).length;i++){
        if (parseInt(state.contacts[i].gpt_requisite_id||0,10) === p.reqId){
          cname = state.contacts[i].gpt_name || '—';
          break;
        }
      }
      setReqHead(r, cname);
      setActiveInList(reqList, p.reqId);
    }
  }

  // 2) addr
  if (p.addrId > 0){
    var a = getSelectedById(state.delivery, p.addrId);
    if (a){
      setAddrHead(a);
      setActiveInList(addrList, p.addrId);
    }
  }

  // 3) invoice (0 allowed)
  if (p.invoiceId === 0){
    setContactHead(invoiceHead, { gpt_id:0, gpt_name:'— не беспокоить —', gpt_phone:'', gpt_email:'' });
    setActiveInList(invoiceList, 0);
  } else if (p.invoiceId > 0){
    var cI = getSelectedById(state.contacts, p.invoiceId);
    if (cI){
      setContactHead(invoiceHead, cI);
      setActiveInList(invoiceList, p.invoiceId);
    }
  }

  // 4) notify (0 allowed)
  if (p.notifyId === 0){
    setContactHead(notifyHead, { gpt_id:0, gpt_name:'— не беспокоить —', gpt_phone:'', gpt_email:'' });
    setActiveInList(notifyList, 0);
    if (notifHidden) notifHidden.value = '';
  } else if (p.notifyId > 0){
    var cN = getSelectedById(state.contacts, p.notifyId);
    if (cN){
      setContactHead(notifyHead, cN);
      setActiveInList(notifyList, p.notifyId);
      if (notifHidden) notifHidden.value = cN.gpt_phone ? cN.gpt_phone : '';
    }
  }

  // 5) call
  if (p.callId > 0){
    var cC = getSelectedById(state.contacts, p.callId);
    if (cC){
      setContactHead(callHead, cC);
      setActiveInList(callList, p.callId);
      gptCbCallManual = true;
      gptCbCallManualId = p.callId;
    }
  }

  // preserve-mode: НЕ применяем init
  return;
}

      // apply init selections (только при первичной инициализации / смене контрагента)
      if (!opts.preserve) applyInitSelections();

    }).catch(function(err){
      if (reqList)    reqList.innerHTML    = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Ошибка загрузки</div></div>';
      if (invoiceList) invoiceList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Ошибка загрузки</div></div>';
      if (notifyList) notifyList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Ошибка загрузки</div></div>';
      if (callList)   callList.innerHTML   = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Ошибка загрузки</div></div>';
      if (addrList)   addrList.innerHTML   = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">Ошибка загрузки</div></div>';
      setContragentNote('');
      // console.log(err);
    });
  }


  // dropdowns
  var dds = root.querySelectorAll('.gpt_cb_dd');
  for (var i=0;i<dds.length;i++) bindDropdown(dds[i]);

  // contragent search
  var searchTimer = null;
  inputName.addEventListener('input', function(){
    var term = (inputName.value || '').trim();
    if (term.length < 2){
      closeSuggest();
      return;
    }
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(function(){
      var url = API + '?gpt_action=search_contragents&gpt_term=' + encodeURIComponent(term);
      fetchJson(url).then(function(j){
        if (!j || !j.ok) { closeSuggest(); return; }
        openSuggest((j.data && j.data.items) ? j.data.items : []);
      }).catch(function(){
        closeSuggest();
      });
    }, 180);
  });

  // click on suggest item
  suggest.addEventListener('click', function(e){
    var t = e.target;
    while (t && t !== suggest && !t.classList.contains('gpt_cb_suggestItem')) t = t.parentNode;
    if (!t || t === suggest) return;
    var id = parseInt(t.getAttribute('data-id')||'0',10) || 0;
    var name = t.getAttribute('data-name') || '';
    if (id > 0) setContragent(id, name);
    userTouchedBlock();

  });

  // close on outside click / esc
  document.addEventListener('click', function(e){
    // если клик НЕ внутри root — ничего не делаем
    if (!root.contains(e.target)) return;

    // если клик не по input — закрываем подсказки
    if (e.target !== inputName) closeSuggest();

    // dropdowns закрываем всегда по клику внутри root (кроме head/list, там stopPropagation)
    closeAllDropdowns();
  });

  document.addEventListener('keydown', function(e){
    if (e && e.keyCode === 27){
      closeAllDropdowns();
      closeSuggest();
    }
  });

  // open constructor modal via твоей функции
  if (openCtorBtn){
    openCtorBtn.addEventListener('click', function(){
      var cid = parseInt(inputId.value||'0',10) || 0;
      if (typeof window.crmNcOpen === 'function'){
        window.crmNcOpen(cid);
      } else {
        console.warn('crmNcOpen() не найдена на странице');
      }
    });
  }

  // если из конструктора придёт выбор (когда добавишь postMessage) — поддержим
  window.addEventListener('message', function(ev){
    try{
      var d = ev.data || {};
      if (d.type === 'gpt_newcontragents_selected' && d.id){
        setContragent(parseInt(d.id,10)||0, d.name || '');
      }
    } catch(e){}
  });

  // initial load
  if (state.contragentId > 0){
    loadAllForContragent(state.contragentId);
  } else {
    if (reqList) reqList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">— выбери контрагента —</div></div>';
    if (addrList) addrList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">—</div></div>';
    if (notifyList) notifyList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">—</div></div>';
    if (callList) callList.innerHTML = '<div class="gpt_cb_dd_item" style="cursor:default;color:rgba(0,0,0,0.55)"><div class="gpt_cb_line1">—</div></div>';
  }

  // локальная функция — чтобы не выносить наружу
function setContragent(id, name){
  userTouchedBlock();
  state.contragentId = id;
  state.contragentName = name;

  inputId.value = id;
  inputName.value = name;
  if (idHint) idHint.textContent = 'ID: #' + id;

   // !!! ДОБАВЬ ВОТ ЭТО
if (window.crmFillLegacyContragent) {
  window.crmFillLegacyContragent({ id: id, name: name });
}


  // сброс ручного выбора контакта для связи
  gptCbCallManual = false;
  gptCbCallManualId = 0;

  // новый контрагент => выбор контактов считаем не подтвержденным
showWarnContacts();

// init-id’шники больше не применяем
initReqId = initAddrId = initNotifyId = initCallId = 0;
initApplied = true; // чтобы applyInitSelections не лез


  closeSuggest();
  loadAllForContragent(id);
}

function syncLegacyFormFields(cid, cname){
  // 1) базовые поля
  var oldId = document.querySelector('input[name="contragent_id"]');
  if (oldId) oldId.value = String(cid);

  var oldName = document.querySelector('input[name="contragent_name"]');
  if (oldName) oldName.value = String(cname || '');

  // 2) старые поля (адрес/реквизиты/контакты/whatsapp)
  // Берем их из API get_old_contragent_fields (он уже существует в конструкторе)
  var url = API + '?gpt_action=get_old_contragent_fields&gpt_contragent_id=' + encodeURIComponent(cid);

  fetchJson(url).then(function(j){
    if (!j || !j.ok || !j.data) return;
    var d = j.data || {};

    var ta;

    ta = document.querySelector('[name="contragent_contacts"]');
    if (ta) ta.value = d.contacts || '';

    ta = document.querySelector('[name="contragent_fullinfo"]');
    if (ta) ta.value = d.fullinfo || '';

    ta = document.querySelector('[name="contragent_address"]');
    if (ta) ta.value = d.address || '';

    ta = document.querySelector('[name="notification_number"]');
    if (ta) {
      ta.value = d.notification_number || '';
      // если у тебя есть formattingNumbers() — нормализуем
      try { if (typeof formattingNumbers === 'function') formattingNumbers(ta); } catch(e){}
    }
  }).catch(function(){});


}

function fillLegacyFields(cid, cname){
  // 1) id / name
  var oldId = document.querySelector('input[name="contragent_id"]');
  if (oldId) oldId.value = String(cid);

  var oldName = document.querySelector('input[name="contragent_name"]');
  if (oldName) oldName.value = String(cname || '');

  // 2) старые реквизиты/контакты/адрес/wa — тянем из API
  var url = API + '?gpt_action=get_old_contragent_fields&gpt_contragent_id=' + encodeURIComponent(cid);

  fetchJson(url).then(function(j){
    if (!j || !j.ok || !j.data) return;
    var d = j.data || {};

    var el;

    el = document.querySelector('[name="contragent_contacts"]');
    if (el) el.value = d.contacts || '';

    el = document.querySelector('[name="contragent_fullinfo"]');
    if (el) el.value = d.fullinfo || '';

    el = document.querySelector('[name="contragent_address"]');
    if (el) el.value = d.address || '';

    el = document.querySelector('[name="notification_number"]');
    if (el) {
      el.value = d.notification_number || '';
      try { if (typeof formattingNumbers === 'function') formattingNumbers(el); } catch(e){}
    }
  }).catch(function(){});
}



//window.gptCbReloadAll = function(){
//  var cid = state && state.contragentId ? (parseInt(state.contragentId,10)||0) : 0;
//  if (cid > 0) loadAllForContragent(cid);
//};
//
//// экспорт наружу, чтобы CRM могла принудительно обновить данные после закрытия конструктора
//window.gptCbReloadAll = function(){
//  try {
//    var el = document.getElementById('gpt_cb_contragent_id');
//    var cid = el ? (parseInt(String(el.value||'').trim(),10) || 0) : 0;
//    if (cid > 0) loadAllForContragent(cid);
//  } catch(e){}
//};
// экспорт наружу: CRM может попросить "мягко" обновить списки без сброса выбора
window.gptCbReloadAll = function(preserve){
  try{
    var cid = parseInt(inputId.value || '0', 10) || 0;
    if (!cid) return;

    if (preserve){
      gptCbPreserve = {
        reqId:    reqHead    ? (parseInt(reqHead.getAttribute('data-sel-id')||'0',10) || 0) : 0,
        addrId:   addrHead   ? (parseInt(addrHead.getAttribute('data-sel-id')||'0',10) || 0) : 0,
        invoiceId: invoiceHead? (parseInt(invoiceHead.getAttribute('data-sel-id')||'0',10) || 0) : 0, // ВАЖНО: 0 допустим
        notifyId: notifyHead ? (parseInt(notifyHead.getAttribute('data-sel-id')||'0',10) || 0) : 0, // ВАЖНО: 0 допустим
        callId:   callHead   ? (parseInt(callHead.getAttribute('data-sel-id')||'0',10) || 0) : 0
      };
    } else {
      gptCbPreserve = null;
    }

    loadAllForContragent(cid);

  } catch(e){}
};




})();
</script>
