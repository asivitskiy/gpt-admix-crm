<div class="gpt_newcontragents_page">
    <div class="gpt_newcontragents_btn" id="gpt_newcontragents_openBtn" style="display:none">ОТКРЫТЬ КОНСТРУКТОР</div>

    <div class="gpt_newcontragents_overlay" id="gpt_newcontragents_overlay">
        <div class="gpt_newcontragents_modal">

            <div class="gpt_newcontragents_modal_header">
                <div class="gpt_newcontragents_modal_title">
                    
                    <span class="gpt_newcontragents_badge" id="gpt_newcontragents_selectedBadge">не выбран</span>
                </div>

                <div class="gpt_newcontragents_topPick">
                    <input class="gpt_newcontragents_input gpt_newcontragents_topPickInput"
                           id="gpt_newcontragents_searchInput"
                           placeholder="Начни вводить контрагента..."
                           autocomplete="off" />
                    <div class="gpt_newcontragents_list gpt_newcontragents_topPickList"
                         id="gpt_newcontragents_searchResults"></div>
                </div>
                
            <div class="gpt_newcontragents_headerActions">
              <div id="gpt_newcontragents_addContragentBtn">+ Новый</div>
              <div id="gpt_newcontragents_editContragentBtn">Редактировать</div>
              <div id="gpt_newcontragents_closeBtn">Закрыть</div>
            </div>

            </div>

            <div class="gpt_newcontragents_modal_body">

                <div id="gpt_newcontragents_rightEmpty" class="gpt_newcontragents_okhint">
                    Выбери контрагента сверху (начни вводить название).
                </div>

                <div id="gpt_newcontragents_rightBody" class="gpt_newcontragents_hidden">

                    <div class="gpt_newcontragents_twoCols">

                        <!-- LEFT: REQUISITES -->
                        <div class="gpt_newcontragents_col">
                            <div class="gpt_newcontragents_colHead">
                                <div class="gpt_newcontragents_h">Реквизиты</div>
                                <div class="gpt_newcontragents_headActions">
                                    <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_addRequisiteOpenBtn">+ Добавить</div>
                                    <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_reloadRequisitesBtn">Обновить</div>
                                </div>
                            </div>

                            <div class="gpt_newcontragents_colBody" id="gpt_newcontragents_requisitesList">
                                <!-- cards -->
                            </div>
                        </div>

                        <!-- RIGHT: CONTACTS -->
                        <div class="gpt_newcontragents_col">
                            <div class="gpt_newcontragents_colHead">
                                <div class="gpt_newcontragents_h">Контакты</div>
                                <div class="gpt_newcontragents_headActions">
                                    <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_addContactOpenBtn">+ Добавить</div>
                                    <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_reloadContactsBtn">Обновить</div>
                                </div>
                            </div>

                            <div class="gpt_newcontragents_colBody" id="gpt_newcontragents_contactsList">
                                <!-- cards -->
                            </div>
                        </div>

                        <!-- THIRD: DELIVERY -->
                        <div class="gpt_newcontragents_col">
                          <div class="gpt_newcontragents_colHead">
                            <div class="gpt_newcontragents_h">Доставка</div>
                            <div class="gpt_newcontragents_headActions">
                              <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_addDeliveryOpenBtn">+ Добавить</div>
                              <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_reloadDeliveryBtn">Обновить</div>
                            </div>
                          </div>

                          <div class="gpt_newcontragents_colBody" id="gpt_newcontragents_deliveryList">
                            <!-- cards -->
                          </div>
                        </div>

                    
                    
                    </div><!-- twoCols -->

                </div><!-- rightBody -->
            </div><!-- modal body -->




            <!-- POPUP: DELIVERY -->
            <div class="gpt_newcontragents_subOverlay gpt_newcontragents_hidden" id="gpt_newcontragents_deliveryPopupOverlay">
              <div class="gpt_newcontragents_subModal">
                <div class="gpt_newcontragents_subHeader">
                  <div class="gpt_newcontragents_subTitle" id="gpt_newcontragents_deliveryPopupTitle">Адрес доставки</div>
                  <div class="gpt_newcontragents_closeMini" id="gpt_newcontragents_deliveryPopupCloseBtn">✕</div>
                </div>

                <div class="gpt_newcontragents_subBody">
                  <div class="gpt_newcontragents_popupGrid">
                    <div class="gpt_newcontragents_popupMain">
                      <input type="hidden" id="gpt_newcontragents_d_id" value="0" />

                      <div class="gpt_newcontragents_row">
                        <input class="gpt_newcontragents_input" id="gpt_newcontragents_d_title" placeholder="Название (кратко)" />

                        <textarea class="gpt_newcontragents_textarea" id="gpt_newcontragents_d_address"
                                  placeholder="Адрес доставки"></textarea>

                        <label>
                          <input type="checkbox" id="gpt_newcontragents_d_default" />
                          <span>Основной адрес доставки</span>
                        </label>

                        <div class="gpt_newcontragents_actions">
                          <div class="gpt_newcontragents_mini_btn gpt_newcontragents_primary_btn" id="gpt_newcontragents_deliverySaveBtn">Сохранить</div>
                          <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_deliveryCancelBtn">Отмена</div>
                        </div>

                        <div class="gpt_newcontragents_okhint" id="gpt_newcontragents_deliveryPopupHint"></div>
                      </div>
                    </div>

                    <div class="gpt_newcontragents_popupSide">
                      <div class="gpt_newcontragents_oldBlock" id="gpt_newcontragents_oldData_delivery">
                        <div class="gpt_newcontragents_oldBlockTitle">Старые данные</div>

                        <div class="gpt_newcontragents_oldBlockItem">
                          <div class="gpt_newcontragents_oldBlockLabel">Реквизиты (fullinfo)</div>
                          <div class="gpt_newcontragents_oldBlockValue" data-old-field="fullinfo"></div>
                        </div>

                        <div class="gpt_newcontragents_oldBlockItem">
                          <div class="gpt_newcontragents_oldBlockLabel">Адрес</div>
                          <div class="gpt_newcontragents_oldBlockValue" data-old-field="address"></div>
                        </div>

                        <div class="gpt_newcontragents_oldBlockItem">
                          <div class="gpt_newcontragents_oldBlockLabel">Телефон для оповещений</div>
                          <div class="gpt_newcontragents_oldBlockValue" data-old-field="notification_number"></div>
                        </div>

                        <div class="gpt_newcontragents_oldBlockItem">
  <div class="gpt_newcontragents_oldBlockLabel">Контакты (contacts)</div>
  <div class="gpt_newcontragents_oldBlockValue" data-old-field="contacts">—</div>
