{{-- Giriş/qeydiyyat modalı (Journal iframe popup-ı daxilində). $tab: login | register --}}
@php
    // Qeydiyyat formasından qayıdan xətalar/köhnə dəyərlər varsa qeydiyyat tabı açıq qalsın
    $registerFields = ['first_name', 'last_name', 'phone', 'password'];
    if (old('first_name') !== null || collect($registerFields)->contains(fn ($f) => $errors->has($f))) {
        $tab = 'register';
    }
@endphp

@push('styles')
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-ui.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-ui.css')) }}" rel="stylesheet"/>
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-checkout.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-checkout.css')) }}" rel="stylesheet"/>
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-search.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-search.css')) }}" rel="stylesheet"/>
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-auth.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-auth.css')) }}" rel="stylesheet"/>
@endpush

<div class="bz-auth-modal">
  <div class="bz-auth-modal__head">
    <div class="bz-auth-modal__title">{{ __('Xoş gəlmisiniz!') }}</div>
    <p>{{ __('Sifarişlərinizi izləmək və arzu siyahınızı saxlamaq üçün daxil olun.') }}</p>
  </div>

  <div class="bz-tabs" role="tablist">
    <button type="button" role="tab" class="bz-tabs__tab {{ $tab === 'login' ? 'is-active' : '' }}" data-auth-tab="login" aria-selected="{{ $tab === 'login' ? 'true' : 'false' }}">{{ __('Daxil ol') }}</button>
    <button type="button" role="tab" class="bz-tabs__tab {{ $tab === 'register' ? 'is-active' : '' }}" data-auth-tab="register" aria-selected="{{ $tab === 'register' ? 'true' : 'false' }}">{{ __('Qeydiyyat') }}</button>
  </div>

  <div data-auth-panel="login" role="tabpanel" @if($tab !== 'login') hidden @endif>
    @include('front.auth._login-form', ['popup' => true, 'showErrors' => $tab === 'login'])
  </div>
  <div data-auth-panel="register" role="tabpanel" @if($tab !== 'register') hidden @endif>
    @include('front.auth._register-form', ['popup' => true, 'showErrors' => $tab === 'register'])
  </div>
</div>

@push('scripts')
@include('front.auth._scripts')
@endpush
