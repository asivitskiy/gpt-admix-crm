(function(){
  function qsa(el, sel){ return Array.prototype.slice.call(el.querySelectorAll(sel)); }
  // ВАЖНО: textarea должны вести себя стандартно (скролл + resize мышью).
  // Поэтому автоподгон высоты НЕ используем.

  function renumber(container){
    var rows = qsa(container, '.onWorkRow');
    rows.forEach(function(row, i){
      row.dataset.pos = String(i+1);
      var h = row.querySelector('.onWorkSort');
      if(h) h.value = String(i+1);
    });
  }

  function makeRowId(){
    return 'tmp_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2,7);
  }

  function init(container){
    if(!container) return;

    // delete confirm modal
    var delModal = document.getElementById('onWorkDelModal');
    var delTargetRow = null;

    function closeDelModal(){
      if(!delModal) return;
      delModal.setAttribute('hidden','hidden');
      delTargetRow = null;
    }

    function openDelModal(row){
      if(!delModal) return;
      delTargetRow = row;
      delModal.removeAttribute('hidden');
    }

    if(delModal){
      // click outside box
      delModal.addEventListener('click', function(e){
        if(e.target === delModal) closeDelModal();
      });
      // buttons
      delModal.addEventListener('click', function(e){
        var a = e.target && e.target.getAttribute && e.target.getAttribute('data-action');
        if(!a) return;
        if(a === 'del_cancel'){
          closeDelModal();
          return;
        }
        if(a === 'del_confirm'){
          if(delTargetRow && delTargetRow.parentNode){
            delTargetRow.parentNode.removeChild(delTargetRow);
            renumber(container);
          }
          closeDelModal();
        }
      });
      // escape
      document.addEventListener('keydown', function(e){
        if(e.key === 'Escape') closeDelModal();
      });
    }

    // textarea оставляем стандартной (без автоподгона высоты)

    // actions
    container.addEventListener('click', function(e){
      var btn = e.target.closest && e.target.closest('[data-action]');
      if(!btn) return;

      var action = btn.getAttribute('data-action');
      var row = btn.closest('.onWorkRow');
      if(!row) return;

      if(action === 'dup'){
        var clone = row.cloneNode(true);

        // reset any transient states
        clone.classList.remove('is-dragging');

        // ensure new row-id
        clone.setAttribute('data-row-id', makeRowId());

        // insert after current row
        row.parentNode.insertBefore(clone, row.nextSibling);
        renumber(container);
        return;
      }

      if(action === 'gear'){
        // пока заглушка (позже сделаем меню)
        // eslint-disable-next-line no-alert
        // alert('Настройки (позже)');
        return;
      }

      if(action === 'del'){
        openDelModal(row);
        return;
      }
    });

    // ===== Sortable (Variant B) =====
    var drag = {
      active:false,
      row:null,
      placeholder:null,
      offsetY:0,
      startX:0,
      startY:0,
      width:0,
      left:0
    };

    function onPointerMove(ev){
      if(!drag.active) return;

      // лёгкий автоскролл
      var edge = 60;
      if(ev.clientY < edge) window.scrollBy(0, -12);
      else if(ev.clientY > window.innerHeight - edge) window.scrollBy(0, 12);

      var top = ev.clientY - drag.offsetY;
      drag.row.style.top = top + 'px';
      drag.row.style.left = drag.left + 'px';
      drag.row.style.width = drag.width + 'px';

      // определить куда вставлять placeholder
      var rows = qsa(container, '.onWorkRow').filter(function(r){ return r !== drag.row; });
      var insertBefore = null;

      for(var i=0;i<rows.length;i++){
        var r = rows[i];
        var rect = r.getBoundingClientRect();
        var mid = rect.top + rect.height/2;
        if(ev.clientY < mid){
          insertBefore = r;
          break;
        }
      }

      if(insertBefore){
        if(drag.placeholder.nextSibling !== insertBefore){
          container.insertBefore(drag.placeholder, insertBefore);
        }
      }else{
        container.appendChild(drag.placeholder);
      }
      ev.preventDefault();
    }

    function endDrag(){
      if(!drag.active) return;

      drag.active = false;
      document.removeEventListener('pointermove', onPointerMove, {passive:false});
      document.removeEventListener('pointerup', onPointerUp, true);
      document.removeEventListener('pointercancel', onPointerUp, true);

      // вернуть строку на место
      drag.row.classList.remove('is-dragging');

      drag.row.style.top = '';
      drag.row.style.left = '';
      drag.row.style.width = '';
      drag.row.style.position = '';
      drag.row.style.zIndex = '';
      drag.row.style.boxShadow = '';
      drag.row.style.pointerEvents = '';

      container.insertBefore(drag.row, drag.placeholder);
      drag.placeholder.parentNode && drag.placeholder.parentNode.removeChild(drag.placeholder);

      renumber(container);

      drag.row = null;
      drag.placeholder = null;
    }

    function onPointerUp(ev){
      endDrag();
    }

    container.addEventListener('pointerdown', function(ev){
      var handle = ev.target.closest && ev.target.closest('.onWorkHandle');
      if(!handle) return;

      var row = handle.closest('.onWorkRow');
      if(!row) return;

      // start drag
      var rect = row.getBoundingClientRect();

      drag.active = true;
      drag.row = row;

      drag.offsetY = ev.clientY - rect.top;
      drag.left = rect.left;
      drag.width = rect.width;

      // placeholder
      var ph = document.createElement('div');
      ph.className = 'onWorkPlaceholder';
      ph.style.height = rect.height + 'px';
      drag.placeholder = ph;

      // insert placeholder after row
      container.insertBefore(ph, row.nextSibling);

      // floating row
      row.classList.add('is-dragging');
      row.style.position = 'fixed';
      row.style.left = rect.left + 'px';
      row.style.top = rect.top + 'px';
      row.style.width = rect.width + 'px';
      row.style.pointerEvents = 'none';

      // move listeners
      document.addEventListener('pointermove', onPointerMove, {passive:false});
      document.addEventListener('pointerup', onPointerUp, true);
      document.addEventListener('pointercancel', onPointerUp, true);

      // prevent text selection / scroll
      ev.preventDefault();
    });

    renumber(container);
  }

  document.addEventListener('DOMContentLoaded', function(){
    var container = document.getElementById('orderWorks');
    init(container);
  });
})();
