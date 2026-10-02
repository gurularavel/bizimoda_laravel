<form action="{{ lroute('front.password.email') }}" method="post" class="bz-auth-form js-auth-form" novalidate>
  @csrf
  <div class="bz-field {{ $errors->has('email') ? 'has-error' : '' }}">
    <label for="forgot-email">{{ __('E-mail') }}<span class="bz-req" aria-hidden="true">*</span></label>
    <input type="email" name="email" id="forgot-email" value="{{ old('email') }}" placeholder="email@example.com" autocomplete="email" required autofocus/>
    @error('email')<span class="bz-field__error">{{ $message }}</span>@enderror
  </div>
  <button type="submit" class="bz-btn bz-btn--primary bz-btn--lg bz-btn--block">{{ __('Bərpa linkini göndər') }}</button>
</form>