</div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            
            <!-- POPUP: CONTACT -->
            <div class="gpt_newcontragents_subOverlay gpt_newcontragents_hidden" id="gpt_newcontragents_contactPopupOverlay">
                <div class="gpt_newcontragents_subModal">
                    <div class="gpt_newcontragents_subHeader">
                        <div class="gpt_newcontragents_subTitle" id="gpt_newcontragents_contactPopupTitle">Контакт</div>
                        <div class="gpt_newcontragents_closeMini" id="gpt_newcontragents_contactPopupCloseBtn">✕</div>
                    </div>

                    <div class="gpt_newcontragents_subBody">
                        <div class="gpt_newcontragents_popupGrid">
                            <div class="gpt_newcontragents_popupMain">
                                <input type="hidden" id="gpt_newcontragents_c_id" value="0" />

                                <div class="gpt_newcontragents_row">
                                    <input class="gpt_newcontragents_input" id="gpt_newcontragents_c_name" placeholder="Имя контакта (обязательно)" />
                                    <input class="gpt_newcontragents_input" id="gpt_newcontragents_c_phone" placeholder="Телефон" />
                                    <input class="gpt_newcontragents_input" id="gpt_newcontragents_c_email" placeholder="Email" />
                                    <input id="gpt_newcontragents_c_chat_id"
       class="gpt_newcontragents_input"
       type="text"
       placeholder="WAMM chat_id (TG/WA идентификатор)">
                                    <select class="gpt_newcontragents_select" id="gpt_newcontragents_c_requisite">
                                        <option value="0">Привязка к реквизитам (не выбрано)</option>
                                    </select>

                                    <input class="gpt_newcontragents_input" id="gpt_newcontragents_c_comment" placeholder="Комментарий (например: доставка/бухгалтерия/менеджер)" />

