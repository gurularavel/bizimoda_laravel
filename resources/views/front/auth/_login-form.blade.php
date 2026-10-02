{{-- Giriş forması (səhifə və modal). $showErrors=false olanda (modalda qeydiyyat tabı aktivdirsə) xətalar göstərilmir. --}}
@php $showErrors = $showErrors ?? true; @endphp
<form action="{{ lroute('front.login') }}" method="post" class="bz-auth-form login-form js-auth-form" @if(! empty($popup)) target="_self" @endif novalidate>
  @csrf
  @if(! empty($popup))<input type="hidden" name="popup" value="1"/>@endif

  @if($showErrors && $errors->has('email') && ! $errors->has('password'))
    <div class="bz-alert bz-alert--error" role="alert">{{ $errors->first('email') }}</div>
  @endif

  <div class="bz-field {{ $showErrors && $errors->has('email') ? 'has-error' : '' }}">
    <label for="login-email">{{ __('E-mail ünvanı') }}</label>
    <input type="email" name="email" id="login-email" value="{{ old('email') }}" placeholder="email@example.com" autocomplete="email" required/>
  </div>

  <div class="bz-field">
    <div class="bz-field__row">
      <label for="login-password">{{ __('Şifrə') }}</label>
      <a href="{{ lroute('front.password.request') }}" target="_top" class="bz-auth-link">{{ __('Şifrənizi unutmusunuz?') }}</a>
    </div>
    <div class="bz-pass">
      <input type="password" name="password" id="login-password" placeholder="••••••••" autocomplete="current-password" required/>
      @include('front.auth._pass-toggle')
    </div>
  </div>

  <button type="submit" class="bz-btn bz-btn--primary bz-btn--lg bz-btn--block">{{ __('Daxil ol') }}</button>
</form>
@include('front.auth._social')
