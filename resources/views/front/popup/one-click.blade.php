@extends('front.layouts.popup', ['popupClass' => 'popup-module module-popup-22', 'journalPopup' => ['modulePopupId' => 22]])

@push('styles')
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-ui.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-ui.css')) }}" rel="stylesheet"/>
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-checkout.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-checkout.css')) }}" rel="stylesheet"/>
@endpush

@section('content')
<div class="bz-oneclick">
  <div class="bz-oneclick__head">
    <span class="bz-oneclick__badge" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 4 14h7l-1 8 9-12h-7z"/></svg>
    </span>
    <div class="bz-oneclick__title">{{ __('Bir kliklə al') }}</div>
    <p>{{ __('Adınızı və telefon nömrənizi yazın — operatorumuz sifarişi təsdiqləmək üçün sizinlə əlaqə saxlayacaq.') }}</p>
  </div>

  <div class="bz-oneclick__product" data-oc-product hidden>
    <div class="bz-oneclick__thumb"><img src="" alt="" data-oc-image hidden/></div>
    <div class="bz-oneclick__info">
      <div class="bz-oneclick__name" data-oc-name></div>
      <div class="bz-oneclick__price">
        <span class="bz-price" data-oc-price></span>
        <span class="bz-price-old" data-oc-old-price></span>
      </div>
    </div>
  </div>

  <form action="{{ lroute('front.form.send', ['type' => 'one_click']) }}" method="post" class="bz-oneclick__form" data-oc-form novalidate>
    @csrf
    <input type="hidden" name="product_id" value=""/>

    <div class="bz-field">
      <label for="oc-name">{{ __('Ad') }}<span class="bz-req">*</span></label>
      <input type="text" name="name" id="oc-name" value="{{ auth('web')->user()?->name }}" placeholder="{{ __('Adınız') }}" autocomplete="name" required/>
    </div>
    <div class="bz-field">
      <label for="oc-phone">{{ __('Telefon nömrəsi') }}<span class="bz-req">*</span></label>
      <input type="tel" name="phone" id="oc-phone" value="{{ auth('web')->user()?->phone }}" placeholder="+994 __ ___ __ __" autocomplete="tel" inputmode="tel" required/>
    </div>
    <div class="bz-field">
      <label for="oc-message">{{ __('Qeyd') }} <span class="bz-oneclick__opt">({{ __('istəyə bağlı') }})</span></label>
      <textarea name="message" id="oc-message" rows="3" placeholder="{{ __('Rəng, ölçü və ya sualınız') }}"></textarea>
    </div>

    <button type="submit" class="bz-btn bz-btn--primary bz-btn--lg bz-btn--block">{{ __('Sifarişi təsdiqlə') }}</button>
    <p class="bz-oneclick__note">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
      {{ __('Ödəniş məhsulu təhvil alanda edilir.') }}
    </p>
  </form>

  <div class="bz-oneclick__done" data-oc-done hidden>
    <span class="bz-oneclick__check" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
    </span>
    <div class="bz-oneclick__title">{{ __('Təşəkkür edirik!') }}</div>
    <p data-oc-done-text></p>
    <button type="button" class="bz-btn bz-btn--outline bz-btn--lg bz-btn--block" data-oc-close>{{ __('Bağla') }}</button>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    var form = document.querySelector('[data-oc-form]');
    var done = document.querySelector('[data-oc-done]');

    function fitParent() {
      if (window.parent === window) { return; }
      try {
        var inner = window.parent.document.querySelector('.popup-wrapper .popup-inner-body');
        var frame = inner && inner.querySelector('iframe');
        if (!inner || !frame) { return; }
        var h = document.querySelector('.site-wrapper').offsetHeight;
        inner.style.height = h + 'px';
        frame.style.height = h + 'px';
        frame.height = h;
      } catch (err) { /* başqa origin — keç */ }
    }

    function closePopup() {
      try {
        parent.window.__popup_url = undefined;
        parent.$('.module-popup-' + Journal['modulePopupId'] + ' .popup-close').trigger('click');
      } catch (err) {}
    }

    // Məhsul məlumatı valideyn səhifədən gəlir (bizimoda-shop.js → window.__popup_product)
    try {
      var p = parent.window.__popup_product || {};
      form.querySelector('[name="product_id"]').value = p.id || '';
      if (p.name) {
        document.querySelector('[data-oc-name]').textContent = p.name;
        document.querySelector('[data-oc-price]').textContent = p.price || '';
        document.querySelector('[data-oc-old-price]').textContent = p.oldPrice || '';
        if (p.image) {
          var img = document.querySelector('[data-oc-image]');
          img.src = p.image;
          img.alt = p.name;
          img.hidden = false;
        }
        document.querySelector('[data-oc-product]').hidden = false;
      }
    } catch (err) {}

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('[type="submit"]');
      form.querySelectorAll('.has-error').forEach(function (el) { el.classList.remove('has-error'); });
      form.querySelectorAll('.bz-field__error').forEach(function (el) { el.remove(); });

      var data = $(form).serializeArray();
      data.push({ name: 'url', value: parent.window.__popup_url || parent.window.location.toString() });

      btn.disabled = true;
      btn.classList.add('is-loading');

      $.ajax({ url: form.action, type: 'post', data: data, dataType: 'json' })
        .done(function (response) {
          if (response.status === 'success') {
            form.reset();
            document.querySelector('[data-oc-done-text]').textContent = response.response.message;
            form.hidden = true;
            document.querySelector('.bz-oneclick__head').hidden = true;
            document.querySelector('[data-oc-product]').hidden = true;
            done.hidden = false;
          } else if (response.response && response.response.errors) {
            $.each(response.response.errors, function (field, error) {
              var input = form.querySelector('[name="' + field + '"]');
              var box = input ? input.closest('.bz-field') : form.querySelector('.bz-field');
              box.classList.add('has-error');
              var msg = document.createElement('span');
              msg.className = 'bz-field__error';
              msg.textContent = error;
              box.appendChild(msg);
            });
          }
        })
        .fail(function () {
          alert(@json(__('Xəta baş verdi. Zəhmət olmasa yenidən cəhd edin.')));
        })
        .always(function () {
          btn.disabled = false;
          btn.classList.remove('is-loading');
          fitParent();
        });
    });

    document.querySelector('[data-oc-close]').addEventListener('click', closePopup);

    fitParent();
    window.addEventListener('load', fitParent);
    document.addEventListener('input', function (e) { if (e.target.tagName === 'TEXTAREA') { fitParent(); } });
  })();
</script>
@endpush