<label><input type="checkbox" id="gpt_newcontragents_c_default"> Для связи</label>
<label><input type="checkbox" id="gpt_newcontragents_c_notify"> Для оповещений</label>
<label><input type="checkbox" id="gpt_newcontragents_c_invoice"> Для счетов</label>

                                    <div class="gpt_newcontragents_actions">
                                        <div class="gpt_newcontragents_mini_btn gpt_newcontragents_primary_btn" id="gpt_newcontragents_contactSaveBtn">Сохранить</div>
                                        <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_contactCancelBtn">Отмена</div>
                                    </div>

                                    <div class="gpt_newcontragents_okhint" id="gpt_newcontragents_contactPopupHint"></div>
                                </div>
                            </div>

                            <div class="gpt_newcontragents_popupSide">
                                <div class="gpt_newcontragents_oldBlock" id="gpt_newcontragents_oldData_contact">
                                  <div class="gpt_newcontragents_oldBlockTitle">Старые данные</div>

                                  <div class="gpt_newcontragents_oldBlockItem">
                                    <div class="gpt_newcontragents_oldBlockLabel">Реквизиты (fullinfo)</div>
                                    <div class="gpt_newcontragents_oldBlockValue" data-old-field="fullinfo"></div>
                                  </div>

                                  <div class="gpt_newcontragents_oldBlockItem">
                                    <div class="gpt_newcontragents_oldBlockLabel">Адрес</div>
                                    <div class="gpt_newcontragents_oldBlockValue" data-old-field="address"></div>
                                  </div>

                                  <div class="gpt_newcontragents_oldBlockItem">
                                    <div class="gpt_newcontragents_oldBlockLabel">Телефон для оповещений</div>
                                    <div class="gpt_newcontragents_oldBlockValue" data-old-field="notification_number"></div>
                                  </div>

                                  <div class="gpt_newcontragents_oldBlockItem">
  <div class="gpt_newcontragents_oldBlockLabel">Контакты (contacts)</div>
  <div class="gpt_newcontragents_oldBlockValue" data-old-field="contacts">—</div>
