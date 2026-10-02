@extends('front.layouts.popup')

@push('styles')
<link href="catalog/view/theme/journal3/lib/imagezoom/imagezoom.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/lib/swiper/swiper.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-shop.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-shop.css')) }}" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/stylesheet/bizimoda-ui.css?v={{ filemtime(public_path('catalog/view/theme/journal3/stylesheet/bizimoda-ui.css')) }}" type="text/css" rel="stylesheet" media="all" />
@endpush
@push('scripts')
<script src="catalog/view/theme/journal3/lib/imagezoom/jquery.imagezoom.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/js/bizimoda-shop.js?v={{ filemtime(public_path('catalog/view/theme/journal3/js/bizimoda-shop.js')) }}"></script>
@endpush

@php
    $gallery = $product->images->pluck('path');
    if ($product->isSet()) {
        $gallery = $gallery->merge($product->setItems->map(fn ($i) => $i->component?->images->first()?->path)->filter());
    }
    $gallery = $gallery->unique()->values();
    if ($gallery->isEmpty()) { $gallery = collect([null]); }
    $hasDiscount = $price['unit_old_price'] !== null;
@endphp

@section('content')
<div id="product-product" class="container">
  <div class="row">
    <div id="content">
      <div class="product-info">
        <div class="product-left">
          <div class="product-image direction-horizontal position-bottom">
            <div class="swiper main-image" data-options='{"speed":500,"autoplay":false,"pauseOnHover":true,"loop":false}'>
              <div class="swiper-container">
                <div class="swiper-wrapper">
                  @foreach($gallery as $i => $path)
                    <div class="swiper-slide" data-index="{{ $i }}">
                      <img src="{{ thumb($path, 550, 550) }}" alt="{{ $product->name }}" width="550" height="550"/>
                    </div>
                  @endforeach
                </div>
              </div>
              <div class="swiper-controls">
                <div class="swiper-buttons"><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div>
                <div class="swiper-pagination"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="product-right">
          <div id="product" class="product-details" data-price-url="{{ lroute('front.product.price', ['product' => $product->id]) }}" data-cart-url="{{ lroute('front.cart.add', ['product' => $product->id]) }}">
            <div class="title page-title"><a href="{{ $canonical }}" target="_top">{{ $product->name }}</a></div>
            <div class="product-price-group">
              <div class="price-wrapper">
                <div class="price-group price-block">
                  <div class="{{ $hasDiscount ? 'product-price-new' : 'product-price' }} price-block__current"><span class="js-unit-price">{{ money($price['unit_price']) }}</span></div>
                  <div class="product-price-old price-block__old" @unless($hasDiscount) style="display:none" @endunless><span class="js-unit-old-price">{{ $hasDiscount ? money($price['unit_old_price']) : '' }}</span></div>
                </div>
              </div>
            </div>
            @if($product->productOptions->isNotEmpty())
              @include('front.product.options')
            @endif
            @if($product->isSet() && $product->setItems->isNotEmpty())
              @include('front.product.set-modules')
            @endif
            <div class="alert alert-danger js-config-error" style="display:none"></div>
            @php $minQty = $product->isSet() ? 1 : max(1, (int) $product->min_qty); @endphp
            <div class="bz-buy__actions">
              <div class="bz-qty" data-min="{{ $minQty }}">
                <button type="button" class="bz-qty__btn" data-step="-1" aria-label="-">&minus;</button>
                <input id="product-quantity" type="text" inputmode="numeric" name="quantity" value="{{ $minQty }}" class="bz-qty__input" aria-label="{{ __('Sayı') }}"/>
                <button type="button" class="bz-qty__btn" data-step="1" aria-label="+">+</button>
              </div>
              <button type="button" id="button-cart" data-loading-text="{{ __('Səbətə at') }}…" class="bz-btn bz-btn--primary bz-btn--lg"><span>{{ __('Səbətə at') }}</span></button>
            </div>
            @if($product->short_description)
              <div class="product-short-description" style="margin-top:15px">{!! $product->short_description !!}</div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
