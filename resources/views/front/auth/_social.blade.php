@php
    $google = setting('social.google_client_id');
    $facebook = setting('social.facebook_client_id');
@endphp
@if($google || $facebook)
  <div class="bz-auth-social">
    <div class="bz-auth-divider"><span>{{ __('və ya') }}</span></div>
    <div class="bz-auth-social__btns">
      @if($google)
        <a href="{{ route('social.redirect', 'google') }}" target="_top" class="bz-btn bz-btn--outline">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#4285F4" d="M21.6 12.2c0-.7-.1-1.4-.2-2H12v3.8h5.4a4.6 4.6 0 0 1-2 3v2.5h3.2c1.9-1.7 3-4.3 3-7.3z"/><path fill="#34A853" d="M12 22c2.7 0 5-.9 6.6-2.4l-3.2-2.5c-.9.6-2 1-3.4 1-2.6 0-4.8-1.8-5.6-4.1H3.1v2.6A10 10 0 0 0 12 22z"/><path fill="#FBBC05" d="M6.4 14c-.2-.6-.3-1.3-.3-2s.1-1.4.3-2V7.4H3.1a10 10 0 0 0 0 9.2L6.4 14z"/><path fill="#EA4335" d="M12 5.9c1.5 0 2.8.5 3.8 1.5l2.9-2.9A10 10 0 0 0 3.1 7.4L6.4 10C7.2 7.7 9.4 5.9 12 5.9z"/></svg>
          Google
        </a>
      @endif
      @if($facebook)
        <a href="{{ route('social.redirect', 'facebook') }}" target="_top" class="bz-btn bz-btn--outline">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#1877F2" d="M24 12a12 12 0 1 0-13.9 11.9v-8.4h-3V12h3V9.4c0-3 1.8-4.7 4.5-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9V12h3.4l-.5 3.5h-2.9v8.4A12 12 0 0 0 24 12z"/></svg>
          Facebook
        </a>
      @endif
    </div>
  </div>
@endif
