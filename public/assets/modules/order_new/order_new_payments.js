(function(){
  'use strict';

  // ===== тестовая классификация типов оплат =====
  // group: 'manager' | 'accounting'
  var GPT_PAY_METHODS = [
    { value: '',          label: 'выберите тип оплаты', group: '' },

     { value: 'bank',      label: 'Банк (бух)',          group: 'accounting' },
    { value: 'qr',        label: 'QR',                  group: 'manager' },
    { value: 'card',      label: 'Карта',               group: 'manager' },

    { value: 'invoice',   label: 'Счёт',                group: 'accounting' },
    { value: 'cash',      label: 'Наличные',            group: 'manager' }
  ];

  // ===== тестовая "история оплат" =====
  // date: 'YYYY/MM/DD HH:MM'
  var GPT_PAY_HISTORY = [
    // { date:'2026/02/12 13:29', amount:120.00, method:'qr', note:'(QR)' }
  ];

  // ===== DOM =====
  function $(id){ return document.getElementById(id); }

  function fmtMoney(v){
    var n = Number(v || 0);
    if (!isFinite(n)) n = 0;
    return n.toFixed(2);
  }

  function nowStamp(){
    var d = new Date();
    var yyyy = d.getFullYear();
    var mm = String(d.getMonth()+1).padStart(2,'0');
    var dd = String(d.getDate()).padStart(2,'0');
    var hh = String(d.getHours()).padStart(2,'0');
    var mi = String(d.getMinutes()).padStart(2,'0');
    return yyyy + '/' + mm + '/' + dd + ' ' + hh + ':' + mi;
  }

  function getMethodMeta(val){
    for (var i=0; i<GPT_PAY_METHODS.length; i++){
      if (GPT_PAY_METHODS[i].value === val) return GPT_PAY_METHODS[i];
    }
    return { value: val, label: val, group: '' };
  }

  function buildMethodsSelect(){
    var sel = $('gpt_pay_type');
    if (!sel) return;

    // очистка
    while (sel.firstChild) sel.removeChild(sel.firstChild);

    for (var i=0; i<GPT_PAY_METHODS.length; i++){
      var o = document.createElement('option');
      o.value = GPT_PAY_METHODS[i].value;
      o.textContent = GPT_PAY_METHODS[i].label;
      sel.appendChild(o);
    }
  }

  function applyMode(){
    var sel = $('gpt_pay_type');
    var meta = getMethodMeta(sel ? sel.value : '');

    var boxMgr = $('gpt_pay_mode_manager');
    var boxAcc = $('gpt_pay_mode_accounting');

    if (!boxMgr || !boxAcc) return;

    if (meta.group === 'accounting'){
      boxMgr.style.display = 'none';
      boxAcc.style.display = '';
    } else if (meta.group === 'manager'){
      boxAcc.style.display = 'none';
      boxMgr.style.display = '';
    } else {
      // ничего не выбрано
      boxAcc.style.display = 'none';
      boxMgr.style.display = 'none';
    }

    // подпись в правой истории (для теста)
    var badge = $('gpt_pay_acc_badge');
    if (badge){
      badge.textContent = 'Счёт запрошен';
      // позже: badge.textContent = 'Счёт №123 от ...';
    }
  }

  function calcTotals(){
    // В будущем: "Общая сумма" будет приходить из заказа.
    // Сейчас делаем демо: общая = 0, оплачено = сумма истории.
    var paid = 0;
    for (var i=0; i<GPT_PAY_HISTORY.length; i++){
      paid += Number(GPT_PAY_HISTORY[i].amount || 0);
    }
    var total = Number($('gpt_pay_total') ? $('gpt_pay_total').getAttribute('data-total') : 0) || 0;
    var need = total - paid;
    if (need < 0) need = 0;

    var elPaid = $('gpt_pay_sum_paid');
    var elTotal = $('gpt_pay_sum_total');
    var elNeed = $('gpt_pay_sum_need');

    if (elPaid)  elPaid.textContent  = fmtMoney(paid);
    if (elTotal) elTotal.textContent = fmtMoney(total);
    if (elNeed)  elNeed.textContent  = fmtMoney(need);
  }

  function renderHistory(){
    var list = $('gpt_pay_history');
    if (!list) return;

    // очистка
    while (list.firstChild) list.removeChild(list.firstChild);

    if (!GPT_PAY_HISTORY.length){
      var empty = document.createElement('div');
      empty.className = 'gpt_pay_hist_empty';
      empty.textContent = 'Платежей пока нет';
      list.appendChild(empty);
      return;
    }

    for (var i=0; i<GPT_PAY_HISTORY.length; i++){
      var h = GPT_PAY_HISTORY[i];
      var meta = getMethodMeta(h.method);

var row = document.createElement('div');
row.className = 'gpt_pay_hist_row';

var l = document.createElement('div');
l.className = 'gpt_pay_hist_l';

var d1 = document.createElement('div');
d1.className = 'gpt_pay_hist_date';
d1.textContent = h.date;

var d2 = document.createElement('div');
d2.className = 'gpt_pay_hist_desc';
d2.textContent = meta.label + (h.note ? (' ' + h.note) : '');

l.appendChild(d1);
l.appendChild(d2);

var right = document.createElement('div');
right.className = 'gpt_pay_hist_right';
right.textContent = fmtMoney(h.amount);

row.appendChild(l);
row.appendChild(right);

      list.appendChild(row);
    }
  }

  function addManagerPayment(){
    var sel = $('gpt_pay_type');
    if (!sel) return;

    var meta = getMethodMeta(sel.value);
    if (meta.group !== 'manager') return;

    var note = $('gpt_pay_note') ? String($('gpt_pay_note').value || '').trim() : '';
    var sumStr = $('gpt_pay_amount') ? String($('gpt_pay_amount').value || '').replace(',', '.') : '';
    var sum = Number(sumStr);

    if (!isFinite(sum) || sum <= 0){
      // без алертов — просто подсветим поле
      if ($('gpt_pay_amount')) $('gpt_pay_amount').classList.add('gpt_pay_err');
      return;
    }
    if ($('gpt_pay_amount')) $('gpt_pay_amount').classList.remove('gpt_pay_err');

    var entry = {
      date: nowStamp(),
      amount: sum,
      method: sel.value,
      note: note ? ('— ' + note) : ''
    };

    GPT_PAY_HISTORY.unshift(entry);

    // очистим поля
    if ($('gpt_pay_note')) $('gpt_pay_note').value = '';
    if ($('gpt_pay_amount')) $('gpt_pay_amount').value = '';

    renderHistory();
    calcTotals();
  }

  function wire(){
    var sel = $('gpt_pay_type');
    if (sel){
      sel.addEventListener('change', function(){
        applyMode();
      });
    }

    var btn = $('gpt_pay_add_btn');
    if (btn){
      btn.addEventListener('click', function(){
        addManagerPayment();
      });
    }
  }

  // ===== init =====
  document.addEventListener('DOMContentLoaded', function(){
    buildMethodsSelect();
    applyMode();
    renderHistory();
    calcTotals();
    wire();
  });

})();
