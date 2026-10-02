<script>
(function () {
  'use strict';

  // Şifrəni göstər/gizlət
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-pass-toggle');
    if (!btn) { return; }
    var input = btn.parentNode.querySelector('input');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.classList.toggle('is-visible', show);
    btn.setAttribute('aria-label', show ? btn.dataset.labelHide : btn.dataset.labelShow);
  });

  // Göndərilərkən düyməni blokla (təkrar göndərmənin qarşısı)
  document.addEventListener('submit', function (e) {
    if (!e.target.classList.contains('js-auth-form')) { return; }
    var btn = e.target.querySelector('[type="submit"]');
    if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }
  });

  // Modal (iframe): valideyn popup-ın hündürlüyünü məzmuna uyğunlaşdır
  function fitParent() {
    if (window.parent === window) { return; }
    try {
      var inner = window.parent.document.querySelector('.popup-wrapper .popup-inner-body');
      var frame = inner && inner.querySelector('iframe');
      if (!inner || !frame) { return; }
      var h = document.documentElement.scrollHeight;
      inner.style.height = h + 'px';
      frame.style.height = h + 'px';
      frame.height = h;
    } catch (err) { /* başqa origin — keç */ }
  }

  // Tablar (modal): Giriş / Qeydiyyat
  document.addEventListener('click', function (e) {
    var tab = e.target.closest('[data-auth-tab]');
    if (!tab) { return; }
    e.preventDefault();
    var name = tab.getAttribute('data-auth-tab');
    document.querySelectorAll('[data-auth-tab]').forEach(function (t) {
      var on = t.getAttribute('data-auth-tab') === name;
      t.classList.toggle('is-active', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    document.querySelectorAll('[data-auth-panel]').forEach(function (p) {
      p.hidden = p.getAttribute('data-auth-panel') !== name;
    });
    var first = document.querySelector('[data-auth-panel="' + name + '"] input:not([type=hidden])');
    if (first) { first.focus({ preventScroll: true }); }
    fitParent();
  });

  window.addEventListener('load', fitParent);
  window.addEventListener('resize', fitParent);
})();
</script>