</div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- POPUP: REQUISITE -->
            <div class="gpt_newcontragents_subOverlay gpt_newcontragents_hidden" id="gpt_newcontragents_requisitePopupOverlay">
                <div class="gpt_newcontragents_subModal">
                    <div class="gpt_newcontragents_subHeader">
                        <div class="gpt_newcontragents_subTitle" id="gpt_newcontragents_requisitePopupTitle">Реквизиты</div>
                        <div class="gpt_newcontragents_closeMini" id="gpt_newcontragents_requisitePopupCloseBtn">✕</div>
                    </div>

                    <div class="gpt_newcontragents_subBody">
                        <div class="gpt_newcontragents_popupGrid">
                            <div class="gpt_newcontragents_popupMain">
                                <input type="hidden" id="gpt_newcontragents_r_id" value="0" />

                                <div class="gpt_newcontragents_row">
                                  <input class="gpt_newcontragents_input" id="gpt_newcontragents_r_legal"
                                         placeholder="Название юрлица / ИП (обязательно)" />
                                  
                                  <input type="hidden" id="gpt_newcontragents_r_dadata_json" value="" />
                                  <div class="gpt_newcontragents_innWrap">
                                    <input class="gpt_newcontragents_input" id="gpt_newcontragents_r_inn" placeholder="ИНН" />
                                    <div class="gpt_newcontragents_mini_btn gpt_newcontragents_dadata_btn gpt_newcontragents_hidden" id="gpt_newcontragents_dadataBtn">
                                      Запросить данные
                                    </div>
                                  </div>

                                  <input class="gpt_newcontragents_input" id="gpt_newcontragents_r_contract_no" placeholder="№ договора" />

                                  <textarea class="gpt_newcontragents_textarea" id="gpt_newcontragents_r_jur_address" placeholder="Юридический адрес"></textarea>
                                  <input class="gpt_newcontragents_input" id="gpt_newcontragents_r_rs" placeholder="Р/С" />
                                  <input class="gpt_newcontragents_input" id="gpt_newcontragents_r_bank_name" placeholder="Банк" />
                                  <input class="gpt_newcontragents_input" id="gpt_newcontragents_r_bank_bik" placeholder="БИК" />
                                  <input class="gpt_newcontragents_input" id="gpt_newcontragents_r_director_fio" placeholder="Директор" />
                                  <input class="gpt_newcontragents_input" id="gpt_newcontragents_r_basis" placeholder="Основание (Устав / Договор / Доверенность)" />
                                  <select id="gpt_newcontragents_r_tax_mode" class="gpt_newcontragents_input">
                                    <option value="">— Режим налогообложения —</option>
                                    <option value="АУСН">АУСН УСН УСН+НДС</option>
                                    <option value="НДС 22">НДС 22</option>
                                  </select>
                                  <textarea class="gpt_newcontragents_textarea" id="gpt_newcontragents_r_details" placeholder="Примечание"></textarea>

                                  <label>
                                    <input type="checkbox" id="gpt_newcontragents_r_default" />
                                    <span>Реквизиты по умолчанию</span>
                                  </label>

                                  <div class="gpt_newcontragents_actions">
                                    <div class="gpt_newcontragents_mini_btn gpt_newcontragents_primary_btn" id="gpt_newcontragents_requisiteSaveBtn">Сохранить</div>
                                    <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_requisiteCancelBtn">Отмена</div>
                                  </div>

                                  <div class="gpt_newcontragents_okhint" id="gpt_newcontragents_requisitePopupHint"></div>
                                </div>
                            </div>

                            <div class="gpt_newcontragents_popupSide">
                                <div class="gpt_newcontragents_oldBlock" id="gpt_newcontragents_oldData_requisite">
                                  <div class="gpt_newcontragents_oldBlockTitle">Старые данные</div>

                                  <div class="gpt_newcontragents_oldBlockItem">
                                    <div class="gpt_newcontragents_oldBlockLabel">Реквизиты (fullinfo)</div>
                                    <div class="gpt_newcontragents_oldBlockValue" data-old-field="fullinfo"></div>
                                  </div>

                                  <div class="gpt_newcontragents_oldBlockItem">
                                    <div class="gpt_newcontragents_oldBlockLabel">Адрес</div>
                                    <div class="gpt_newcontragents_oldBlockValue" data-old-field="address"></div>
                                  </div>

                                  <div class="gpt_newcontragents_oldBlockItem">
                                    <div class="gpt_newcontragents_oldBlockLabel">Телефон для оповещений</div>
                                    <div class="gpt_newcontragents_oldBlockValue" data-old-field="notification_number"></div>
                                  </div>
<div class="gpt_newcontragents_oldBlockItem">
  <div class="gpt_newcontragents_oldBlockLabel">Контакты (contacts)</div>
  <div class="gpt_newcontragents_oldBlockValue" data-old-field="contacts"></div>
