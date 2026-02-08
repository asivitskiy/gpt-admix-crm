(function(){
  function q(sel, root){ return (root||document).querySelector(sel); }
  function qa(sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }

  var scope = q('.gptml');
  if (!scope) return;

  // meta
  var metaNum = q('#gptml_metaNum', scope);
  if (metaNum){
    var rows = qa('.gptml_rowWrap', scope);
    metaNum.textContent = String(rows.length);
  }

  // accordion
  scope.addEventListener('click', function(ev){
    var t = ev.target;
    // do not toggle when clicking interactive elements inside details
    if (t && (t.closest && t.closest('.gptml_detailsInner'))){
      var btn = t.closest('button, a, input, select, textarea');
      if (btn){
        return;
      }
    }

    var row = t && t.closest ? t.closest('.gptml_row') : null;
    if (!row) return;
    var wrap = row.parentNode;
    if (!wrap || !wrap.classList || !wrap.classList.contains('gptml_rowWrap')) return;

    // close others (optional)
    var opened = qa('.gptml_rowWrap.open', scope);
    opened.forEach(function(w){ if (w !== wrap) w.classList.remove('open'); });

    wrap.classList.toggle('open');
  });

})();
