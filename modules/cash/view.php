<?php
// Ожидает: $demo (array)
$demo = $demo ?? [];

// на всякий случай — приводим к простому массиву
$demoJson = json_encode(array_values($demo), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

<div class="grid" style="gap:14px;">
  <div class="card">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <div>
        <div style="font-weight:800;">Касса</div>
        <div class="muted" style="margin-top:2px;">Макет: фильтры, таблица, итоги, добавление записи в демо-режиме.</div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <button class="btn" type="button" id="gptCashAddBtn">+ Поступление</button>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="grid" style="grid-template-columns: 1fr 1fr 1.2fr;gap:12px;align-items:end;">
      <label style="display:block;">
        <div class="muted" style="font-size:12px;margin-bottom:6px;">Дата с</div>
        <input id="gptCashFrom" type="date" class="gptInput" />
      </label>
      <label style="display:block;">
        <div class="muted" style="font-size:12px;margin-bottom:6px;">Дата по</div>
        <input id="gptCashTo" type="date" class="gptInput" />
      </label>
      <label style="display:block;">
        <div class="muted" style="font-size:12px;margin-bottom:6px;">Поиск</div>
        <input id="gptCashQ" type="text" class="gptInput" placeholder="Клиент / № заказа / № счета / комментарий" />
      </label>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;align-items:center;">
      <label class="gptPill"><input type="checkbox" id="gptCashOnlyBank" /> <span>Только по счетам (ООО/ИП/ИПС)</span></label>
      <label class="gptPill"><input type="checkbox" id="gptCashOnlyNoInvoice" /> <span>Только без счета</span></label>
      <label class="gptPill"><input type="checkbox" id="gptCashOnlyUnknown" /> <span>Только «не указан канал»</span></label>
      <div style="margin-left:auto;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <span class="muted" style="font-size:12px;">Канал:</span>
        <select id="gptCashMethod" class="gptInput" style="padding-right:34px;">
          <option value="">Все</option>
          <option value="bank_ooo">ООО (счет)</option>
          <option value="bank_ip">ИП (счет)</option>
          <option value="bank_ips">ИПС (счет)</option>
          <option value="terminal">Терминал</option>
          <option value="cash">Нал</option>
          <option value="cash_receipt">Нал чек</option>
          <option value="sbp">СБП</option>
          <option value="qr">QR</option>
          <option value="unknown">Не указан</option>
        </select>
      </div>
    </div>
  </div>

  <div class="card" style="padding:0;overflow:hidden;">
    <div style="overflow:auto;">
      <table class="gptTable" id="gptCashTable">
        <thead>
          <tr>
            <th style="min-width:140px;">Дата</th>
            <th style="min-width:220px;">Клиент</th>
            <th style="min-width:90px;">Заказ</th>
            <th style="min-width:90px;">ООО</th>
            <th style="min-width:90px;">ИП</th>
            <th style="min-width:90px;">ИПС</th>
            <th style="min-width:110px;">Терминал</th>
            <th style="min-width:90px;">Нал</th>
            <th style="min-width:110px;">Нал чек</th>
            <th style="min-width:90px;">СБП</th>
            <th style="min-width:90px;">QR</th>
            <th style="min-width:220px;">Пометки</th>
          </tr>
        </thead>
        <tbody id="gptCashTbody">
          <tr><td colspan="12" class="muted" style="padding:14px;">Загрузка…</td></tr>
        </tbody>
        <tfoot>
          <tr id="gptCashTotals">
            <td colspan="3" style="font-weight:800;">Итого</td>
            <td class="num" data-t="bank_ooo">0</td>
            <td class="num" data-t="bank_ip">0</td>
            <td class="num" data-t="bank_ips">0</td>
            <td class="num" data-t="terminal">0</td>
            <td class="num" data-t="cash">0</td>
            <td class="num" data-t="cash_receipt">0</td>
            <td class="num" data-t="sbp">0</td>
            <td class="num" data-t="qr">0</td>
            <td class="muted" id="gptCashTotalAll">—</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<!-- ===== Модалка добавления (демо) ===== -->
<div class="gptModal" id="gptCashModal" style="display:none;">
  <div class="gptModalBackdrop" data-act="close"></div>
  <div class="gptModalCard">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
      <div style="font-weight:800;">Добавить поступление (демо)</div>
      <button class="rpClose" type="button" data-act="close">✕</button>
    </div>
    <div class="grid" style="grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
      <label style="display:block;">
        <div class="muted" style="font-size:12px;margin-bottom:6px;">Дата и время</div>
        <input id="gptCashM_dt" type="datetime-local" class="gptInput" />
      </label>
      <label style="display:block;">
        <div class="muted" style="font-size:12px;margin-bottom:6px;">Сумма</div>
        <input id="gptCashM_amount" type="number" step="0.01" class="gptInput" placeholder="0.00" />
      </label>
      <label style="display:block;">
        <div class="muted" style="font-size:12px;margin-bottom:6px;">Канал</div>
        <select id="gptCashM_method" class="gptInput">
          <option value="bank_ooo">ООО (счет)</option>
          <option value="bank_ip">ИП (счет)</option>
          <option value="bank_ips">ИПС (счет)</option>
          <option value="terminal">Терминал</option>
          <option value="cash">Нал</option>
          <option value="cash_receipt">Нал чек</option>
          <option value="sbp">СБП</option>
          <option value="qr">QR</option>
          <option value="unknown">Не указан</option>
        </select>
      </label>
      <label style="display:block;">
        <div class="muted" style="font-size:12px;margin-bottom:6px;">Клиент</div>
        <input id="gptCashM_client" type="text" class="gptInput" placeholder="Например: ООО «Ромашка»" />
      </label>
      <label style="display:block;">
        <div class="muted" style="font-size:12px;margin-bottom:6px;">№ заказа (опц.)</div>
        <input id="gptCashM_order" type="text" class="gptInput" placeholder="" />
      </label>
      <label style="display:block;">
        <div class="muted" style="font-size:12px;margin-bottom:6px;">№ счета (опц.)</div>
        <input id="gptCashM_invoice" type="text" class="gptInput" placeholder="Счёт №…" />
      </label>
    </div>
    <label style="display:block;margin-top:12px;">
      <div class="muted" style="font-size:12px;margin-bottom:6px;">Пометки</div>
      <input id="gptCashM_note" type="text" class="gptInput" placeholder="Комментарий" />
    </label>

    <div id="gptCashM_err" style="display:none;margin-top:10px;padding:10px 12px;border-radius:12px;border:1px solid color-mix(in srgb, #ef4444 35%, var(--line));background:color-mix(in srgb, #ef4444 10%, transparent);color:var(--text);">
      <span style="color:#ef4444;font-weight:800;">Ошибка:</span>
      <span id="gptCashM_errText"></span>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px;">
      <button class="rpClose" type="button" data-act="close">Отмена</button>
      <button class="btn" type="button" id="gptCashM_save">Сохранить</button>
    </div>
  </div>
</div>

<style>
  .gptInput{
    width:100%;
    padding:10px 12px;
    border-radius:12px;
    border:1px solid var(--line);
    background:color-mix(in srgb, var(--card) 86%, transparent);
    color:var(--text);
    outline:none;
  }
  .gptInput:focus{border-color:color-mix(in srgb, var(--accent) 45%, var(--line)); box-shadow:0 0 0 3px color-mix(in srgb, var(--accent) 12%, transparent);}
  .gptPill{display:flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid var(--line);border-radius:999px;background:rgba(127,127,127,.06);cursor:pointer;user-select:none;}
  .gptPill input{accent-color: var(--accent);}
  .gptTable{width:100%;border-collapse:separate;border-spacing:0;}
  .gptTable th,.gptTable td{padding:10px 12px;border-bottom:1px solid var(--line);vertical-align:middle;}
  .gptTable thead th{position:sticky;top:0;background:var(--panel);z-index:1;color:var(--muted);font-weight:700;font-size:12px;text-transform:uppercase;letter-spacing:.4px;}
  .gptTable tbody tr{cursor:pointer;}
  .gptTable tbody tr:hover{background:rgba(127,127,127,.06);}
  .gptTable tfoot td{background:color-mix(in srgb, var(--panel) 70%, transparent);border-bottom:none;}
  .gptTable td.num{text-align:right;font-variant-numeric: tabular-nums;}
  .gptTag{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid var(--line);color:var(--muted);font-size:12px;}

  .gptModal{position:fixed;inset:0;z-index:200;}
  .gptModalBackdrop{position:absolute;inset:0;background:rgba(0,0,0,.55);}
  .gptModalCard{
    position:relative;
    width:min(720px, 92vw);
    margin:7vh auto 0;
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:var(--radius);
    padding:14px;
    box-shadow:0 20px 60px rgba(0,0,0,.35);
  }
</style>

<script>
(function(){
  var SEED = <?php echo $demoJson ?: '[]'; ?>;
  var STORAGE_KEY = 'admix_cash_demo_v1';

  function safeParse(json){
    try { return JSON.parse(json||''); } catch(e) { return null; }
  }

  function loadData(){
    var local = safeParse(localStorage.getItem(STORAGE_KEY));
    if (local && Array.isArray(local)) return local;
    return SEED;
  }

  function saveData(arr){
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(arr)); } catch(e) {}
  }

  function pad2(n){ return (n<10?'0':'') + n; }
  function fmtMoney(x){
    var n = Number(x||0);
    return n.toLocaleString('ru-RU', {minimumFractionDigits:2, maximumFractionDigits:2});
  }
  function dtToDateStr(dt){
    // dt: 'YYYY-MM-DD HH:MM'
    return (dt||'').slice(0,10);
  }
  function normalizeStr(s){
    return String(s||'').toLowerCase();
  }
  function isBankMethod(m){
    return m==='bank_ooo' || m==='bank_ip' || m==='bank_ips';
  }

  var data = loadData();

  var elFrom = document.getElementById('gptCashFrom');
  var elTo = document.getElementById('gptCashTo');
  var elQ = document.getElementById('gptCashQ');
  var elMethod = document.getElementById('gptCashMethod');
  var elOnlyBank = document.getElementById('gptCashOnlyBank');
  var elOnlyNoInvoice = document.getElementById('gptCashOnlyNoInvoice');
  var elOnlyUnknown = document.getElementById('gptCashOnlyUnknown');
  var tbody = document.getElementById('gptCashTbody');
  var totalsRow = document.getElementById('gptCashTotals');
  var totalAll = document.getElementById('gptCashTotalAll');

  function setDefaultDates(){
    if (!data.length) return;
    var dates = data.map(function(r){ return dtToDateStr(r.dt); }).filter(Boolean).sort();
    if (!dates.length) return;
    if (!elFrom.value) elFrom.value = dates[0];
    if (!elTo.value) elTo.value = dates[dates.length-1];
  }

  function applyFilters(arr){
    var from = elFrom.value || '';
    var to = elTo.value || '';
    var q = normalizeStr(elQ.value || '');
    var method = elMethod.value || '';
    var onlyBank = !!elOnlyBank.checked;
    var onlyNoInvoice = !!elOnlyNoInvoice.checked;
    var onlyUnknown = !!elOnlyUnknown.checked;

    return arr.filter(function(r){
      var d = dtToDateStr(r.dt);
      if (from && d && d < from) return false;
      if (to && d && d > to) return false;

      var m = (r.method || 'unknown');
      if (method && m !== method) return false;
      if (onlyBank && !isBankMethod(m)) return false;
      if (onlyNoInvoice && String(r.invoice||'').trim()) return false;
      if (onlyUnknown && m !== 'unknown') return false;

      if (q){
        var hay = normalizeStr([r.client, r.order, r.invoice, r.note].join(' '));
        if (hay.indexOf(q) === -1) return false;
      }
      return true;
    });
  }

  function cellFor(method, amount){
    if (!amount) return '';
    return '<div style="text-align:right;font-variant-numeric:tabular-nums;">'+fmtMoney(amount)+'</div>';
  }

  function render(){
    var filtered = applyFilters(data);
    if (!filtered.length){
      tbody.innerHTML = '<tr><td colspan="12" class="muted" style="padding:14px;">Нет данных по выбранным фильтрам.</td></tr>';
    } else {
      tbody.innerHTML = filtered.map(function(r){
        var m = (r.method || 'unknown');
        var cols = {
          bank_ooo:'', bank_ip:'', bank_ips:'', terminal:'', cash:'', cash_receipt:'', sbp:'', qr:''
        };
        if (cols.hasOwnProperty(m)) cols[m] = cellFor(m, r.amount);

        var note = String(r.note||'');
        var inv = String(r.invoice||'').trim();
        var noteHtml = '';
        if (inv) noteHtml += '<span class="gptTag">'+escapeHtml(inv)+'</span> ';
        if (note) noteHtml += '<span class="muted">'+escapeHtml(note)+'</span>';
        if (!noteHtml) noteHtml = '<span class="muted">—</span>';

        return (
          '<tr data-id="'+escapeHtml(r.id)+'">'
          +'<td>'+escapeHtml(r.dt)+'</td>'
          +'<td>'+escapeHtml(r.client||'')+'</td>'
          +'<td class="muted">'+(r.order?('#'+escapeHtml(r.order)):'—')+'</td>'
          +'<td class="num">'+(cols.bank_ooo||'')+'</td>'
          +'<td class="num">'+(cols.bank_ip||'')+'</td>'
          +'<td class="num">'+(cols.bank_ips||'')+'</td>'
          +'<td class="num">'+(cols.terminal||'')+'</td>'
          +'<td class="num">'+(cols.cash||'')+'</td>'
          +'<td class="num">'+(cols.cash_receipt||'')+'</td>'
          +'<td class="num">'+(cols.sbp||'')+'</td>'
          +'<td class="num">'+(cols.qr||'')+'</td>'
          +'<td>'+noteHtml+'</td>'
          +'</tr>'
        );
      }).join('');
    }

    // totals
    var sums = {
      bank_ooo:0, bank_ip:0, bank_ips:0, terminal:0, cash:0, cash_receipt:0, sbp:0, qr:0
    };
    var all = 0;
    filtered.forEach(function(r){
      var m = (r.method || 'unknown');
      var a = Number(r.amount || 0);
      all += a;
      if (sums.hasOwnProperty(m)) sums[m] += a;
    });
    Object.keys(sums).forEach(function(k){
      var td = totalsRow.querySelector('[data-t="'+k+'"]');
      if (td) td.textContent = fmtMoney(sums[k]);
    });
    totalAll.textContent = 'Всего: ' + fmtMoney(all);
  }

  function escapeHtml(s){
    return String(s||'')
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }

  function bind(){
    [elFrom, elTo, elQ, elMethod, elOnlyBank, elOnlyNoInvoice, elOnlyUnknown].forEach(function(el){
      if (!el) return;
      el.addEventListener('input', render);
      el.addEventListener('change', render);
    });

    tbody.addEventListener('click', function(e){
      var tr = e.target.closest('tr');
      if (!tr || !tr.getAttribute('data-id')) return;
      var id = tr.getAttribute('data-id');
      var r = data.find(function(x){ return String(x.id) === String(id); });
      if (!r) return;
      showInRightPanel(r);
    });
  }

  function showInRightPanel(r){
    var body = document.getElementById('gptRightPanelBody');
    if (!body) return;

    var methodLabel = {
      bank_ooo:'ООО (счет)',
      bank_ip:'ИП (счет)',
      bank_ips:'ИПС (счет)',
      terminal:'Терминал',
      cash:'Нал',
      cash_receipt:'Нал чек',
      sbp:'СБП',
      qr:'QR',
      unknown:'Не указан'
    }[r.method||'unknown'] || (r.method||'');

    body.innerHTML = (
      '<div style="font-weight:800;margin-bottom:10px;">Поступление</div>'
      +'<div class="grid" style="gap:8px;">'
      +  item('Дата', r.dt)
      +  item('Сумма', fmtMoney(r.amount))
      +  item('Канал', methodLabel)
      +  item('Клиент', r.client||'')
      +  item('Заказ', r.order ? ('#'+r.order) : '—')
      +  item('Счёт', (r.invoice||'').trim() || '—')
      +  item('Пометки', (r.note||'').trim() || '—')
      +'</div>'
      +'<div style="margin-top:12px;">'
      +  '<button class="rpClose" type="button" id="gptCashEditStub">Редактировать (позже)</button>'
      +'</div>'
    );

    if (window.GPT_RightPanel && window.GPT_RightPanel.open) window.GPT_RightPanel.open();
  }

  function item(k,v){
    return '<div style="display:flex;justify-content:space-between;gap:10px;">'
      +'<div class="muted">'+escapeHtml(k)+'</div>'
      +'<div style="font-variant-numeric:tabular-nums;">'+escapeHtml(v)+'</div>'
    +'</div>';
  }

  // modal add
  var modal = document.getElementById('gptCashModal');
  var addBtn = document.getElementById('gptCashAddBtn');
  var m_dt = document.getElementById('gptCashM_dt');
  var m_amount = document.getElementById('gptCashM_amount');
  var m_method = document.getElementById('gptCashM_method');
  var m_client = document.getElementById('gptCashM_client');
  var m_order = document.getElementById('gptCashM_order');
  var m_invoice = document.getElementById('gptCashM_invoice');
  var m_note = document.getElementById('gptCashM_note');
  var m_save = document.getElementById('gptCashM_save');
  var m_errWrap = document.getElementById('gptCashM_err');
  var m_errText = document.getElementById('gptCashM_errText');

  function openModal(){
    if (!modal) return;
    modal.style.display = 'block';
    if (m_errWrap) m_errWrap.style.display = 'none';
    // now
    var now = new Date();
    var v = now.getFullYear()+'-'+pad2(now.getMonth()+1)+'-'+pad2(now.getDate())+'T'+pad2(now.getHours())+':'+pad2(now.getMinutes());
    if (m_dt) m_dt.value = v;
    if (m_amount) m_amount.value = '';
    if (m_client) m_client.value = '';
    if (m_order) m_order.value = '';
    if (m_invoice) m_invoice.value = '';
    if (m_note) m_note.value = '';
    if (m_method) m_method.value = 'cash';
    if (m_errWrap) m_errWrap.style.display = 'none';
  }
  function closeModal(){
    if (!modal) return;
    modal.style.display = 'none';
  }

  function bindModal(){
    if (addBtn) addBtn.addEventListener('click', openModal);
    if (!modal) return;
    modal.addEventListener('click', function(e){
      var act = e.target && e.target.getAttribute('data-act');
      if (act === 'close') closeModal();
    });
    var closeBtns = modal.querySelectorAll('[data-act="close"]');
    for (var i=0;i<closeBtns.length;i++){
      closeBtns[i].addEventListener('click', closeModal);
    }
    if (m_save) m_save.addEventListener('click', function(){
      var dtv = (m_dt && m_dt.value) ? m_dt.value : '';
      // convert to 'YYYY-MM-DD HH:MM'
      dtv = dtv ? dtv.replace('T',' ') : '';
      var amt = Number((m_amount && m_amount.value) ? m_amount.value : 0);
      if (!dtv || !isFinite(amt) || amt<=0){
        if (m_errWrap && m_errText){
          m_errText.textContent = 'Заполни дату и сумму (больше 0).';
          m_errWrap.style.display = 'block';
        }
        return;
      }
      var rec = {
        id: 'u'+Date.now(),
        dt: dtv,
        client: (m_client && m_client.value) ? m_client.value : '',
        order: (m_order && m_order.value) ? m_order.value : '',
        invoice: (m_invoice && m_invoice.value) ? m_invoice.value : '',
        method: (m_method && m_method.value) ? m_method.value : 'unknown',
        amount: amt,
        note: (m_note && m_note.value) ? m_note.value : ''
      };
      data = [rec].concat(data);
      saveData(data);
      // обновим диапазон дат, если пусто
      if (elFrom && !elFrom.value) elFrom.value = dtToDateStr(rec.dt);
      if (elTo && !elTo.value) elTo.value = dtToDateStr(rec.dt);
      render();
      closeModal();
      showInRightPanel(rec);
    });
  }

  setDefaultDates();
  bind();
  bindModal();
  render();
})();
</script>
