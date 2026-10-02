@extends('front.layouts.app')

@php
    $title = $page?->title ?? __('Əlaqə yaradın');

    // "info@..., +994..." kimi sərbəst mətni ayrı-ayrı kliklənən kontaktlara bölür
    $contacts = collect(preg_split('/[,;\n]+/', (string) setting_t('contact.phones')))
        ->map(fn ($v) => trim($v))->filter()
        ->map(fn ($v) => str_contains($v, '@')
            ? ['type' => 'mail', 'text' => $v, 'href' => 'mailto:'.$v]
            : ['type' => 'tel', 'text' => $v, 'href' => 'tel:'.preg_replace('/[^\d+]/', '', $v)])
        ->values();
    $phoneLink = setting('contact.phone_link') ?: $contacts->firstWhere('type', 'tel')['text'] ?? null;
    $phoneHref = $phoneLink ? 'tel:'.preg_replace('/[^\d+]/', '', $phoneLink) : null;
    $mailHref = $contacts->firstWhere('type', 'mail')['href'] ?? null;
    $whatsapp = preg_replace('/\D+/', '', (string) setting('site.whatsapp'));

    $icons = [
        'pin' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'phone' => '<path d="M5 4h3.5l1.5 4-2 1.5a11 11 0 0 0 6.5 6.5l1.5-2 4 1.5V19a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
        'mail' => '<rect x="3.5" y="5.5" width="17" height="13" rx="2.5"/><path d="M4 7l8 6 8-6"/>',
        'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'store' => '<path d="M4 9.5V20h16V9.5M3 9.5l1.8-5h14.4l1.8 5a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0zM10 20v-5h4v5"/>',
        'chat' => '<path d="M4 20l1.2-4A8 8 0 1 1 8 18.8z"/>',
    ];
    $icon = fn ($name, $size = 22) => '<svg viewBox="0 0 24 24" width="'.$size.'" height="'.$size.'" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$icons[$name].'</svg>';
@endphp

@section('title', $page?->meta_title ?: $title)
@section('meta_description', $page?->meta_description)

@section('content')
@include('front.partials.breadcrumbs', ['items' => [['title' => $title, 'url' => $page?->url() ?? lroute('front.contact')]]])

<div class="bz-contact">
  <div class="bz-contact__head">
    <h1 class="bz-contact__title">{{ $title }}</h1>
    <p class="bz-contact__lead">{{ __('Sualınız, təklifiniz və ya sifarişinizlə bağlı bizə yazın — komandamız qısa zamanda sizinlə əlaqə saxlayacaq.') }}</p>

    @if($phoneHref || $whatsapp || $mailHref)
      <div class="bz-contact__actions">
        @if($phoneHref)
          <a href="{{ $phoneHref }}" class="bz-btn bz-btn--primary">{!! $icon('phone', 18) !!}<span>{{ __('Zəng edin') }}</span></a>
        @endif
        @if($whatsapp)
          <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="bz-btn bz-contact__wa">{!! $icon('chat', 18) !!}<span>WhatsApp</span></a>
        @endif
        @if($mailHref)
          <a href="{{ $mailHref }}" class="bz-btn bz-btn--outline">{!! $icon('mail', 18) !!}<span>{{ __('E-poçt') }}</span></a>
        @endif
      </div>
    @endif
  </div>

  <div class="bz-contact__grid">
    <div class="bz-contact__info">
      @if($address = setting_t('contact.address'))
        <div class="bz-contact__card">
          <span class="bz-contact__ico">{!! $icon('pin') !!}</span>
          <div>
            <div class="bz-contact__label">{{ __('Ünvan') }}</div>
            <div class="bz-contact__text">{!! nl2br(e($address)) !!}</div>
          </div>
        </div>
      @endif

      @if($contacts->isNotEmpty())
        <div class="bz-contact__card">
          <span class="bz-contact__ico">{!! $icon('phone') !!}</span>
          <div>
            <div class="bz-contact__label">{{ __('Telefon və e-poçt') }}</div>
            <ul class="bz-contact__list">
              @foreach($contacts as $c)
                <li><a href="{{ $c['href'] }}">{{ $c['text'] }}</a></li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      @if($hours = setting_t('contact.hours'))
        <div class="bz-contact__card">
          <span class="bz-contact__ico">{!! $icon('clock') !!}</span>
          <div>
            <div class="bz-contact__label">{{ __('İş saatları') }}</div>
            <div class="bz-contact__text">{!! nl2br(e($hours)) !!}</div>
          </div>
        </div>
      @endif

      @if($stores = setting_t('contact.stores'))
        <div class="bz-contact__card">
          <span class="bz-contact__ico">{!! $icon('store') !!}</span>
          <div>
            <div class="bz-contact__label">{{ __('Mağaza əraziləri') }}</div>
            <ul class="bz-contact__tags">
              @foreach(array_filter(array_map('trim', preg_split('/[,;\n]+/', $stores))) as $store)
                <li>{{ $store }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif
    </div>

    <div class="bz-contact__form">
      @include('front.partials.contact-form', ['title' => __('Məktub yazın')])
    </div>
  </div>

  @if($page && ($page->content || $page->blocks->isNotEmpty()))
    <div class="bz-contact__content">
      @if($page->content)<div class="content">{!! $page->content !!}</div>@endif
      @include('front.page.blocks', ['blocks' => $page->blocks])
    </div>
  @endif

  @if($map = setting('contact.map_embed'))
    <div class="bz-contact__map">{!! $map !!}</div>
  @endif
</div>
@endsection
