/* ====== pref: gpt_newcontragents ====== */
(function(){
  // API endpoint (host page may override before this file is loaded)
  var gpt_newcontragents_api = (window.GPT_NEWCONTRAGENTS_API || "/api/contragents.php");

  var gpt_newcontragents_selectedContragentId = 0;
  var gpt_newcontragents_selectedContragentName = "";

  var gpt_newcontragents_cacheContacts = {};   // id -> row
  var gpt_newcontragents_cacheRequisites = {}; // id -> row

  function el(id){ return document.getElementById(id); }

  function htmlEscape(s){
    s = (s === null || s === undefined) ? "" : String(s);
    return s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
  }

  function qs(obj){
    var s = [];
    for (var k in obj){
      if (!obj.hasOwnProperty(k)) continue;
      s.push(encodeURIComponent(k) + "=" + encodeURIComponent(obj[k]));
    }
    return s.join("&");
  }


    /* =============================
     Старые данные (fullinfo / address / notification_number)
     ============================= */
  var gptNcOldDataCache = {}; // cid -> {fullinfo, address, notification_number, contacts}

  function gptNcFetchOldContragentData(cb){
    var cid = gpt_newcontragents_selectedContragentId;
    if (!cid){
      if (cb) cb("no contragent", null);
      return;
    }

    if (gptNcOldDataCache[cid]){
      if (cb) cb(null, gptNcOldDataCache[cid]);
      return;
    }

    http(
      "GET",
      gpt_newcontragents_api + "?gpt_action=get_old_contragent_fields&gpt_contragent_id=" + cid,
      null,
      function(err, json){
        if (err || !json || !json.ok){
          if (cb) cb(err || (json && json.error) || "error", null);
          return;
        }
        var d = json.data || {};
        console.log('OLD DATA:', d);
        gptNcOldDataCache[cid] = d;
        if (cb) cb(null, d);
      }
    );
  }

  function gptNcFillOldPanel(blockId){
    var box = el(blockId);
    if (!box) return;

    var fullNode  = box.querySelector('[data-old-field="fullinfo"]');
    var addrNode  = box.querySelector('[data-old-field="address"]');
    var phoneNode = box.querySelector('[data-old-field="notification_number"]');
    var contNode  = box.querySelector('[data-old-field="contacts"]');
    console.log('oldPanel nodes:', {
  full: !!fullNode, addr: !!addrNode, phone: !!phoneNode, cont: !!contNode,
  blockId: blockId
});


    function set(node, value){
      if (!node) return;
      node.textContent = value;
    }

    var cid = gpt_newcontragents_selectedContragentId;
    if (!cid){
      set(fullNode, "Контрагент не выбран");
      set(addrNode, "—");
      set(phoneNode, "—");
      return;
    }

    // прелоадер
    set(fullNode, "Загрузка...");
    set(addrNode, "");
    set(phoneNode, "");
    set(contNode, "—");

    gptNcFetchOldContragentData(function(err, data){
      if (err || !data){
        set(fullNode, "Нет данных");
        set(addrNode, "");
        set(phoneNode, "");
        set(contNode, "—");
        return;
      }

      var full  = (data.fullinfo || "").replace(/^\s+|\s+$/g, "");
      var addr  = (data.address || "").replace(/^\s+|\s+$/g, "");
      var phone = (data.notification_number || "").replace(/^\s+|\s+$/g, "");
      var cont  = (data.contacts || "").replace(/^\s+|\s+$/g, "");

      set(fullNode,  full  || "—");
      set(addrNode,  addr  || "—");
      set(phoneNode, phone || "—");
      set(contNode, cont || "—");
    });
  }

  function http(method, url, data, cb){
    var xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.onreadystatechange = function(){
      if (xhr.readyState === 4){
        var txt = xhr.responseText || "";
        try {
          var json = JSON.parse(txt);
          cb(null, json);
        } catch(e){
          cb(e, {ok:0, raw:txt});
        }
      }
    };
    if (method === "POST"){
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
    }
    xhr.send(data || null);
  }

  /* =============================
     modal
     ============================= */
  function openModal(){
    el("gpt_newcontragents_overlay").style.display = "flex";
    var inp = el("gpt_newcontragents_searchInput");
    if (inp) inp.focus();
  }
  function closeModal(){
    el("gpt_newcontragents_overlay").style.display = "none";
    hideSearchList();
    hideContactPopup();
    hideRequisitePopup();
  }
  function setBadge(){
    var b = el("gpt_newcontragents_selectedBadge");
    if (!b) return;
    if (gpt_newcontragents_selectedContragentId > 0){
      b.innerHTML = "#" + gpt_newcontragents_selectedContragentId + " — " + htmlEscape(gpt_newcontragents_selectedContragentName);
    } else {
      b.innerHTML = "— не выбран —";
    }
    updateHeaderButtonsState();
  }

  /* =============================
     search contragents
     ============================= */
  var searchTimer = null;

  function hideSearchList(){
    var box = el("gpt_newcontragents_searchResults");
    if (box) box.style.display = "none";
  }

  function renderSearch(items){
    var box = el("gpt_newcontragents_searchResults");
    if (!box) return;

    if (!items || !items.length){
      box.innerHTML = '<div class="gpt_newcontragents_list_item" style="cursor:default;color:rgba(0,0,0,0.55)">Ничего не найдено</div>';
      box.style.display = "block";
      return;
    }

    var html = "";
    for (var i=0;i<items.length;i++){
      var it = items[i];
      html += '<div class="gpt_newcontragents_list_item" data-id="'+it.id+'" data-name="'+htmlEscape(it.name)+'">'
           +  '<b>#'+it.id+'</b> ' + htmlEscape(it.name)
           + '</div>';
    }
    box.innerHTML = html;
    box.style.display = "block";

    var nodes = box.querySelectorAll(".gpt_newcontragents_list_item");
    for (var j=0;j<nodes.length;j++){
      nodes[j].onclick = function(){
        var id = parseInt(this.getAttribute("data-id"),10) || 0;
        var name = this.getAttribute("data-name") || "";
        if (id > 0) selectContragent(id, name);
      };
    }
  }

  function doSearch(term){
    var box = el("gpt_newcontragents_searchResults");
    if (!box) return;

    term = term || "";
    if (term.length < 2){
      box.innerHTML = "";
      box.style.display = "none";
      return;
    }

    http("GET", gpt_newcontragents_api + "?gpt_action=search_contragents&gpt_term=" + encodeURIComponent(term), null, function(err, json){
      if (err || !json || !json.ok){
        renderSearch([]);
        return;
      }
      renderSearch((json.data && json.data.items) ? json.data.items : []);
    });
  }

  function selectContragent(id, name){
    gpt_newcontragents_selectedContragentId = id;
    gpt_newcontragents_selectedContragentName = name || "";

    setBadge();

    var inp = el("gpt_newcontragents_searchInput");
    if (inp) inp.value = name || "";
    hideSearchList();

    el("gpt_newcontragents_rightEmpty").className = "gpt_newcontragents_hidden";
    el("gpt_newcontragents_rightBody").className = "";

    reloadAll();

// сообщаем хосту, кого выбрали
  try {
    if (window.GPT_Contragents && typeof window.GPT_Contragents._onPick === 'function') {
      window.GPT_Contragents._onPick({id:id, name:name || ""});
    }
  } catch(e){}

  try {
    window.dispatchEvent(new CustomEvent('gpt:contragent:pick', {detail:{id:id, name:name || ""}}));
  } catch(e){}

  // legacy: postMessage for iframe embeds
  try {
    if (window.parent && window.parent !== window) {
      window.parent.postMessage({
        type: "gpt_newcontragents_selected",
        fn:   "gpt_newcontragents_selected", // на случай если где-то в проекте слушают .fn
        id: id,
        name: name || ""
      }, "*");
    }
  } catch(e){}

  }

  /* =============================
     load data
     ============================= */
  function loadRequisites(cb){
    var cid = gpt_newcontragents_selectedContragentId;
    if (!cid) return;

    http("GET", gpt_newcontragents_api + "?gpt_action=list_requisites&gpt_contragent_id=" + cid, null, function(err, json){
      var items = [];
      if (!err && json && json.ok && json.data && json.data.items) items = json.data.items;

      gpt_newcontragents_cacheRequisites = {};
      for (var i=0;i<items.length;i++){
        gpt_newcontragents_cacheRequisites[items[i].gpt_id] = items[i];
      }
      if (cb) cb();
    });
  }

  function loadDelivery(cb){
  var cid = gpt_newcontragents_selectedContragentId;
  if (!cid) return;

  http("GET", gpt_newcontragents_api + "?gpt_action=list_delivery&gpt_contragent_id=" + cid, null, function(err, json){
    var items = [];
    if (!err && json && json.ok && json.data && json.data.items) items = json.data.items;

    gpt_newcontragents_cacheDelivery = {};
    for (var i=0;i<items.length;i++){
      gpt_newcontragents_cacheDelivery[items[i].gpt_id] = items[i];
    }
    if (cb) cb();
  });
}


  function loadContacts(cb){
    var cid = gpt_newcontragents_selectedContragentId;
    if (!cid) return;

    http("GET", gpt_newcontragents_api + "?gpt_action=list_contacts&gpt_contragent_id=" + cid, null, function(err, json){
      var items = [];
      if (!err && json && json.ok && json.data && json.data.items) items = json.data.items;

      gpt_newcontragents_cacheContacts = {};
      for (var i=0;i<items.length;i++){
        gpt_newcontragents_cacheContacts[items[i].gpt_id] = items[i];
      }
      if (cb) cb();
    });
  }

    function reloadAll(){
      loadRequisites(function(){
        loadContacts(function(){
          loadDelivery(function(){
            renderRequisitesCards();
            renderContactsCards();
            renderDeliveryCards();
            bindLinkHover();
          });
        });
      });
    }


  function getRequisitesArray(){
    var arr = [];
    for (var rid in gpt_newcontragents_cacheRequisites){
      if (!gpt_newcontragents_cacheRequisites.hasOwnProperty(rid)) continue;
      var r = gpt_newcontragents_cacheRequisites[rid];
      arr.push({
        id: parseInt(r.gpt_id,10) || 0,
        name: r.gpt_legal_name || "",
        inn: r.gpt_inn || ""
      });
    }
    arr.sort(function(a,b){
      var aa = (a.name||"").toLowerCase();
      var bb = (b.name||"").toLowerCase();
      if (aa < bb) return -1;
      if (aa > bb) return 1;
      return (a.id - b.id);
    });
    return arr;
  }

  function getContactForRequisite(reqId){
    var any = null;
    for (var cid in gpt_newcontragents_cacheContacts){
      if (!gpt_newcontragents_cacheContacts.hasOwnProperty(cid)) continue;
      var c = gpt_newcontragents_cacheContacts[cid];
      if (parseInt(c.gpt_requisite_id,10) === parseInt(reqId,10)){
        if (c.gpt_is_default == 1) return c;
        if (!any) any = c;
      }
    }
    return any;
  }

  /* =============================
     custom two-line dropdown
     ============================= */
  function ddCloseAll(){
    var menus = document.querySelectorAll(".gpt_newcontragents_dd_menu");
    for (var i=0;i<menus.length;i++){
      menus[i].style.display = "none";
    }
  }

  function buildReqDropdown(contactId, selectedReqId, reqArr){
    var line1 = "— не выбрано —";
    var line2 = "";

    if (selectedReqId){
      for (var i=0;i<reqArr.length;i++){
        if (parseInt(reqArr[i].id,10) === parseInt(selectedReqId,10)){
          line1 = reqArr[i].name || ("ID " + reqArr[i].id);
          line2 = reqArr[i].inn ? ("ИНН: " + reqArr[i].inn) : "";
          break;
        }
      }
    }

    var itemsHtml = '';
    itemsHtml += ''
      + '<div class="gpt_newcontragents_dd_item ' + (!selectedReqId ? 'gpt_newcontragents_dd_item_active' : '') + '"'
      + ' data-act="dd_pick" data-contact="'+contactId+'" data-req="0">'
      +   '<div class="gpt_newcontragents_dd_l1">— не выбрано —</div>'
      +   '<div class="gpt_newcontragents_dd_l2">&nbsp;</div>'
      + '</div>';

    for (var j=0;j<reqArr.length;j++){
      var r = reqArr[j];
      var active = (parseInt(selectedReqId,10) === parseInt(r.id,10));
      itemsHtml += ''
        + '<div class="gpt_newcontragents_dd_item ' + (active ? 'gpt_newcontragents_dd_item_active' : '') + '"'
        + ' data-act="dd_pick" data-contact="'+contactId+'" data-req="'+r.id+'">'
        +   '<div class="gpt_newcontragents_dd_l1">'+htmlEscape(r.name || ("ID "+r.id))+'</div>'
        +   '<div class="gpt_newcontragents_dd_l2">'+htmlEscape(r.inn ? ("ИНН: " + r.inn) : "")+'</div>'
        + '</div>';
    }

    var html = ''
      + '<div class="gpt_newcontragents_dd" data-dd-contact="'+contactId+'">'
      +   '<div class="gpt_newcontragents_dd_btn" data-act="dd_toggle" data-contact="'+contactId+'">'
      +     '<div class="gpt_newcontragents_dd_l1" data-role="dd_line1">'+htmlEscape(line1)+'</div>'
      +     '<div class="gpt_newcontragents_dd_l2" data-role="dd_line2">'+htmlEscape(line2)+'</div>'
      +   '</div>'
      +   '<div class="gpt_newcontragents_dd_menu">' + itemsHtml + '</div>'
      + '</div>';

    return html;
  }

  function bindReqDropdowns(wrap, reqArr){
    if (!wrap) return;
    if (wrap._gpt_dd_bound) return;
    wrap._gpt_dd_bound = true;

    function closestByAttr(elm, attr, val){
      while (elm && elm !== wrap){
        if (elm.getAttribute && elm.getAttribute(attr) === val) return elm;
        elm = elm.parentNode;
      }
      return null;
    }

    wrap.addEventListener("click", function(e){
      var toggleNode = closestByAttr(e.target, "data-act", "dd_toggle");
      var pickNode   = closestByAttr(e.target, "data-act", "dd_pick");

      // toggle
      if (toggleNode){
        e.stopPropagation();
        var dd = toggleNode.parentNode;
        if (!dd || !dd.querySelector) return;

        var menu = dd.querySelector(".gpt_newcontragents_dd_menu");
        if (!menu) return;

        var isOpen = (menu.style.display === "block");
        ddCloseAll();
        menu.style.display = isOpen ? "none" : "block";
        return;
      }

      // pick
      if (pickNode){
        e.stopPropagation();

        var contactId = parseInt(pickNode.getAttribute("data-contact"),10) || 0;
        var reqId     = parseInt(pickNode.getAttribute("data-req"),10) || 0;
        if (!contactId) return;

        var c = gpt_newcontragents_cacheContacts[contactId];
        if (!c) return;

        // optimistic UI
        var oldReqId = parseInt(c.gpt_requisite_id,10) || 0;
        c.gpt_requisite_id = reqId;

        // update UI
        var dd2 = wrap.querySelector(".gpt_newcontragents_dd[data-dd-contact='"+contactId+"']");
        if (dd2){
          var l1 = dd2.querySelector("[data-role='dd_line1']");
          var l2 = dd2.querySelector("[data-role='dd_line2']");
          var name = "— не выбрано —";
          var inn  = "";

          if (reqId){
            for (var k=0;k<reqArr.length;k++){
              if (parseInt(reqArr[k].id,10) === reqId){
                name = reqArr[k].name || ("ID " + reqArr[k].id);
                inn  = reqArr[k].inn ? ("ИНН: " + reqArr[k].inn) : "";
                break;
              }
            }
          }

          if (l1) l1.innerHTML = htmlEscape(name);
          if (l2) l2.innerHTML = htmlEscape(inn);

          var allItems = dd2.querySelectorAll(".gpt_newcontragents_dd_item");
          for (var t=0;t<allItems.length;t++){
            allItems[t].className = allItems[t].className
              .replace("gpt_newcontragents_dd_item_active","")
              .replace(/\s+/g,' ')
              .replace(/^\s+|\s+$/g,'');
          }
          pickNode.className += " gpt_newcontragents_dd_item_active";

          var menu2 = dd2.querySelector(".gpt_newcontragents_dd_menu");
          if (menu2) menu2.style.display = "none";
        }

        // save
          http("POST", gpt_newcontragents_api + "?gpt_action=update_contact", qs({
            gpt_contragent_id: gpt_newcontragents_selectedContragentId,
            gpt_contact_id: contactId,
            gpt_name: c.gpt_name || "",
            gpt_phone: c.gpt_phone || "",
            gpt_email: c.gpt_email || "",
            gpt_comment: c.gpt_comment || "",
            gpt_requisite_id: reqId,
            gpt_is_default: (c.gpt_is_default == 1) ? 1 : 0,
            gpt_is_notify_default: (c.gpt_is_notify_default == 1) ? 1 : 0
          }), function(err, json){
            if (err || !json || !json.ok){
              c.gpt_requisite_id = oldReqId;
            
              // чтобы UI не “врал” — сразу перезагружаем актуальное состояние
              console.warn("update_contact failed:", err, json);
              reloadAll();
              return;
            }
            reloadAll();
          });


        return;
      }
    });
  }

  document.addEventListener("click", function(){
    ddCloseAll();
  });

function gptNcLine(label, value){
  if (value === null || value === undefined) return "";
  value = String(value).replace(/^\s+|\s+$/g, "");
  if (!value) return "";
  return label + ": " + value;
}

function gptNcBuildFullRequisitesText(r){
  var lines = [];

  // поля формы (то же самое сюда выводим)
  lines.push(gptNcLine("Наименование", r.gpt_legal_name));
  lines.push(gptNcLine("ИНН", r.gpt_inn));
  lines.push(gptNcLine("Юридический адрес", r.gpt_jur_address));
  lines.push(gptNcLine("Р/С", r.gpt_rs));
  lines.push(gptNcLine("Банк", r.gpt_bank_name));
  lines.push(gptNcLine("БИК", r.gpt_bank_bik));
  lines.push(gptNcLine("Директор", r.gpt_director_fio));
  lines.push(gptNcLine("Основание", r.gpt_basis));
  lines.push(gptNcLine("Налогообложение", r.gpt_tax_mode));
  lines.push(gptNcLine("№ договора", r.gpt_contract_no));


  // примечание (у тебя это gpt_details) — в конце
  if (r.gpt_details !== null && r.gpt_details !== undefined) {
    var det = String(r.gpt_details).replace(/^\s+|\s+$/g, "");
    if (det) {
      lines.push(""); // пустая строка-разделитель
      lines.push("Примечание: " + det);
    }
  }

  // фильтр пустых
  var out = [];
  for (var i = 0; i < lines.length; i++){
    if (lines[i]) out.push(lines[i]);
  }

  return out.join("\n");
}



function renderDeliveryCards(){
  var box = el("gpt_newcontragents_deliveryList");
  if (!box) return;

  var html = '';
  var ids = [];
  for (var id in gpt_newcontragents_cacheDelivery){
    if (!gpt_newcontragents_cacheDelivery.hasOwnProperty(id)) continue;
    ids.push(parseInt(id,10));
  }
  ids.sort(function(a,b){
    var aa = gpt_newcontragents_cacheDelivery[a];
    var bb = gpt_newcontragents_cacheDelivery[b];
    var da = (aa && aa.gpt_is_default==1) ? 1 : 0;
    var db = (bb && bb.gpt_is_default==1) ? 1 : 0;

    if (da !== db) return db - da;
    return a - b;
  });

  if (!ids.length){
    box.innerHTML = '<div class="gpt_newcontragents_okhint">Адресов доставки пока нет</div>';
    return;
  }

  for (var i=0;i<ids.length;i++){
    var d = gpt_newcontragents_cacheDelivery[ids[i]];

    var title = (d.gpt_title || '').trim();          // НОВОЕ: короткое название
    var addr  = (d.gpt_address || '').trim();
    var isDef = (parseInt(d.gpt_is_default,10) === 1);

    html += ''
      + '<div class="gpt_newcontragents_card" data-delivery-id="'+d.gpt_id+'">'
      +   '<div class="gpt_newcontragents_cardTop">'
      +   '</div>'

      +   '<div class="gpt_newcontragents_small">'

      // НОВОЕ: название адреса (если пусто — показываем "—")
      +     '<div class="gpt_newcontragents_deliveryTitle">'+htmlEscape(title !== '' ? title : '—')+'</div>'

      // адрес как был
      +     '<div class="gpt_newcontragents_deliveryAddress">'+htmlEscape(addr)+'</div>'

      +   '</div>'

      +   '<div class="gpt_newcontragents_actions">'
      +     '<div class="gpt_newcontragents_mini_btn '+(isDef ? 'gpt_newcontragents_btn_green' : '')+'" data-act="delivery_default">По умолчанию</div>'
      +     '<div class="gpt_newcontragents_mini_btn" data-act="delivery_edit">Редактировать</div>'
      +     '<div class="gpt_newcontragents_mini_btn" data-act="delivery_delete">Удалить</div>'
      +   '</div>'

      +   '<div class="gpt_newcontragents_okhint">ID: '+d.gpt_id+'</div>'
      + '</div>';
  }

  box.innerHTML = html;

  // bind actions
  var cards = box.querySelectorAll(".gpt_newcontragents_card");
  for (var k=0;k<cards.length;k++){
    cards[k].onclick = function(e){
      var act = (e.target && e.target.getAttribute) ? e.target.getAttribute("data-act") : "";
      if (!act) return;

      var did = parseInt(this.getAttribute("data-delivery-id"),10) || 0;
      if (!did) return;

      if (act === "delivery_edit") { openDeliveryPopupEdit(did); return; }
      if (act === "delivery_delete") { deleteDelivery(did); return; }
      if (act === "delivery_default") { toggleDefaultDelivery(did); return; }
    };
  }
}



  /* =============================
     render requisites
     ============================= */

  function renderRequisitesCards(){
    var wrap = el("gpt_newcontragents_requisitesList");
    if (!wrap) return;

    var html = "";
    var have = false;

    for (var rid in gpt_newcontragents_cacheRequisites){
      if (!gpt_newcontragents_cacheRequisites.hasOwnProperty(rid)) continue;
      have = true;

      var r = gpt_newcontragents_cacheRequisites[rid];
      var gpt_newcontragents_cacheDelivery = {}; // id -> row

      var contact = getContactForRequisite(r.gpt_id);
      var contactLine = contact ? (htmlEscape(contact.gpt_name || "") + (contact.gpt_phone ? (" ("+htmlEscape(contact.gpt_phone)+")") : "")) : "-";
      
      var fullText = gptNcBuildFullRequisitesText(r);
      if (!fullText.replace(/\s+/g,'')) fullText = "—";


      html += ''
        + '<div class="gpt_newcontragents_reqRow" data-req-id="'+r.gpt_id+'">'
        +   '<div class="gpt_newcontragents_reqCell">'
        +     '<div class="gpt_newcontragents_reqTitle">'+htmlEscape(r.gpt_legal_name || '')+'</div>'
        +     '<div class="gpt_newcontragents_small"><b>ИНН:</b> '+htmlEscape(r.gpt_inn || '-')+'</div>'
        +     '<div class="gpt_newcontragents_small"><b>Контакт:</b> '+contactLine+'</div>'

        +     '<div class="gpt_newcontragents_reqActions">'
        +       '<div class="gpt_newcontragents_mini_btn '+(r.gpt_is_default==1?'gpt_newcontragents_btn_default_active':'')+'" data-act="def_req" data-id="'+r.gpt_id+'">Сделать по умолчанию</div>'
        +       '<div class="gpt_newcontragents_mini_btn" data-act="edit_req" data-id="'+r.gpt_id+'">Редактировать</div>'
        +       '<div class="gpt_newcontragents_mini_btn" data-act="del_req" data-id="'+r.gpt_id+'">Удалить</div>'
        +     '</div>'

        +     '<div class="gpt_newcontragents_small">ID: '+htmlEscape(r.gpt_id)+'</div>'
        +   '</div>'

        +   '<div class="gpt_newcontragents_reqCell">'
        +     '<div class="gpt_newcontragents_reqTitle">Полные реквизиты</div>'
        +     '<div class="gpt_newcontragents_small gpt_newcontragents_preLine">'+htmlEscape(fullText)+'</div>'

        +   '</div>'
        + '</div>';
    }

    if (!have){
      html = '<div class="gpt_newcontragents_okhint">Реквизитов пока нет</div>';
    }

    wrap.innerHTML = html;

    var btns = wrap.querySelectorAll(".gpt_newcontragents_mini_btn");
    for (var i=0;i<btns.length;i++){
      btns[i].onclick = function(){
        var act = this.getAttribute("data-act");
        var id = parseInt(this.getAttribute("data-id"),10) || 0;
        if (!id) return;

        if (act === "edit_req") openRequisitePopup(id);

        if (act === "def_req"){
          http("POST", gpt_newcontragents_api + "?gpt_action=set_default_requisite", qs({
            gpt_contragent_id: gpt_newcontragents_selectedContragentId,
            gpt_requisite_id: id
          }), function(){ reloadAll(); });
        }

        if (act === "del_req"){
          if (!confirm("Удалить реквизиты?")) return;
          http("POST", gpt_newcontragents_api + "?gpt_action=delete_requisite", qs({
            gpt_contragent_id: gpt_newcontragents_selectedContragentId,
            gpt_requisite_id: id
          }), function(){ reloadAll(); });
        }
      };
    }
  }

  /* =============================
     render contacts
     ============================= */
  function renderContactsCards(){
    var wrap = el("gpt_newcontragents_contactsList");
    if (!wrap) return;

    var reqArr = getRequisitesArray();

    var html = "";
    var have = false;

    for (var cid in gpt_newcontragents_cacheContacts){
      if (!gpt_newcontragents_cacheContacts.hasOwnProperty(cid)) continue;
      have = true;

      var c = gpt_newcontragents_cacheContacts[cid];
      var reqId = parseInt(c.gpt_requisite_id,10) || 0;

      // бейдж "по умолчанию" НЕ показываем, только оповещения
      var badges = "";
      //if (c.gpt_is_notify_default == 1) badges += ' <span class="gpt_newcontragents_badge">оповещения</span>';

      html += ''
        + '<div class="gpt_newcontragents_contactCard" data-contact-id="'+c.gpt_id+'" data-req-id="'+reqId+'">'

        +   '<div>'
        +     '<div class="gpt_newcontragents_contactName">'+htmlEscape(c.gpt_name||'')+badges+'</div>'
        +     '<div class="gpt_newcontragents_small"><b>Тел:</b> '+htmlEscape(c.gpt_phone||'-')+'</div>'
        +     '<div class="gpt_newcontragents_small"><b>Почта:</b> '+htmlEscape(c.gpt_email||'-')+'</div>'
        +     (c.gpt_comment ? ('<div class="gpt_newcontragents_small">'+htmlEscape(c.gpt_comment)+'</div>') : '')
        +     '<div class="gpt_newcontragents_small">ID: '+htmlEscape(c.gpt_id)+'</div>'
        +   '</div>'

        +   '<div class="gpt_newcontragents_contactRight">'
        +     buildReqDropdown(c.gpt_id, reqId, reqArr)
        +   '</div>'

+   '<div class="gpt_newcontragents_contactBottom">'
+     '<div class="gpt_newcontragents_mini_btn '+(c.gpt_is_default==1?'gpt_newcontragents_btn_default_active':'')+'" data-act="def_c" data-id="'+c.gpt_id+'">Для связи</div>'
+     '<div class="gpt_newcontragents_mini_btn '+(c.gpt_is_notify_default==1?'gpt_newcontragents_btn_default_active':'')+'" data-act="not_c" data-id="'+c.gpt_id+'">Для оповещений</div>'
+     '<div class="gpt_newcontragents_mini_btn '+(c.gpt_is_invoice_default==1?'gpt_newcontragents_btn_default_active':'')+'" data-act="inv_c" data-id="'+c.gpt_id+'">Для счетов</div>'
+     '<div class="gpt_newcontragents_mini_btn" data-act="edit_c" data-id="'+c.gpt_id+'">Редактировать</div>'
+     '<div class="gpt_newcontragents_mini_btn" data-act="del_c" data-id="'+c.gpt_id+'">Удалить</div>'
+   '</div>'


        + '</div>';
    }

    if (!have){
      html = '<div class="gpt_newcontragents_okhint">Контактов пока нет</div>';
    }

    wrap.innerHTML = html;

    // bind dropdowns (delegated once)
    bindReqDropdowns(wrap, reqArr);

    // buttons
    var btns = wrap.querySelectorAll(".gpt_newcontragents_mini_btn");
    for (var j=0;j<btns.length;j++){
      btns[j].onclick = function(){
        var act = this.getAttribute("data-act");
        var id = parseInt(this.getAttribute("data-id"),10) || 0;
        if (!id) return;

        if (act === "edit_c") openContactPopup(id);

        if (act === "def_c"){
          http("POST", gpt_newcontragents_api + "?gpt_action=set_default_contact", qs({
            gpt_contragent_id: gpt_newcontragents_selectedContragentId,
            gpt_contact_id: id
          }), function(){ reloadAll(); });
        }

        if (act === "not_c"){
          http("POST", gpt_newcontragents_api + "?gpt_action=set_notify_contact", qs({
            gpt_contragent_id: gpt_newcontragents_selectedContragentId,
            gpt_contact_id: id
          }), function(){ reloadAll(); });
        }
if (act == 'inv_c'){
  http("POST", gpt_newcontragents_api + "?gpt_action=set_invoice_contact", qs({
    gpt_contragent_id: gpt_newcontragents_selectedContragentId,
    gpt_contact_id: id
  }), function(){ reloadAll(); });
}
        
        if (act === "del_c"){
          if (!confirm("Удалить контакт?")) return;
          http("POST", gpt_newcontragents_api + "?gpt_action=delete_contact", qs({
            gpt_contragent_id: gpt_newcontragents_selectedContragentId,
            gpt_contact_id: id
          }), function(){ reloadAll(); });
        }
      };
    }
  }

  /* =============================
     hover-link highlight (req <-> contacts)
     ============================= */
  function bindLinkHover(){
    var reqWrap = el("gpt_newcontragents_requisitesList");
    var conWrap = el("gpt_newcontragents_contactsList");
    if (!reqWrap || !conWrap) return;

    if (reqWrap._gpt_link_bound) return;
    reqWrap._gpt_link_bound = true;

    function clearAll(){
      var a = document.querySelectorAll(".gpt_newcontragents_link_hover");
      for (var i=0;i<a.length;i++){
        a[i].className = a[i].className.replace("gpt_newcontragents_link_hover","").replace(/\s+/g,' ').replace(/^\s+|\s+$/g,'');
      }
    }

    function addByReqId(reqId){
      if (!reqId || reqId <= 0) return;

      // requisites row: its own id == reqId
      var r = reqWrap.querySelectorAll("[data-req-id='"+reqId+"']");
      for (var i=0;i<r.length;i++) r[i].className += " gpt_newcontragents_link_hover";

      // contacts linked to this reqId
      var c = conWrap.querySelectorAll("[data-req-id='"+reqId+"']");
      for (var j=0;j<c.length;j++) c[j].className += " gpt_newcontragents_link_hover";
    }

    function findParentAttr(node, attr){
      while (node && node !== document.body){
        if (node.getAttribute && node.getAttribute(attr) !== null) return node;
        node = node.parentNode;
      }
      return null;
    }

    function over(e){
      var n = e.target;

      // contact card
      var cc = findParentAttr(n, "data-contact-id");
      if (cc){
        clearAll();
        var reqId = parseInt(cc.getAttribute("data-req-id"),10) || 0;
        addByReqId(reqId);
        return;
      }

      // requisites row
      var rr = findParentAttr(n, "data-req-id");
      if (rr && rr.className && rr.className.indexOf("gpt_newcontragents_reqRow") !== -1){
        clearAll();
        var rid = parseInt(rr.getAttribute("data-req-id"),10) || 0;
        addByReqId(rid);
        return;
      }
    }

    function out(){
      clearAll();
    }

    conWrap.addEventListener("mouseover", over);
    reqWrap.addEventListener("mouseover", over);
    conWrap.addEventListener("mouseleave", out);
    reqWrap.addEventListener("mouseleave", out);
  }

  /* =============================
     Contact popup
     ============================= */
  function showContactPopup(){
    el("gpt_newcontragents_contactPopupOverlay").className = "gpt_newcontragents_subOverlay";
  }
  function hideContactPopup(){
    el("gpt_newcontragents_contactPopupOverlay").className = "gpt_newcontragents_subOverlay gpt_newcontragents_hidden";
  }

  function openContactPopup(contactId){
    el("gpt_newcontragents_contactPopupHint").innerHTML = "";

    // rebuild select in popup
    var sel = el("gpt_newcontragents_c_requisite");
    if (sel){
      var opt = '<option value="0">Привязка к реквизитам (не выбрано)</option>';
      for (var rid in gpt_newcontragents_cacheRequisites){
        if (!gpt_newcontragents_cacheRequisites.hasOwnProperty(rid)) continue;
        var r = gpt_newcontragents_cacheRequisites[rid];
        opt += '<option value="'+r.gpt_id+'">'+htmlEscape(r.gpt_legal_name||("ID "+r.gpt_id))+'</option>';
      }
      sel.innerHTML = opt;
    }

    // новый контакт
    if (!contactId){
      el("gpt_newcontragents_contactPopupTitle").innerHTML = "Добавить контакт";
      el("gpt_newcontragents_c_id").value = "0";
      el("gpt_newcontragents_c_name").value = "";
      el("gpt_newcontragents_c_phone").value = "";
      el("gpt_newcontragents_c_email").value = "";
      el("gpt_newcontragents_c_comment").value = "";
      el("gpt_newcontragents_c_chat_id").value = "";
      el("gpt_newcontragents_c_requisite").value = "0";
      el("gpt_newcontragents_c_default").checked = false;
      el("gpt_newcontragents_c_notify").checked = false;
      el("gpt_newcontragents_c_invoice") && (el("gpt_newcontragents_c_invoice").checked = false);

      gptNcFillOldPanel("gpt_newcontragents_oldData_contact");
      showContactPopup();
      return;
    }

    // редактирование
    var c = gpt_newcontragents_cacheContacts[contactId];
    if (!c){
      el("gpt_newcontragents_contactPopupHint").innerHTML = "Контакт не найден. Нажми «Обновить».";
      gptNcFillOldPanel("gpt_newcontragents_oldData_contact");
      showContactPopup();
      return;
    }

    el("gpt_newcontragents_contactPopupTitle").innerHTML = "Редактировать контакт";
    el("gpt_newcontragents_c_id").value = String(c.gpt_id);
    el("gpt_newcontragents_c_name").value = c.gpt_name || "";
    el("gpt_newcontragents_c_chat_id").value = c.gpt_chat_id || "";
    el("gpt_newcontragents_c_phone").value = c.gpt_phone || "";
    el("gpt_newcontragents_c_email").value = c.gpt_email || "";
    el("gpt_newcontragents_c_comment").value = c.gpt_comment || "";
    el("gpt_newcontragents_c_requisite").value = String(c.gpt_requisite_id || 0);
    el("gpt_newcontragents_c_default").checked = (c.gpt_is_default == 1);
    el("gpt_newcontragents_c_notify").checked = (c.gpt_is_notify_default == 1);
    el("gpt_newcontragents_c_invoice") && (el("gpt_newcontragents_c_invoice").checked = (c.gpt_is_invoice_default == 1));

    gptNcFillOldPanel("gpt_newcontragents_oldData_contact");
    showContactPopup();
  }


function saveContact(){
  var cid = gpt_newcontragents_selectedContragentId;
  if (!cid) return;

  var id = parseInt(el("gpt_newcontragents_c_id").value,10) || 0;
  var name = el("gpt_newcontragents_c_name").value || "";
  var chatId = (el("gpt_newcontragents_c_chat_id").value || "").trim();

  var phone = el("gpt_newcontragents_c_phone").value || "";
  var email = el("gpt_newcontragents_c_email").value || "";
  var comment = el("gpt_newcontragents_c_comment").value || "";
  var rid = parseInt(el("gpt_newcontragents_c_requisite").value,10) || 0;
  var isdef = el("gpt_newcontragents_c_default").checked ? 1 : 0;
  var isnot = el("gpt_newcontragents_c_notify").checked ? 1 : 0;

  if (name.replace(/\s+/g,'').length < 2){
    el("gpt_newcontragents_contactPopupHint").innerHTML = "Укажи имя контакта.";
    return;
  }

  var action = id ? "update_contact" : "add_contact";

  http("POST", gpt_newcontragents_api + "?gpt_action=" + action, qs({
    gpt_contragent_id: cid,
    gpt_contact_id: id,
    gpt_name: name,
    gpt_chat_id: chatId,            // <<< ВОТ ЭТОГО НЕ ХВАТАЛО
    gpt_phone: phone,
    gpt_email: email,
    gpt_comment: comment,
    gpt_requisite_id: rid,
    gpt_is_default: isdef,
    gpt_is_notify_default: isnot
  }), function(err, json){
    if (err || !json || !json.ok){
      el("gpt_newcontragents_contactPopupHint").innerHTML = "Ошибка сохранения.";
      return;
    }
    hideContactPopup();
    reloadAll();
  });
}




  function showDeliveryPopup(){
  el("gpt_newcontragents_deliveryPopupOverlay").className = "gpt_newcontragents_subOverlay";
}
function hideDeliveryPopup(){
  el("gpt_newcontragents_deliveryPopupOverlay").className = "gpt_newcontragents_subOverlay gpt_newcontragents_hidden";
  el("gpt_newcontragents_deliveryPopupHint").innerHTML = "";
}


function showDeliveryPopup(){
  el("gpt_newcontragents_deliveryPopupOverlay").className = "gpt_newcontragents_subOverlay";
}
function hideDeliveryPopup(){
  el("gpt_newcontragents_deliveryPopupOverlay").className = "gpt_newcontragents_subOverlay gpt_newcontragents_hidden";
  el("gpt_newcontragents_deliveryPopupHint").innerHTML = "";
}

function openDeliveryPopupNew(){
  el("gpt_newcontragents_deliveryPopupTitle").innerHTML = "Добавить адрес доставки";
  el("gpt_newcontragents_d_id").value = "0";
  el("gpt_newcontragents_d_address").value = "";
  el("gpt_newcontragents_d_default").checked = false;
  el("gpt_newcontragents_d_title").value = "";

  gptNcFillOldPanel("gpt_newcontragents_oldData_delivery");
  showDeliveryPopup();
}

function openDeliveryPopupEdit(deliveryId){
  var d = gpt_newcontragents_cacheDelivery[deliveryId];
  if (!d){
    openDeliveryPopupNew();
    return;
  }

  el("gpt_newcontragents_deliveryPopupTitle").innerHTML = "Редактировать адрес доставки";
  el("gpt_newcontragents_d_id").value = String(d.gpt_id || 0);
  el("gpt_newcontragents_d_address").value = d.gpt_address || "";
  el("gpt_newcontragents_d_default").checked = (parseInt(d.gpt_is_default,10) === 1);
  el("gpt_newcontragents_d_title").value = d.gpt_title || "";

  gptNcFillOldPanel("gpt_newcontragents_oldData_delivery");
  showDeliveryPopup();
}


function saveDelivery(){
  var cid = gpt_newcontragents_selectedContragentId;
  var did = parseInt(el("gpt_newcontragents_d_id").value,10) || 0;
  var addr = (el("gpt_newcontragents_d_address").value || "").trim();
  var isdef = el("gpt_newcontragents_d_default").checked ? 1 : 0;
  var title = (el("gpt_newcontragents_d_title").value || "").trim();

  if (!cid){ el("gpt_newcontragents_deliveryPopupHint").innerHTML = "Не выбран контрагент"; return; }
  if (!addr){ el("gpt_newcontragents_deliveryPopupHint").innerHTML = "Адрес пустой"; return; }

  var action = (did > 0) ? "update_delivery" : "add_delivery";
  var payload = {
    gpt_contragent_id: cid,
    gpt_address: addr,
    gpt_title: title,
    gpt_is_default: isdef
  };
  if (did > 0) payload.gpt_delivery_id = did;

  http("POST", gpt_newcontragents_api + "?gpt_action=" + action, qs(payload), function(err, json){
    if (err || !json || !json.ok){
      el("gpt_newcontragents_deliveryPopupHint").innerHTML = "Ошибка сохранения";
      return;
    }
    hideDeliveryPopup();
    reloadAll();
  });
}

function deleteDelivery(deliveryId){
  var cid = gpt_newcontragents_selectedContragentId;
  if (!cid || !deliveryId) return;

  if (!confirm("Удалить адрес доставки?")) return;

  http("POST", gpt_newcontragents_api + "?gpt_action=delete_delivery", qs({
    gpt_contragent_id: cid,
    gpt_delivery_id: deliveryId
  }), function(err, json){
    if (err || !json || !json.ok){
      console.warn("delete_delivery failed", err, json);
      reloadAll();
      return;
    }
    reloadAll();
  });
}

function toggleDefaultDelivery(deliveryId){
  var cid = gpt_newcontragents_selectedContragentId;
  if (!cid || !deliveryId) return;

  http("POST", gpt_newcontragents_api + "?gpt_action=set_default_delivery", qs({
    gpt_contragent_id: cid,
    gpt_delivery_id: deliveryId
  }), function(err, json){
    if (err || !json || !json.ok){
      console.warn("set_default_delivery failed", err, json);
      reloadAll();
      return;
    }
    reloadAll();
  });
}



  /* =============================
     Requisite popup
     ============================= */
  function showRequisitePopup(){
    el("gpt_newcontragents_requisitePopupOverlay").className = "gpt_newcontragents_subOverlay";
  }
  function hideRequisitePopup(){
    el("gpt_newcontragents_requisitePopupOverlay").className = "gpt_newcontragents_subOverlay gpt_newcontragents_hidden";
  }

function openRequisitePopup(reqId){
  el("gpt_newcontragents_requisitePopupHint").innerHTML = "";

  // новый набор реквизитов
  if (!reqId){
    el("gpt_newcontragents_requisitePopupTitle").innerHTML = "Добавить реквизиты";
    el("gpt_newcontragents_r_id").value = "0";
    el("gpt_newcontragents_r_contract_no").value = "";
    el("gpt_newcontragents_r_legal").value = "";
    el("gpt_newcontragents_r_inn").value = "";
    el("gpt_newcontragents_r_jur_address").value = "";
    el("gpt_newcontragents_r_rs").value = "";
    el("gpt_newcontragents_r_bank_name").value = "";
    el("gpt_newcontragents_r_bank_bik").value = "";
    el("gpt_newcontragents_r_director_fio").value = "";
    el("gpt_newcontragents_r_basis").value = "";
    el("gpt_newcontragents_r_tax_mode").value = "";
    el("gpt_newcontragents_r_details").value = "";
    el("gpt_newcontragents_r_dadata_json").value = "";
    syncDaDataBtn();
    el("gpt_newcontragents_r_default").checked = false;

    gptNcFillOldPanel("gpt_newcontragents_oldData_requisite");
    showRequisitePopup();
    return;
  }

  // редактирование
  var r = gpt_newcontragents_cacheRequisites[reqId];
  if (!r){
    gptNcFillOldPanel("gpt_newcontragents_oldData_requisite");
    showRequisitePopup();
    return;
  }

  el("gpt_newcontragents_requisitePopupTitle").innerHTML = "Редактировать реквизиты";
  el("gpt_newcontragents_r_id").value = String(r.gpt_id || 0);

  el("gpt_newcontragents_r_legal").value = r.gpt_legal_name || "";
  el("gpt_newcontragents_r_inn").value = r.gpt_inn || "";
  el("gpt_newcontragents_r_jur_address").value = r.gpt_jur_address || "";
  el("gpt_newcontragents_r_rs").value = r.gpt_rs || "";
  el("gpt_newcontragents_r_bank_name").value = r.gpt_bank_name || "";
  el("gpt_newcontragents_r_bank_bik").value = r.gpt_bank_bik || "";
  el("gpt_newcontragents_r_director_fio").value = r.gpt_director_fio || "";
  el("gpt_newcontragents_r_basis").value = r.gpt_basis || "";
  el("gpt_newcontragents_r_tax_mode").value = r.gpt_tax_mode || "";
  el("gpt_newcontragents_r_details").value = r.gpt_details || "";
  el("gpt_newcontragents_r_dadata_json").value = r.gpt_dadata_json || "";
  syncDaDataBtn();
  el("gpt_newcontragents_r_default").checked = (parseInt(r.gpt_is_default,10) === 1);

  gptNcFillOldPanel("gpt_newcontragents_oldData_requisite");
  showRequisitePopup();
}



function saveRequisite(){
  var cid = gpt_newcontragents_selectedContragentId;
  if (!cid) return;

  var id      = parseInt(el("gpt_newcontragents_r_id").value, 10) || 0;

  var legal   = el("gpt_newcontragents_r_legal").value || "";
  var inn     = el("gpt_newcontragents_r_inn").value || "";
  var contractNo = el("gpt_newcontragents_r_contract_no").value || "";
  var jur     = el("gpt_newcontragents_r_jur_address").value || "";
  var rs      = el("gpt_newcontragents_r_rs").value || "";
  var bank    = el("gpt_newcontragents_r_bank_name").value || "";
  var bik     = el("gpt_newcontragents_r_bank_bik").value || "";
  var director= el("gpt_newcontragents_r_director_fio").value || "";
  var basis   = el("gpt_newcontragents_r_basis").value || "";
  var tax     = el("gpt_newcontragents_r_tax_mode").value || "";
  var details = el("gpt_newcontragents_r_details").value || "";

  var djson   = el("gpt_newcontragents_r_dadata_json").value || "";

  var isdef   = el("gpt_newcontragents_r_default").checked ? 1 : 0;

  if (legal.replace(/\s+/g,'').length < 2){
    el("gpt_newcontragents_requisitePopupHint").innerHTML = "Укажи название юрлица / ИП.";
    return;
  }

  var action = id ? "update_requisite" : "add_requisite";

  http("POST", gpt_newcontragents_api + "?gpt_action=" + action, qs({
    gpt_contragent_id: cid,
    gpt_requisite_id: id,

    gpt_legal_name: legal,
    gpt_inn: inn,
    gpt_contract_no: contractNo,
    gpt_jur_address: jur,
    gpt_rs: rs,
    gpt_bank_name: bank,
    gpt_bank_bik: bik,
    gpt_director_fio: director,
    gpt_basis: basis,
    gpt_tax_mode: tax,

    gpt_details: details,
    gpt_dadata_json: djson,

    gpt_is_default: isdef
  }), function(err, json){
    if (err || !json || !json.ok){
      el("gpt_newcontragents_requisitePopupHint").innerHTML = (json && json.error) ? json.error : "Ошибка сохранения.";
      return;
    }
    hideRequisitePopup();
    reloadAll();
  });
}





function syncDaDataBtn(){
  var btn = el("gpt_newcontragents_dadataBtn");
  if (!btn) return;

  var inn = (el("gpt_newcontragents_r_inn").value || "").replace(/\D+/g,'');
  var haveJson = (el("gpt_newcontragents_r_dadata_json").value || "").length > 0;

  // если JSON уже есть — кнопку не показываем
  if (haveJson){
    btn.className = "gpt_newcontragents_mini_btn gpt_newcontragents_dadata_btn gpt_newcontragents_hidden";
    return;
  }

  // показываем кнопку только если ИНН уже начали вводить (и он похож на ИНН)
  if (inn.length >= 8){
    btn.className = "gpt_newcontragents_mini_btn gpt_newcontragents_dadata_btn";
  } else {
    btn.className = "gpt_newcontragents_mini_btn gpt_newcontragents_dadata_btn gpt_newcontragents_hidden";
  }
}

function applyDaDataToForm(dadataRaw, dadataJson){
  // 0) сохраняем полный JSON (как строку) в hidden (для БД)
  el("gpt_newcontragents_r_dadata_json").value = dadataRaw || "";

  // берём первую подсказку
  var s = null;
  if (dadataJson && dadataJson.suggestions && dadataJson.suggestions.length){
    s = dadataJson.suggestions[0];
  }
  if (!s) {
    syncDaDataBtn();
    return;
  }

  var d = s.data || {};

  // 1) Название: s.value
  if (s.value) el("gpt_newcontragents_r_legal").value = s.value;

  // 2) Адрес: data.address.unrestricted_value (если нет — value)
  var addr = "";
  if (d.address && d.address.unrestricted_value) addr = d.address.unrestricted_value;
  else if (d.address && d.address.value) addr = d.address.value;

  if (addr) el("gpt_newcontragents_r_jur_address").value = addr;

  // 3) ФИО директора: data.management.name
  var director = "";
  if (d.management && d.management.name) director = d.management.name;
  if (director) el("gpt_newcontragents_r_director_fio").value = director;

  // 4) tax_system -> в начало примечания (если пришло)
  var tax = "";
  if (d.finance && d.finance.tax_system) tax = d.finance.tax_system;

  if (tax){
    var noteEl = el("gpt_newcontragents_r_details");
    var cur = noteEl.value || "";
    var prefix = tax + "\n";
    if (cur.indexOf(prefix) !== 0){
      noteEl.value = prefix + cur;
    }
  }

  // 5) после появления json — кнопку спрятать
  syncDaDataBtn();
}


function requestDaDataByInn(){
  var innEl = el("gpt_newcontragents_r_inn");
  if (!innEl) return;

  var inn = (innEl.value || "").replace(/\D+/g,'');
  if (inn.length < 8){
    el("gpt_newcontragents_requisitePopupHint").innerHTML = "Введите ИНН.";
    return;
  }

  var btn = el("gpt_newcontragents_dadataBtn");
  if (btn) btn.innerHTML = "Запрос…";

  http("GET", gpt_newcontragents_api + "?gpt_action=dadata_by_inn&gpt_inn=" + encodeURIComponent(inn), null, function(err, json){
    if (btn) btn.innerHTML = "Запросить данные";

    if (err || !json || !json.ok || !json.data){
      el("gpt_newcontragents_requisitePopupHint").innerHTML = "Ошибка запроса DaData.";
      return;
    }

    var raw = json.data.raw || "";
    var dj = json.data.json || null;

    // если suggestions пустой — сообщим
    if (!dj || !dj.suggestions || !dj.suggestions.length){
      el("gpt_newcontragents_requisitePopupHint").innerHTML = "DaData: ничего не найдено по ИНН.";
      return;
    }

    applyDaDataToForm(raw, dj);
    el("gpt_newcontragents_requisitePopupHint").innerHTML = "";
  });
}






  /* =============================
     Contragent popup (add/edit)
     ============================= */
  function updateHeaderButtonsState(){
    var editBtn = el("gpt_newcontragents_editContragentBtn");
    if (!editBtn) return;

    if (gpt_newcontragents_selectedContragentId > 0){
      editBtn.className = "gpt_newcontragents_mini_btn";
    } else {
      editBtn.className = "gpt_newcontragents_mini_btn gpt_newcontragents_btn_disabled";
    }
  }

  function showContragentPopup(){
    el("gpt_newcontragents_contragentPopupOverlay").className = "gpt_newcontragents_subOverlay";
  }
  function hideContragentPopup(){
    el("gpt_newcontragents_contragentPopupOverlay").className = "gpt_newcontragents_subOverlay gpt_newcontragents_hidden";
  }

 function openContragentPopup(mode){ // mode: "add" | "edit"
  el("gpt_newcontragents_contragentPopupHint").innerHTML = "";

  if (mode === "add"){
    el("gpt_newcontragents_contragentPopupTitle").innerHTML = "Добавить контрагента";
    el("gpt_newcontragents_contragent_id").value = "0";
    el("gpt_newcontragents_contragent_name").value = "";
    el("gpt_newcontragents_contragent_keywords").value = "";
    el("gpt_newcontragents_contragent_note").value = "";
    showContragentPopup();
    return;
  }

  // edit
  var cid = parseInt(gpt_newcontragents_selectedContragentId, 10) || 0;
  if (!cid){
    el("gpt_newcontragents_contragentPopupHint").innerHTML = "Не выбран контрагент";
    return;
  }

  el("gpt_newcontragents_contragentPopupTitle").innerHTML = "Редактировать контрагента";
  el("gpt_newcontragents_contragent_id").value = String(cid);
  el("gpt_newcontragents_contragent_name").value = gpt_newcontragents_selectedContragentName || "";
  el("gpt_newcontragents_contragent_keywords").value = "";
  el("gpt_newcontragents_contragent_note").value = "";

  showContragentPopup();

  // подтянуть note/keywords с сервера
  http("GET",
    gpt_newcontragents_api + "?gpt_action=get_contragent&gpt_contragent_id=" + cid,
    null,
    function(err, json){
      if (err || !json || !json.ok || !json.data){
        el("gpt_newcontragents_contragentPopupHint").innerHTML = (json && json.err) ? ("Ошибка: " + json.err) : "Не удалось загрузить данные";
        return;
      }
      if (json.data.name !== undefined) el("gpt_newcontragents_contragent_name").value = json.data.name || "";
      if (json.data.keywords !== undefined) el("gpt_newcontragents_contragent_keywords").value = json.data.keywords || "";
      if (json.data.note !== undefined) el("gpt_newcontragents_contragent_note").value = json.data.note || "";
    }
  );
}


function saveContragent(){
  var cid = parseInt(el("gpt_newcontragents_contragent_id").value, 10) || 0;

  var name = (el("gpt_newcontragents_contragent_name").value || "").trim();
  var keywords = (el("gpt_newcontragents_contragent_keywords").value || "").trim();
  var note = (el("gpt_newcontragents_contragent_note").value || "").trim();

  if (name.replace(/\s+/g,'').length < 2){
    el("gpt_newcontragents_contragentPopupHint").innerHTML = "Укажи название.";
    return;
  }

  var action = (cid > 0) ? "update_contragent" : "add_contragent";

  var payload = {
    gpt_contragent_id: cid,
    gpt_name: name,
    gpt_keywords: keywords,
    gpt_note: note
  };

  http("POST", gpt_newcontragents_api + "?gpt_action=" + action, qs(payload), function(err, json){
    if (err || !json || !json.ok){
      el("gpt_newcontragents_contragentPopupHint").innerHTML =
        (json && json.err) ? ("Ошибка: " + json.err) : "Ошибка сохранения";
      return;
    }

    // сервер может вернуть id/name
    if (json.data && json.data.id) gpt_newcontragents_selectedContragentId = parseInt(json.data.id,10) || gpt_newcontragents_selectedContragentId;
    if (json.data && json.data.name) gpt_newcontragents_selectedContragentName = json.data.name;

    setBadge();

    var inp = el("gpt_newcontragents_searchInput");
    if (inp) inp.value = gpt_newcontragents_selectedContragentName || name;

    el("gpt_newcontragents_rightEmpty").className = "gpt_newcontragents_hidden";
    el("gpt_newcontragents_rightBody").className = "";

    hideContragentPopup();
    reloadAll();
  });
}



  /* =============================
     bind ui events
     ============================= */
  if (el("gpt_newcontragents_openBtn")) el("gpt_newcontragents_openBtn").onclick = openModal;
  if (el("gpt_newcontragents_closeBtn")) el("gpt_newcontragents_closeBtn").onclick = closeModal;

if (el("gpt_newcontragents_overlay")){
  el("gpt_newcontragents_overlay").onclick = function(e){
    // ничего не делаем: не закрываем по клику в пустоту
  };
}

  if (el("gpt_newcontragents_searchInput")){
    el("gpt_newcontragents_searchInput").onkeyup = function(){
      var term = this.value || "";
      if (searchTimer) clearTimeout(searchTimer);
      searchTimer = setTimeout(function(){ doSearch(term); }, 250);
    };
    el("gpt_newcontragents_searchInput").onfocus = function(){
      var term = this.value || "";
      if (term.length >= 2) el("gpt_newcontragents_searchResults").style.display = "block";
    };
  }

  document.addEventListener("click", function(e){
    var box = el("gpt_newcontragents_searchResults");
    var inp = el("gpt_newcontragents_searchInput");
    if (!box || !inp) return;
    if (e.target === inp || box.contains(e.target)) return;
    hideSearchList();
  });

  // buttons top right blocks
  if (el("gpt_newcontragents_addContactOpenBtn")) el("gpt_newcontragents_addContactOpenBtn").onclick = function(){ openContactPopup(0); };
  if (el("gpt_newcontragents_addRequisiteOpenBtn")) el("gpt_newcontragents_addRequisiteOpenBtn").onclick = function(){ openRequisitePopup(0); };
  if (el("gpt_newcontragents_reloadContactsBtn")) el("gpt_newcontragents_reloadContactsBtn").onclick = function(){ loadContacts(function(){ renderContactsCards(); bindLinkHover(); }); };
  if (el("gpt_newcontragents_reloadRequisitesBtn")) el("gpt_newcontragents_reloadRequisitesBtn").onclick = function(){ loadRequisites(function(){ renderRequisitesCards(); bindLinkHover(); }); };

  // popups
  if (el("gpt_newcontragents_contactPopupCloseBtn")) el("gpt_newcontragents_contactPopupCloseBtn").onclick = hideContactPopup;
  if (el("gpt_newcontragents_contactCancelBtn")) el("gpt_newcontragents_contactCancelBtn").onclick = hideContactPopup;
  if (el("gpt_newcontragents_contactSaveBtn")) el("gpt_newcontragents_contactSaveBtn").onclick = saveContact;

  if (el("gpt_newcontragents_requisitePopupCloseBtn")) el("gpt_newcontragents_requisitePopupCloseBtn").onclick = hideRequisitePopup;
  if (el("gpt_newcontragents_requisiteCancelBtn")) el("gpt_newcontragents_requisiteCancelBtn").onclick = hideRequisitePopup;
  if (el("gpt_newcontragents_requisiteSaveBtn")) el("gpt_newcontragents_requisiteSaveBtn").onclick = saveRequisite;
  if (el("gpt_newcontragents_dadataBtn")) el("gpt_newcontragents_dadataBtn").onclick = requestDaDataByInn;


// delivery
if (el("gpt_newcontragents_addDeliveryOpenBtn"))
  el("gpt_newcontragents_addDeliveryOpenBtn").onclick = openDeliveryPopupNew;

if (el("gpt_newcontragents_reloadDeliveryBtn"))
  el("gpt_newcontragents_reloadDeliveryBtn").onclick = function(){
    loadDelivery(function(){ renderDeliveryCards(); });
  };

if (el("gpt_newcontragents_deliveryPopupCloseBtn"))
  el("gpt_newcontragents_deliveryPopupCloseBtn").onclick = hideDeliveryPopup;

if (el("gpt_newcontragents_deliveryCancelBtn"))
  el("gpt_newcontragents_deliveryCancelBtn").onclick = hideDeliveryPopup;

if (el("gpt_newcontragents_deliverySaveBtn"))
  el("gpt_newcontragents_deliverySaveBtn").onclick = saveDelivery;




if (el("gpt_newcontragents_r_inn")) {
  el("gpt_newcontragents_r_inn").addEventListener("input", function(){
    // если пользователь поменял ИНН — сбросим старый JSON и дадим возможность запросить снова
    el("gpt_newcontragents_r_dadata_json").value = "";
    syncDaDataBtn();
  });
}

    // contragent add/edit
  if (el("gpt_newcontragents_addContragentBtn")) el("gpt_newcontragents_addContragentBtn").onclick = function(){ openContragentPopup("add"); };
  if (el("gpt_newcontragents_editContragentBtn")) el("gpt_newcontragents_editContragentBtn").onclick = function(){ openContragentPopup("edit"); };

  if (el("gpt_newcontragents_contragentPopupCloseBtn")) el("gpt_newcontragents_contragentPopupCloseBtn").onclick = hideContragentPopup;
  if (el("gpt_newcontragents_contragentCancelBtn")) el("gpt_newcontragents_contragentCancelBtn").onclick = hideContragentPopup;
  if (el("gpt_newcontragents_contragentSaveBtn")) el("gpt_newcontragents_contragentSaveBtn").onclick = saveContragent;


// принимать contragent_id от родительской страницы (iframe)
window.addEventListener("message", function(e){
  if (!e || !e.data) return;
  if (e.data.type !== 'gpt_newcontragents_set_contragent') return;

  var id = parseInt(e.data.id, 10) || 0;
  if (id <= 0) return;

  // выбрать контрагента по id (имя подтянем отдельно)
  gpt_newcontragents_selectedContragentId = id;
  gpt_newcontragents_selectedContragentName = "";
  setBadge();

  // показать правую часть и загрузить данные
  el("gpt_newcontragents_rightEmpty").className = "gpt_newcontragents_hidden";
  el("gpt_newcontragents_rightBody").className = "";
  reloadAll();

  // подтянуть имя контрагента по id (из contragents) и обновить UI
  http(
    "GET",
    gpt_newcontragents_api + "?gpt_action=get_contragent&gpt_contragent_id=" + id,
    null,
    function(err, json){
      if (!err && json && json.ok && json.data && json.data.name) {
        gpt_newcontragents_selectedContragentName = json.data.name;
        setBadge();

        var inp = el("gpt_newcontragents_searchInput");
        if (inp) inp.value = json.data.name;
      }
    }
  );

}, false);


  // init badge
  setBadge();

  // ===== Public widget API (host modules) =====
  window.GPT_Contragents = {
    open: function(opts){
      opts = opts || {};
      // optional callback when a contragent is picked (selected)
      this._onPick = (typeof opts.onPick === 'function') ? opts.onPick : null;
      if (opts.preset && opts.preset.contragent_id){
        var pid = parseInt(opts.preset.contragent_id,10) || 0;
        if (pid > 0) {
          // load name and select
          http(
            "GET",
            gpt_newcontragents_api + "?gpt_action=get_contragent&gpt_contragent_id=" + pid,
            null,
            function(err, json){
              if (!err && json && json.ok && json.data && json.data.name){
                selectContragent(pid, json.data.name);
              }
            }
          );
        }
      }
      if (typeof openModal === 'function') openModal();
    },
    close: function(){
      if (typeof closeModal === 'function') closeModal();
    },
    getSelected: function(){
      return {id: gpt_newcontragents_selectedContragentId, name: gpt_newcontragents_selectedContragentName};
    },
    _onPick: null
  };



})();
