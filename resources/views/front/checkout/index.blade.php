@extends('front.layouts.app')

@section('title', __('Sifarişin rəsmiləşdirilməsi'))

@php
    $field = function (string $name, string $label, bool $required, ?string $default, string $type = 'text', string $placeholder = '', string $autocomplete = '') use ($errors) {
        return compact('name', 'label', 'required', 'default', 'type', 'placeholder', 'autocomplete') + ['error' => $errors->first($name)];
    };
    $contactFields = [
        $field('first_name', __('Ad'), true, $user?->first_name, 'text', __('Adınız'), 'given-name'),
        $field('last_name', __('Soyad'), false, $user?->last_name, 'text', __('Soyadınız'), 'family-name'),
        $field('phone', __('Telefon'), true, $user?->phone, 'tel', '+994 50 000 00 00', 'tel'),
        $field('email', __('E-mail'), false, $user?->email, 'email', 'email@example.com', 'email'),
    ];
@endphp

@section('content')
@include('front.partials.breadcrumbs', ['items' => [
    ['title' => __('Səbət'), 'url' => lroute('front.cart')],
    ['title' => __('Sifariş rəsmiləşdirmə'), 'url' => lroute('front.checkout')],
]])
<h1 class="title page-title"><span>{{ __('Sifarişin rəsmiləşdirilməsi') }}</span></h1>
<div class="container">
  <div class="row">
    <div id="content" class="col-sm-12">
      <ol class="bz-steps" aria-label="{{ __('Sifariş addımları') }}">
        <li class="is-done"><a href="{{ lroute('front.cart') }}"><span>1</span>{{ __('Səbət') }}</a></li>
        <li class="is-current" aria-current="step"><span>2</span>{{ __('Rəsmiləşdirmə') }}</li>
        <li><span>3</span>{{ __('Təsdiq') }}</li>
      </ol>

      @if($errors->any())
        <div class="bz-alert bz-alert--error" role="alert">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M12 7.5v5.5M12 16.5h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          {{ __('Zəhmət olmasa qırmızı ilə qeyd olunmuş xanaları düzgün doldurun.') }}
        </div>
      @endif

      <form method="post" action="{{ lroute('front.checkout.store') }}" id="checkout-form" class="bz-checkout" novalidate>
        @csrf
        <div class="bz-checkout__main">
          @if(! $user)
            <div class="bz-co-login">
              <div>
                <b>{{ __('Hesabınız var?') }}</b>
                <span>{{ __('Daxil olun və ya qonaq kimi davam edin.') }}</span>
              </div>
              <div class="bz-co-login__actions">
                <a href="javascript:open_login_popup()" class="bz-btn bz-btn--outline">{{ __('Daxil ol') }}</a>
                @if(setting('social.google_client_id'))
                  <a href="{{ route('social.redirect', 'google') }}" class="bz-btn bz-btn--outline"><i class="fa fa-google"></i> Google</a>
                @endif
                @if(setting('social.facebook_client_id'))
                  <a href="{{ route('social.redirect', 'facebook') }}" class="bz-btn bz-btn--outline"><i class="fa fa-facebook"></i> Facebook</a>
                @endif
              </div>
            </div>
          @endif

          <section class="bz-co-card">
            <h2 class="bz-co-card__title"><span class="bz-co-card__num">1</span>{{ __('Əlaqə məlumatları') }}</h2>
            <div class="bz-co-grid">
              @foreach($contactFields as $f)
                <div class="bz-field {{ $f['error'] ? 'has-error' : '' }}">
                  <label for="input-{{ $f['name'] }}">{{ $f['label'] }}@if($f['required'])<span class="bz-req" aria-hidden="true">*</span>@endif</label>
                  <input type="{{ $f['type'] }}" name="{{ $f['name'] }}" id="input-{{ $f['name'] }}" value="{{ old($f['name'], $f['default']) }}"
                         placeholder="{{ $f['placeholder'] }}" autocomplete="{{ $f['autocomplete'] }}" @if($f['required']) required aria-required="true" @endif
                         @if($f['error']) aria-invalid="true" aria-describedby="err-{{ $f['name'] }}" @endif/>
                  @if($f['error'])<span class="bz-field__error" id="err-{{ $f['name'] }}">{{ $f['error'] }}</span>@endif
                </div>
              @endforeach
            </div>
          </section>

          <section class="bz-co-card">
            <h2 class="bz-co-card__title"><span class="bz-co-card__num">2</span>{{ __('Çatdırılma ünvanı') }}</h2>
            @if($addresses->isNotEmpty())
              <div class="bz-field">
                <label for="input-saved-address">{{ __('Mövcud ünvanlarım') }}</label>
                <select id="input-saved-address">
                  <option value="">{{ __('Yeni ünvan') }}</option>
                  @foreach($addresses as $address)
                    <option value="{{ $address->id }}" data-city="{{ $address->city }}" data-address="{{ $address->address }}">{{ $address->full }}</option>
                  @endforeach
                </select>
              </div>
            @endif
            <div class="bz-co-grid bz-co-grid--address">
              <div class="bz-field {{ $errors->has('city') ? 'has-error' : '' }}">
                <label for="input-city">{{ __('Şəhər') }}</label>
                <input type="text" name="city" id="input-city" value="{{ old('city', setting_t('checkout.default_city', 'Bakı')) }}" placeholder="{{ __('Şəhər') }}" autocomplete="address-level2"/>
                @error('city')<span class="bz-field__error">{{ $message }}</span>@enderror
              </div>
              <div class="bz-field {{ $errors->has('address') ? 'has-error' : '' }}">
                <label for="input-address">{{ __('Ünvan') }}<span class="bz-req" aria-hidden="true">*</span></label>
                <input type="text" name="address" id="input-address" value="{{ old('address') }}" placeholder="{{ __('Küçə, ev, mənzil') }}" autocomplete="street-address" required aria-required="true"
                       @error('address') aria-invalid="true" aria-describedby="err-address" @enderror/>
                @error('address')<span class="bz-field__error" id="err-address">{{ $message }}</span>@enderror
              </div>
            </div>
            @if($user)
              <label class="bz-check bz-co-check"><input type="checkbox" name="save_address" value="1" checked/> <span class="links-text">{{ __('Bu ünvanı yadda saxla') }}</span></label>
            @endif
          </section>

          <section class="bz-co-card">
            <h2 class="bz-co-card__title"><span class="bz-co-card__num">3</span>{{ __('Çatdırılma və ödəniş') }}</h2>
            <div class="bz-co-label">{{ __('Çatdırılma üsulu') }}</div>
            <div class="bz-options">
              <label class="bz-option">
                <input type="radio" name="shipping_method" value="courier" checked/>
                <span class="bz-option__icon"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M3 6h11v9H3zM14 9h4l3 3v3h-7M7.5 18.5a1.5 1.5 0 1 0 0-.01M17.5 18.5a1.5 1.5 0 1 0 0-.01" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
                <span class="bz-option__text">
                  <b>{{ setting_t('delivery.title') ?: __('Kuryer ilə çatdırılma və quraşdırılma') }}</b>
                  <small>{{ __('Mebel ünvana çatdırılır və yerində quraşdırılır') }}</small>
                </span>
                <span class="bz-option__price {{ $deliveryFee > 0 ? '' : 'is-free' }}">{{ $deliveryFee > 0 ? money($deliveryFee) : __('Pulsuz') }}</span>
              </label>
            </div>

            <div class="bz-co-label">{{ __('Ödəniş üsulu') }}</div>
            <div class="bz-options {{ $errors->has('payment_method') ? 'has-error' : '' }}">
              @forelse($paymentMethods as $code => $title)
                <label class="bz-option">
                  <input type="radio" name="payment_method" value="{{ $code }}" @checked(old('payment_method', array_key_first($paymentMethods)) === $code)/>
                  <span class="bz-option__icon">
                    @if($code === 'kapital')
                      <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><rect x="3" y="5.5" width="18" height="13" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M3 10h18M7 15h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    @else
                      <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><rect x="2.5" y="6.5" width="19" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                    @endif
                  </span>
                  <span class="bz-option__text"><b>{{ $title }}</b></span>
                </label>
              @empty
                <div class="bz-alert bz-alert--warning">{{ __('Ödəniş üsulu mövcud deyil. Zəhmət olmasa əlaqə saxlayın!') }}</div>
              @endforelse
            </div>
            @error('payment_method')<span class="bz-field__error">{{ $message }}</span>@enderror
          </section>

          <section class="bz-co-card">
            <h2 class="bz-co-card__title"><span class="bz-co-card__num">4</span>{{ __('Əlavə qeyd') }} <small>{{ __('(istəyə bağlı)') }}</small></h2>
            <div class="bz-field">
              <label for="input-comment" class="sr-only">{{ __('Sifariş üçün əlavə qeyd') }}</label>
              <textarea name="comment" id="input-comment" rows="3" placeholder="{{ __('Məs.: çatdırılma üçün əlverişli vaxt, mərtəbə, lift və s.') }}">{{ old('comment') }}</textarea>
            </div>
          </section>
        </div>

        <aside class="bz-checkout__aside">
          <div class="bz-summary bz-co-summary">
            <div class="bz-summary__title">{{ __('Sifarişiniz') }} <a href="{{ lroute('front.cart') }}">{{ __('Redaktə et') }}</a></div>
            <div class="bz-co-items">
              @foreach($lines as $line)
                <div class="bz-co-item">
                  <span class="bz-co-item__img">
                    <img src="{{ thumb($line['product']->mainImage(), 64, 64) }}" srcset="{{ thumb($line['product']->mainImage(), 64, 64) }} 1x, {{ thumb($line['product']->mainImage(), 128, 128) }} 2x" width="52" height="52" alt=""/>
                    <span class="bz-co-item__qty">{{ $line['quantity'] }}</span>
                  </span>
                  <span class="bz-co-item__info">
                    <a href="{{ $line['product']->url() }}">{{ $line['product']->name }}</a>
                    @foreach($line['options'] as $option)
                      <small>{{ tr($option['name']) }}: {{ tr($option['value']) }}</small>
                    @endforeach
                    @if($line['components'])
                      <small>{{ collect($line['components'])->map(fn ($c) => $c['product']->name.($c['qty'] > 1 ? ' ×'.$c['qty'] : ''))->implode(', ') }}</small>
                    @endif
                  </span>
                  <span class="bz-co-item__price">{{ money($line['total']) }}</span>
                </div>
              @endforeach
            </div>
            <div class="bz-summary__row"><span>{{ __('Məbləğ') }}</span><span>{{ money($subtotal) }}</span></div>
            <div class="bz-summary__row"><span>{{ __('Çatdırılma') }}</span><span class="{{ $deliveryFee > 0 ? '' : 'bz-text-green' }}">{{ $deliveryFee > 0 ? money($deliveryFee) : __('Pulsuz') }}</span></div>
            <div class="bz-summary__row bz-summary__row--total"><span>{{ __('Ümumi məbləğ') }}</span><span>{{ money($total) }}</span></div>

            @if($termsPage)
              <label class="bz-check bz-co-terms {{ $errors->has('agree') ? 'has-error' : '' }}">
                <input type="checkbox" name="agree" value="1" @checked(old('agree'))/>
                <span class="links-text">{!! __('Mən <a href=":url" target="_blank"><b>:title</b></a> ilə tanış oldum və razıyam', ['url' => $termsPage->url(), 'title' => e($termsPage->title)]) !!}</span>
              </label>
              @error('agree')<span class="bz-field__error">{{ $message }}</span>@enderror
            @endif

            <button type="submit" class="bz-btn bz-btn--primary bz-btn--lg bz-btn--block" id="quick-checkout-button-confirm" data-loading-text="{{ __('Yüklənir...') }}">
              <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><rect x="5" y="10.5" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
              <span>{{ __('Sifarişi təsdiqlə') }}</span>
            </button>
            <ul class="bz-co-trust">
              <li>{{ __('Sifarişdən sonra operatorumuz sizinlə əlaqə saxlayacaq') }}</li>
              <li>{{ __('Məlumatlarınız yalnız çatdırılma üçün istifadə olunur') }}</li>
            </ul>
          </div>
        </aside>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $('#input-saved-address').on('change', function () {
    var $o = $(this).find(':selected');
    if ($o.val()) {
      $('#input-city').val($o.data('city'));
      $('#input-address').val($o.data('address'));
    }
  });
  $('#checkout-form').on('submit', function () {
    $('#quick-checkout-button-confirm').prop('disabled', true).addClass('is-loading');
  });
  // Xətalı xanada yazmağa başlayanda qırmızı vurğunu götür
  $('#checkout-form').on('input change', '.bz-field.has-error input, .bz-field.has-error textarea', function () {
    $(this).removeAttr('aria-invalid').closest('.bz-field').removeClass('has-error').find('.bz-field__error').remove();
  });
  // İlk xətalı xanaya fokus
  var $firstError = $('#checkout-form .bz-field.has-error input').first();
  if ($firstError.length) {
    $firstError.trigger('focus');
  }
</script>
@endpush
