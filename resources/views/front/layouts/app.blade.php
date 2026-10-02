@php
    $isCustomer = (bool) $customer;
    $pageTitle = trim($__env->yieldContent('title')) ?: setting_t('site.meta_title', setting('site.name', 'bizimoda'));
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: setting_t('site.meta_description', '');
    $ogImage = trim($__env->yieldContent('og_image')) ?: image_url(setting('site.logo'));
@endphp
<!DOCTYPE html>
<html dir="ltr" lang="{{ $currentLocale }}" class="{{ $deviceClass ?? 'desktop desktop-header-active' }} win chrome webkit oc30 {{ $isCustomer ? 'is-customer' : 'is-guest' }} {{ $htmlClass ?? 'route-common-home layout-1' }} store-0 skin-1 mobile-sticky">
<head typeof="og:website">
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $pageTitle }}</title>
<base href="{{ url('/') }}/" />
<link rel="preload" href="catalog/view/theme/journal3/icons/fonts/icomoon.woff2?v1" as="font" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com/" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
@if($metaDescription)<meta name="description" content="{{ $metaDescription }}" />@endif
<meta property="og:type" content="website"/>
<meta property="og:title" content="{{ $pageTitle }}"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:image" content="{{ $ogImage }}"/>
<meta property="og:description" content="{{ $metaDescription }}"/>
<meta name="twitter:card" content="summary"/>
<meta name="twitter:title" content="{{ $pageTitle }}"/>
<meta name="twitter:image" content="{{ $ogImage }}"/>
<link rel="canonical" href="{{ $canonical ?? url()->current() }}" />
@foreach($languageLinks as $code => $link)
<link rel="alternate" hreflang="{{ $code }}" href="{{ $link['url'] }}" />
@endforeach
<script>window['Journal'] = @json($journalConfig);</script>
<script src="catalog/view/theme/journal3/js/journal-init.js?v=1"></script>
<link href="https://fonts.googleapis.com/css?family=Montserrat:700,400,600%7CRoboto:700,400&amp;subset=latin-ext" type="text/css" rel="stylesheet"/>
<link href="catalog/view/javascript/bootstrap/css/bootstrap.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/javascript/font-awesome/css/font-awesome.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/icons/style.minimal.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
@stack('styles')
<link href="catalog/view/theme/journal3/lib/swiper/swiper.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/style.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/generated.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/generated.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-custom.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-custom.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-shop.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-shop.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-ui.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-ui.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-header.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-header.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-search.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-search.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-checkout.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-checkout.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-auth.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-auth.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-footer.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-footer.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-contact.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-contact.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="{{ setting('site.favicon') ? image_url(setting('site.favicon')) : asset('images/favicon.png') }}" rel="icon" />
<script src="catalog/view/theme/journal3/lib/modernizr/modernizr-custom.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/jquery/jquery-2.1.1.min.js?v=14218c54"></script>
<script src="catalog/view/javascript/bootstrap/js/bootstrap.min.js?v=14218c54"></script>
<script src="catalog/view/javascript/common.js?v={{ filemtime(public_path('catalog/view/javascript/common.js')) }}"></script>
<script src="catalog/view/theme/journal3/js/bridge.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/bridge.js')) }}"></script>
{!! setting('site.head_scripts') !!}
@stack('head')
</head>
<body class="">
{!! setting('site.body_scripts') !!}

<div class="mobile-container mobile-main-menu-container">
  <div class="mobile-wrapper-header">
    <span>{{ __('Menyu') }}</span>
    <a class="x"></a>
  </div>
  <div class="mobile-main-menu-wrapper"></div>
</div>

<div class="mobile-container mobile-filter-container">
  <div class="mobile-wrapper-header"></div>
  <div class="mobile-filter-wrapper"></div>
</div>

<div class="mobile-container mobile-cart-content-container">
  <div class="mobile-wrapper-header">
    <span>{{ __('Səbətiniz') }}</span>
    <a class="x"></a>
  </div>
  <div class="mobile-cart-content-wrapper cart-content"></div>
</div>

<div class="site-wrapper">
  @include('front.partials.header')

  @if(session('success'))
    <div class="container"><div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> {{ session('success') }} <button type="button" class="close" data-dismiss="alert">&times;</button></div></div>
  @endif
  @if(session('error'))
    <div class="container"><div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> {{ session('error') }} <button type="button" class="close" data-dismiss="alert">&times;</button></div></div>
  @endif

  @yield('content')

  @include('front.partials.footer')
</div><!-- .site-wrapper -->

@if($notification = setting_t('site.notification_text'))
<div class="notification-wrapper notification-wrapper-bottom">
  <div class="module module-notification module-notification-137 notification" data-options='{"position":null,"title":"","cookie":"{{ $journalConfig['notification'][0]['c'] ?? '' }}"}'>
    <button class="btn notification-close"></button>
    <div class="notification-content">
      <div>
        <div class="notification-title"></div>
        <div class="notification-text">{!! nl2br(e($notification)) !!}</div>
      </div>
    </div>
  </div>
</div>
@endif

<script type="application/ld+json">{!! json_encode(['@'.'context' => 'http://schema.org', '@type' => 'WebSite', 'url' => url('/'), 'name' => setting('site.name', 'bizimoda'), 'potentialAction' => ['@type' => 'SearchAction', 'target' => route('front.search').'?search={search}', 'query-input' => 'required name=search']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode(['@'.'context' => 'http://schema.org', '@type' => 'Organization', 'url' => url('/'), 'logo' => image_url(setting('site.logo'))], JSON_UNESCAPED_SLASHES) !!}</script>
@stack('jsonld')

<script src="catalog/view/theme/journal3/lib/anime/anime.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/vanilla-lazyload/lazyload.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/countdown/jquery.countdown.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/typeahead/typeahead.jquery.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/hoverintent/jquery.hoverIntent.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/cjs/cjs.js?v=14218c54"></script>
@stack('libs')
<script src="catalog/view/theme/journal3/lib/swiper/swiper.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/js/common.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/common.js')) }}"></script>
<script src="catalog/view/theme/journal3/js/journal.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/journal.js')) }}"></script>
<script src="catalog/view/theme/journal3/js/bizimoda-custom.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/bizimoda-custom.js')) }}"></script>
<script src="catalog/view/theme/journal3/js/bizimoda-shop.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/bizimoda-shop.js')) }}"></script>
@stack('scripts')

<div class="scroll-top">
  <i class="fa fa-angle-up"></i>
</div>
@include('front.partials.whatsapp')
</body>
</html>
