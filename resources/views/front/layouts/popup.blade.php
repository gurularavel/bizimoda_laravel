<!DOCTYPE html>
<html dir="ltr" lang="{{ $currentLocale }}" class="desktop win chrome webkit oc30 popup {{ $popupClass ?? 'popup-login' }} is-guest {{ $htmlClass ?? '' }} store-0 skin-1 desktop-header-active mobile-sticky">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ setting('site.name', 'bizimoda') }}</title>
<base href="{{ url('/') }}/" />
<script>window['Journal'] = @json(array_merge($journalConfig, ['isPopup' => true], $journalPopup ?? []));</script>
<link href="https://fonts.googleapis.com/css?family=Montserrat:700,400,600%7CRoboto:700,400&amp;subset=latin-ext" type="text/css" rel="stylesheet"/>
<link href="catalog/view/javascript/bootstrap/css/bootstrap.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/javascript/font-awesome/css/font-awesome.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/icons/style.minimal.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/style.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/generated.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/generated.css')) }}" type="text/css" rel="stylesheet" media="all" />
@stack('styles')
<script src="catalog/view/theme/journal3/lib/jquery/jquery-2.1.1.min.js?v=14218c54"></script>
<script src="catalog/view/javascript/bootstrap/js/bootstrap.min.js?v=14218c54"></script>
<script src="catalog/view/javascript/common.js?v={{ filemtime(public_path('catalog/view/javascript/common.js')) }}"></script>
<script src="catalog/view/theme/journal3/js/bridge.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/bridge.js')) }}"></script>
<script src="catalog/view/theme/journal3/js/bizimoda-phone.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/bizimoda-phone.js')) }}"></script>
</head>
<body class="">
<div class="site-wrapper">
  @yield('content')
</div>
<script src="catalog/view/theme/journal3/lib/anime/anime.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/vanilla-lazyload/lazyload.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/countdown/jquery.countdown.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/typeahead/typeahead.jquery.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/hoverintent/jquery.hoverIntent.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/swiper/swiper.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/js/common.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/common.js')) }}"></script>
<script src="catalog/view/theme/journal3/js/journal.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/journal.js')) }}"></script>
@stack('scripts')
</body>
</html>