</div>                                  

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


                        <!-- POPUP: CONTRAGENT -->
            <div class="gpt_newcontragents_subOverlay gpt_newcontragents_hidden" id="gpt_newcontragents_contragentPopupOverlay">
                <div class="gpt_newcontragents_subModal">
                    <div class="gpt_newcontragents_subHeader">
                        <div class="gpt_newcontragents_subTitle" id="gpt_newcontragents_contragentPopupTitle">Контрагент</div>
                        <div class="gpt_newcontragents_closeMini" id="gpt_newcontragents_contragentPopupCloseBtn">✕</div>
                    </div>
            
                    <div class="gpt_newcontragents_subBody">
                        <input type="hidden" id="gpt_newcontragents_contragent_id" value="0" />
            
                        <div class="gpt_newcontragents_row">
                            <input class="gpt_newcontragents_input" id="gpt_newcontragents_contragent_name" placeholder="Название (обязательно)" />

                            <input class="gpt_newcontragents_input"
                                   id="gpt_newcontragents_contragent_keywords"
                                   placeholder="Ключевые слова (через пробел)"
                                   autocomplete="off" />
                            
                            <textarea class="gpt_newcontragents_textarea"
                                      id="gpt_newcontragents_contragent_note"
                                      placeholder="Примечание (внутренний комментарий)"></textarea>

                            <div class="gpt_newcontragents_actions">
                                <div class="gpt_newcontragents_mini_btn gpt_newcontragents_primary_btn" id="gpt_newcontragents_contragentSaveBtn">Сохранить</div>
                                <div class="gpt_newcontragents_mini_btn" id="gpt_newcontragents_contragentCancelBtn">Отмена</div>
                            </div>
            
                            <div class="gpt_newcontragents_okhint" id="gpt_newcontragents_contragentPopupHint"></div>
                        </div>
                    </div>
                </div>
            </div>




        </div><!-- modal -->
    </div><!-- overlay -->

</div>

<style>
  .phone_ok  { border-color:#20b26b !important; box-shadow:0 0 0 2px rgba(32,178,107,.15) !important; }
  .phone_bad { border-color:#e04b4b !important; box-shadow:0 0 0 2px rgba(224,75,75,.12) !important; }
</style>

<script>
(function(){
  var inp = document.getElementById('gpt_newcontragents_c_phone');
  if (!inp) return;

  // защита от двойной инициализации
  if (inp._phoneInited) return;
  inp._phoneInited = true;

  inp.setAttribute('inputmode','tel');
  inp.setAttribute('autocomplete','tel');

  inp.addEventListener('input', formatNow);
  inp.addEventListener('blur',  formatNow);

  function digitsOnly(s){ return (s||'').replace(/\D/g,''); }

  function normalize11(d){
    if (!d) return '';

    // ограничим до 11
    if (d.length > 11) d = d.substr(0,11);

    // если первая цифра 7 или 8 -> всегда 7
    if (d.length >= 1 && (d.charAt(0)==='7' || d.charAt(0)==='8')) {
      d = '7' + d.substr(1);
    }

    // если ввели 10 цифр (без кода страны) — можно НЕ трогать
    // (ты это не просил). Если захочешь: если d.length===10 -> d='7'+d;

    return d;
  }

  function formatRu(d){
    // d: "7..." (может быть короче)
    if (!d) return '';
    var rest = d.charAt(0)==='7' ? d.substr(1) : d; // после 7
    var out = '+7';

    if (rest.length === 0) return out;

    out += '(' + rest.substr(0,3);
    if (rest.length < 3) return out;

    out += ')-' + rest.substr(3,3);
    if (rest.length < 6) return out;

    out += '-' + rest.substr(6,2);
    if (rest.length < 8) return out;

    out += '-' + rest.substr(8,2);
    return out;
  }

  function setState(isFull){
    inp.classList.toggle('phone_ok',  !!isFull);
    inp.classList.toggle('phone_bad', !isFull && inp.value.length>0);
  }

  function moveCaretEnd(){
    try{
      var p = inp.value.length;
      inp.setSelectionRange(p,p);
    }catch(e){}
  }

  function formatNow(){
    var d = digitsOnly(inp.value);
    if (!d){
      inp.value = '';
      setState(false);
      return;
    }

    d = normalize11(d);
    inp.value = formatRu(d);
    moveCaretEnd();

    setState(d.length === 11);
  }

  // если поле уже заполнено при загрузке
  if (inp.value) formatNow();
})();
</script>

