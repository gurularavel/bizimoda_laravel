<form action="{{ lroute('front.password.update') }}" method="post" class="bz-auth-form js-auth-form" novalidate>
  @csrf
  <input type="hidden" name="token" value="{{ $token }}"/>
  <div class="bz-field {{ $errors->has('email') ? 'has-error' : '' }}">
    <label for="reset-email">{{ __('E-mail') }}<span class="bz-req" aria-hidden="true">*</span></label>
    <input type="email" name="email" id="reset-email" value="{{ old('email', $email) }}" autocomplete="email" required/>
    @error('email')<span class="bz-field__error">{{ $message }}</span>@enderror
  </div>
  <div class="bz-field {{ $errors->has('password') ? 'has-error' : '' }}">
    <label for="reset-password">{{ __('Yeni şifrə') }}<span class="bz-req" aria-hidden="true">*</span></label>
    <div class="bz-pass">
      <input type="password" name="password" id="reset-password" autocomplete="new-password" minlength="6" required autofocus/>
      @include('front.auth._pass-toggle')
    </div>
    @error('password')<span class="bz-field__error">{{ $message }}</span>@else<span class="bz-field__hint">{{ __('Ən azı 6 simvol') }}</span>@enderror
  </div>
  <div class="bz-field">
    <label for="reset-confirm">{{ __('Yeni şifrə (təkrar)') }}<span class="bz-req" aria-hidden="true">*</span></label>
    <div class="bz-pass">
      <input type="password" name="password_confirmation" id="reset-confirm" autocomplete="new-password" required/>
      @include('front.auth._pass-toggle')
    </div>
  </div>
  <button type="submit" class="bz-btn bz-btn--primary bz-btn--lg bz-btn--block">{{ __('Şifrəni yenilə') }}</button>
</form>
