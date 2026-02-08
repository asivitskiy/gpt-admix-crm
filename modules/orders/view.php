<?php
// NOTE: Only layout/design. No business logic / DB queries here yet.
?>
<link rel="stylesheet" href="/assets/modules/orders_mainlist.css?v=1">

<div class="gptml">
  <div class="gptml_wrap">

    <div class="gptml_top">
      <div class="gptml_filters">
        <div class="gptml_filtersRow">
          <input class="gptml_in" type="text" placeholder="Поиск по строке (пока без логики)" disabled>

          <div class="gptml_inWrap">
            <input class="gptml_in" type="text" placeholder="Клиент (пока без логики)" disabled>
            <div class="gptml_suggest" style="display:none"></div>
          </div>

          <input class="gptml_in" type="text" placeholder="Диапазон дат (пока без логики)" disabled>
          <select class="gptml_sel" disabled>
            <option>Менеджер</option>
          </select>
          <select class="gptml_sel" disabled>
            <option>Статус</option>
          </select>
        </div>

        <div class="gptml_chipsBar">
          <div class="gptml_chipsBarLeft" id="gptml_statusChips">
            <div class="gptml_chip active" data-key="my">Мои</div>
            <div class="gptml_chip active" data-key="full">Весь список</div>
            <div class="gptml_chip" data-key="done">Завершённые</div>
            <div class="gptml_chip" data-key="del">Доставка</div>
            <div id="gptml_activeFilters" style="display:none"></div>
          </div>
          <div class="gptml_chipsBarRight" id="gptml_metaLine">
            <div class="gptml_meta">Показано: <span class="gptml_metaNum" id="gptml_metaNum">0</span></div>
          </div>
        </div>
      </div>
    </div>

    <div class="gptml_listOuter">
      <div class="gptml_listInner" id="gptml_list">

        <div class="gptml_daySep">07.02</div>

        <div class="gptml_rowWrap" data-id="10421">
          <div class="gptml_row">
            <div class="gptml_id"><span class="gptml_pref">A</span><span>10421</span></div>
            <div class="gptml_client" title="ООО Ромашка">ООО Ромашка</div>
            <div class="gptml_title" title="Печать наклеек, резка">Печать наклеек, резка</div>
            <div class="gptml_dates">07.02 → 08.02</div>
            <div class="gptml_badges">
              <span class="gptml_b ok">P</span>
              <span class="gptml_b warn">M</span>
              <span class="gptml_b neu">S</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
            </div>
            <div class="gptml_sum ok">12 450,00</div>
            <div class="gptml_flags">
              <span class="gptml_flag empty">--</span>
              <span class="gptml_flag empty">--</span>
            </div>
            <div class="gptml_chev"><span>▾</span></div>
          </div>
          <div class="gptml_details">
            <div class="gptml_detailsInner">
              <div class="od_wrap">
                <div class="od_head">
                  <div class="od_title">Заказ #10421</div>
                  <div class="od_meta">Дизайн перенесён без логики</div>
                </div>
                <div class="od_sections">
                  <details class="od_details" open>
                    <summary>Кратко</summary>
                    <div class="od_detailsBody">
                      <div class="od_kv">
                        <div class="od_k">Клиент</div><div class="od_v">ООО Ромашка</div>
                        <div class="od_k">Сумма</div><div class="od_v">12 450,00</div>
                        <div class="od_k">Срок</div><div class="od_v">07.02 → 08.02</div>
                      </div>
                    </div>
                  </details>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="gptml_rowWrap" data-id="10420">
          <div class="gptml_row">
            <div class="gptml_id"><span class="gptml_pref">B</span><span>10420</span></div>
            <div class="gptml_client" title="ИП Иванов">ИП Иванов</div>
            <div class="gptml_title" title="Баннер 3×1, люверсы">Баннер 3×1, люверсы</div>
            <div class="gptml_dates">07.02 →</div>
            <div class="gptml_badges">
              <span class="gptml_b ok">P</span>
              <span class="gptml_b ok">M</span>
              <span class="gptml_b ok">S</span>
              <span class="gptml_b ok">D</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
            </div>
            <div class="gptml_sum warn">8 900,00</div>
            <div class="gptml_flags">
              <span class="gptml_flag warn">ПЗК</span>
              <span class="gptml_flag empty">--</span>
            </div>
            <div class="gptml_chev"><span>▾</span></div>
          </div>
          <div class="gptml_details">
            <div class="gptml_detailsInner">
              <div class="od_wrap">Здесь позже будет подгрузка подробностей. Сейчас — только каркас.</div>
            </div>
          </div>
        </div>

        <div class="gptml_daySep">06.02</div>

        <div class="gptml_rowWrap" data-id="10419">
          <div class="gptml_row">
            <div class="gptml_id"><span class="gptml_pref">—</span><span>10419</span></div>
            <div class="gptml_client" title="ООО Вектор">ООО Вектор</div>
            <div class="gptml_title" title="Визитки 4+4, ламинация">Визитки 4+4, ламинация</div>
            <div class="gptml_dates">06.02 → 07.02</div>
            <div class="gptml_badges">
              <span class="gptml_b bad">P</span>
              <span class="gptml_b neu">M</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
              <span class="gptml_b empty">--</span>
            </div>
            <div class="gptml_sum bad">3 200,00</div>
            <div class="gptml_flags">
              <span class="gptml_flag empty">--</span>
              <span class="gptml_flag bad">ОШБ</span>
            </div>
            <div class="gptml_chev"><span>▾</span></div>
          </div>
          <div class="gptml_details">
            <div class="gptml_detailsInner">
              <div class="od_wrap">Карточка заказа (плейсхолдер)</div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

<script defer src="/assets/modules/orders_mainlist.js?v=1"></script>
