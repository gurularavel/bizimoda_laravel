{{-- Qeydiyyat forması (səhifə və modal). Sahə adları AuthController::register validasiyası ilə eynidir. --}}
@php
    $showErrors = $showErrors ?? true;
    $err = fn (string $name) => $showErrors ? $errors->first($name) : null;
@endphp
<form action="{{ lroute('front.register') }}" method="post" class="bz-auth-form js-auth-form" novalidate>
  @csrf
  @if(! empty($popup))<input type="hidden" name="popup" value="1"/>@endif

  <div class="bz-auth-grid">
    @foreach([
        ['first_name', __('Ad'), 'text', true, 'given-name', ''],
        ['last_name', __('Soyad'), 'text', false, 'family-name', ''],
    ] as [$name, $label, $type, $required, $ac, $ph])
      <div class="bz-field {{ $err($name) ? 'has-error' : '' }}">
        <label for="reg-{{ $name }}">{{ $label }}@if($required)<span class="bz-req" aria-hidden="true">*</span>@endif</label>
        <input type="{{ $type }}" name="{{ $name }}" id="reg-{{ $name }}" value="{{ old($name) }}" autocomplete="{{ $ac }}" @if($required) required @endif/>
        @if($err($name))<span class="bz-field__error">{{ $err($name) }}</span>@endif
      </div>
    @endforeach
  </div>

  <div class="bz-field {{ $err('email') ? 'has-error' : '' }}">
    <label for="reg-email">{{ __('E-mail') }}<span class="bz-req" aria-hidden="true">*</span></label>
    <input type="email" name="email" id="reg-email" value="{{ old('first_name') !== null ? old('email') : '' }}" placeholder="email@example.com" autocomplete="email" required/>
    @if($err('email'))<span class="bz-field__error">{{ $err('email') }}</span>@endif
  </div>

  <div class="bz-field {{ $err('phone') ? 'has-error' : '' }}">
    <label for="reg-phone">{{ __('Telefon') }}<span class="bz-req" aria-hidden="true">*</span></label>
    <input type="tel" name="phone" id="reg-phone" value="{{ old('phone') }}" placeholder="+994 50 000 00 00" autocomplete="tel" required/>
    @if($err('phone'))<span class="bz-field__error">{{ $err('phone') }}</span>@endif
  </div>

  <div class="bz-auth-grid">
    <div class="bz-field {{ $err('password') ? 'has-error' : '' }}">
      <label for="reg-password">{{ __('Şifrə') }}<span class="bz-req" aria-hidden="true">*</span></label>
      <div class="bz-pass">
        <input type="password" name="password" id="reg-password" autocomplete="new-password" minlength="6" required/>
        @include('front.auth._pass-toggle')
      </div>
      @if($err('password'))<span class="bz-field__error">{{ $err('password') }}</span>@else<span class="bz-field__hint">{{ __('Ən azı 6 simvol') }}</span>@endif
    </div>
    <div class="bz-field">
      <label for="reg-confirm">{{ __('Şifrə (təkrar)') }}<span class="bz-req" aria-hidden="true">*</span></label>
      <div class="bz-pass">
        <input type="password" name="password_confirmation" id="reg-confirm" autocomplete="new-password" required/>
        @include('front.auth._pass-toggle')
      </div>
    </div>
  </div>

  <label class="bz-check bz-auth-check"><input type="checkbox" name="newsletter" value="1" @checked(old('newsletter'))/> <span class="links-text">{{ __('Kampaniya və yeniliklərdən xəbərdar olmaq istəyirəm') }}</span></label>

  <button type="submit" class="bz-btn bz-btn--primary bz-btn--lg bz-btn--block">{{ __('Qeydiyyatdan keç') }}</button>
</form>
@include('front.auth._social')
