@php
    $siteName = setting('site.name', 'bizimoda');
    $logo = setting('site.logo') ? image_url(setting('site.logo')) : asset('images/logo.png');
    $about = setting_t('site.footer_about', '') ?: setting_t('site.meta_description', '');
    $whatsapp = preg_replace('/\D+/', '', (string) setting('site.whatsapp'));
    $columns = [
        'footer_1' => setting_t('site.footer_1_title', __('Müştəri xidmətləri')),
        'footer_2' => setting_t('site.footer_2_title', __('Alış-veriş')),
        'footer_3' => setting_t('site.footer_3_title', __('Şirkət')),
    ];
    $socials = collect($menus['social'])->filter(fn ($i) => $i['url'] && $i['url'] !== '#');
    $socialIcons = [
        'facebook' => '<path fill="currentColor" stroke="none" d="M13.5 21v-7.2h2.4l.4-2.8h-2.8V9.2c0-.8.2-1.4 1.4-1.4h1.5V5.3c-.3 0-1.1-.1-2.1-.1-2.1 0-3.6 1.3-3.6 3.7V11H8.3v2.8h2.4V21h2.8z"/>',
        'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r=".6" fill="currentColor"/>',
        'youtube' => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path fill="currentColor" stroke="none" d="M10 9.2v5.6l4.8-2.8z"/>',
        'tiktok' => '<path fill="currentColor" stroke="none" d="M16.4 3c.3 2.1 1.6 3.5 3.8 3.7v3c-1.4.1-2.7-.3-3.8-1v6.2a5.6 5.6 0 1 1-5.6-5.6h.4v3.1a2.6 2.6 0 1 0 2.1 2.5V3h3.1z"/>',
        'linkedin' => '<rect x="3.5" y="3.5" width="17" height="17" rx="3"/><path d="M8 10.5v6M8 7.6v.01M11.5 16.5v-6M11.5 13.2c0-1.6 1-2.7 2.4-2.7s2.1 1 2.1 2.7v3.3"/>',
        'twitter' => '<path d="M4.5 4.5l15 15M19.5 4.5l-15 15" />',
        'x' => '<path d="M4.5 4.5l15 15M19.5 4.5l-15 15" />',
    ];
    $socialIcon = function (string $title) use ($socialIcons) {
        $key = Str::of($title)->lower()->replace([' ', '-'], '')->toString();
        foreach ($socialIcons as $name => $svg) {
            if (str_contains($key, $name)) {
                return $svg;
            }
        }

        return '<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17M12 3.5c2.4 2.5 2.4 14.5 0 17M12 3.5c-2.4 2.5-2.4 14.5 0 17"/>';
    };
@endphp

@if($footerBanner = setting('site.footer_banner'))
  <div class="bz-footer-banner">
    <a @if($link = setting('site.footer_banner_link')) href="{{ $link }}" @endif>
      <img src="{{ image_url($footerBanner) }}" alt="" loading="lazy" />
    </a>
  </div>
@endif

<footer class="bz-footer">
  <div class="bz-footer__main">
    <div class="bz-footer__brand">
      <a href="{{ lroute('front.home') }}" class="bz-footer__logo">
        <img src="{{ $logo }}" width="136" height="36" alt="{{ $siteName }}" loading="lazy" />
      </a>
      @if($about)
        <p class="bz-footer__about">{{ Str::limit($about, 180) }}</p>
      @endif
      @if($socials->isNotEmpty())
        <ul class="bz-footer__social">
          @foreach($socials as $item)
            <li>
              <a href="{{ $item['url'] }}" target="_blank" rel="noopener" aria-label="{{ $item['title'] }}" title="{{ $item['title'] }}">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $socialIcon($item['title']) !!}</svg>
              </a>
            </li>
          @endforeach
        </ul>
      @endif
    </div>

    @foreach($columns as $key => $heading)
      @continue(empty($menus[$key]))
      <nav class="bz-footer__col" aria-label="{{ $heading }}">
        <button type="button" class="bz-footer__title" aria-expanded="false" aria-controls="bz-footer-{{ $key }}">
          {{ $heading }}
          <svg class="bz-footer__chev" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <ul class="bz-footer__links" id="bz-footer-{{ $key }}">
          @foreach($menus[$key] as $item)
            <li>
              <a @if($item['url']) href="{{ $item['url'] }}" @endif @if($item['target']) target="_blank" rel="noopener" @endif>{{ $item['title'] }}</a>
            </li>
          @endforeach
        </ul>
      </nav>
    @endforeach

    <div class="bz-footer__col bz-footer__contact">
      <div class="bz-footer__title bz-footer__title--static">{{ setting_t('site.footer_contact_title', __('Bizimlə əlaqə')) }}</div>
      <ul class="bz-footer__links">
        @if($whatsapp)
          <li>
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="bz-footer__contact-item">
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l1.2-4A8 8 0 1 1 8 18.8z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8c-.9-.4-1.6-1.1-2-2l.8-1-1-2z" fill="currentColor" stroke="none"/></svg>
              <span><small>WhatsApp</small>+{{ $whatsapp }}</span>
            </a>
          </li>
        @endif
        <li>
          <a href="{{ lroute('front.contact') }}" class="bz-footer__contact-item">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="13" rx="2.5"/><path d="M4 7l8 6 8-6"/></svg>
            <span><small>{{ __('Yazın bizə') }}</small>{{ __('Əlaqə forması') }}</span>
          </a>
        </li>
      </ul>
    </div>
  </div>

  <div class="bz-footer__bottom">
    <div class="bz-footer__bottom-inner">
      <span>&copy; {{ date('Y') }} {{ $siteName }}. {{ __('Bütün hüquqlar qorunur.') }}</span>
    </div>
  </div>
</footer>

@push('scripts')
<script>
(function () {
  var mq = window.matchMedia('(max-width: 767px)');
  document.querySelectorAll('.bz-footer__col > button.bz-footer__title').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!mq.matches) return;
      var open = btn.getAttribute('aria-expanded') !== 'true';
      btn.setAttribute('aria-expanded', open);
      btn.parentNode.classList.toggle('is-open', open);
    });
  });
})();
</script>
@endpush
