{{-- Hesab səhifələrinin ümumi çərçivəsi: solda brend paneli, sağda forma. $mode: login | register | forgot | reset --}}
@php
    $copy = [
        'login' => [__('Yenidən xoş gəlmisiniz!'), __('Hesaba daxil olun')],
        'register' => [__('Hesab yaradın'), __('Qeydiyyat')],
        'forgot' => [__('Şifrənizi unutmusunuz?'), __('Şifrənin bərpası')],
        'reset' => [__('Demək olar ki, hazırdır'), __('Yeni şifrə təyin edin')],
    ][$mode];
@endphp
<div class="bz-auth-page">
  <div class="bz-auth-card">
    <aside class="bz-auth-card__brand">
      <div class="bz-auth-card__brand-inner">
        <div class="bz-auth-card__logo">{{ setting('site.name', 'bizimOda') }}</div>
        <h2>{{ $copy[0] }}</h2>
        @if(in_array($mode, ['forgot', 'reset'], true))
          <p>{{ __('Narahat olmayın — bir neçə addımda hesabınıza yenidən daxil olacaqsınız.') }}</p>
          <ol class="bz-auth-steps">
            <li class="{{ $mode === 'forgot' ? 'is-current' : 'is-done' }}">{{ __('E-poçt ünvanınızı daxil edin') }}</li>
            <li class="{{ $mode === 'forgot' ? (session('reset_link_sent') ? 'is-current' : '') : 'is-done' }}">{{ __('Məktubdakı linkə keçin') }}</li>
            <li class="{{ $mode === 'reset' ? 'is-current' : '' }}">{{ __('Yeni şifrə təyin edin') }}</li>
          </ol>
        @else
          <p>{{ __('Mebel və ev tekstili — bir yerdə, sərfəli qiymətə.') }}</p>
          <ul class="bz-auth-perks">
            <li>{{ __('Sifarişlərinizi izləyin') }}</li>
            <li>{{ __('Ünvanlarınızı yadda saxlayın, sürətli sifariş verin') }}</li>
            <li>{{ __('Arzu siyahısı yaradın') }}</li>
            <li>{{ __('Kampaniyalardan ilk siz xəbər tutun') }}</li>
          </ul>
        @endif
      </div>
    </aside>

    <div class="bz-auth-card__body">
      @if($mode === 'forgot' && session('reset_link_sent'))
        {{-- Göndərildi vəziyyəti --}}
        <div class="bz-auth-done">
          <span class="bz-auth-done__icon"><svg viewBox="0 0 24 24" width="30" height="30" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 6.5l8.5 6.5 8.5-6.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
          <h1 class="bz-auth-card__title">{{ __('E-poçtunuzu yoxlayın') }}</h1>
          <p class="bz-auth-card__sub">{!! __('Əgər :email ünvanı ilə hesab mövcuddursa, şifrə bərpası linki göndərildi. Məktub gəlmirsə, «Spam» qovluğunu da yoxlayın.', ['email' => '<b>'.e(session('reset_link_sent')).'</b>']) !!}</p>
          <a href="{{ lroute('front.login') }}" class="bz-btn bz-btn--primary bz-btn--lg bz-btn--block">{{ __('Girişə qayıt') }}</a>
          <a href="{{ lroute('front.password.request') }}" class="bz-auth-link bz-auth-done__again">{{ __('Başqa e-poçt ilə yenidən göndər') }}</a>
        </div>
      @else
        <h1 class="bz-auth-card__title">{{ $copy[1] }}</h1>
        <p class="bz-auth-card__sub">
          @switch($mode)
            @case('login') {{ __('Hesabınız yoxdur?') }} <a href="{{ lroute('front.register') }}">{{ __('Qeydiyyatdan keçin') }}</a> @break
            @case('register') {{ __('Artıq hesabınız var?') }} <a href="{{ lroute('front.login') }}">{{ __('Daxil olun') }}</a> @break
            @case('forgot') {{ __('Hesabınızın e-poçt ünvanını daxil edin, şifrə bərpası linkini göndərək.') }} @break
            @case('reset') {{ __('Yeni şifrənizi daxil edin. Ən azı 6 simvol olmalıdır.') }} @break
          @endswitch
        </p>

        @include('front.auth._'.$mode.'-form')

        @if(in_array($mode, ['forgot', 'reset'], true))
          <a href="{{ lroute('front.login') }}" class="bz-auth-back">
            <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ __('Girişə qayıt') }}
          </a>
        @endif
      @endif
    </div>
  </div>
</div>

@push('scripts')
@include('front.auth._scripts')
@endpush
