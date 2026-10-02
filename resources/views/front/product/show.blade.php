@extends('front.layouts.app')

@section('title', $product->meta_title ?: $product->name)
@section('meta_description', $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->short_description), 160))
@section('og_image', thumb($product->mainImage(), 600, 600))

@push('styles')
<link href="catalog/view/theme/journal3/lib/imagezoom/imagezoom.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/lib/lightgallery/css/lightgallery.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/lib/lightgallery/css/lg-transitions.min.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/javascript/jquery/magnific/magnific-popup.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
@endpush
@push('libs')
<script src="catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/imagezoom/jquery.imagezoom.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/lightgallery/js/lightgallery-all.js?v=14218c54"></script>
@endpush

@php
    $gallery = $product->images->pluck('path');
    if ($product->isSet()) {
        $gallery = $gallery->merge($product->setItems->map(fn ($i) => $i->component?->images->first()?->path)->filter());
    }
    $gallery = $gallery->unique()->values();
    if ($gallery->isEmpty()) {
        $gallery = collect([null]);
    }
    $galleryJson = $gallery->map(fn ($p) => ['src' => thumb($p, 1000, 1000), 'thumb' => thumb($p, 80, 80), 'subHtml' => $product->name])->all();
    $label = $product->getTranslation('label', app()->getLocale(), false);
    $hasDiscount = $price['unit_old_price'] !== null;
    $discount = $hasDiscount ? (int) round((1 - $price['unit_price'] / $price['unit_old_price']) * 100) : 0;
    $rating = (int) round($reviews->avg('rating') ?? 0);
@endphp

@section('content')
@include('front.partials.breadcrumbs', ['items' => $breadcrumbs])
<h1 class="title page-title"><span>{{ $product->name }}</span></h1>
<div id="product-product" class="container">
  <div class="row">
    @include('front.product.sidebar', ['categories' => $sidebarCategories, 'active' => $product->mainCategory])

    <div id="content" class="">
      <div class="product-info has-extra-button ">
        <div class="product-left">
          <div class="product-image direction-vertical position-left">
            <div class="swiper main-image" data-options='{"speed":500,"autoplay":{"delay":3000},"pauseOnHover":true,"loop":false}' style="width: calc(100% - 80px)">
              <div class="swiper-container">
                <div class="swiper-wrapper">
                  @foreach($gallery as $i => $path)
                    <div class="swiper-slide" data-gallery=".lightgallery-product-images" data-index="{{ $i }}">
                      <img src="{{ thumb($path, 550, 550) }}" srcset="{{ thumb($path, 550, 550) }} 1x, {{ thumb($path, 1100, 1100) }} 2x" data-largeimg="{{ thumb($path, 1000, 1000) }}" alt="{{ $product->name }}" title="{{ $product->name }}" width="550" height="550" @if($i) loading="lazy" @endif/>
                    </div>
                  @endforeach
                </div>
              </div>
              <div class="swiper-controls">
                <div class="swiper-buttons">
                  <div class="swiper-button-prev"></div>
                  <div class="swiper-button-next"></div>
                </div>
                <div class="swiper-pagination"></div>
              </div>
              @if($label || $hasDiscount)
                <div class="product-labels">
                  @if($label)<span class="product-label product-label-31 product-label-default"><b>{{ $label }}</b></span>@endif
                  @if($hasDiscount)<span class="product-label product-label-28 product-label-default"><b>-{{ $discount }} %</b></span>@endif
                </div>
              @endif
            </div>
            <div class="swiper additional-images" data-options='{"slidesPerView":"auto","spaceBetween":0,"direction":"vertical"}' style="width: 80px">
              <div class="swiper-container">
                <div class="swiper-wrapper">
                  @foreach($gallery as $i => $path)
                    <div class="swiper-slide additional-image" data-index="{{ $i }}">
                      <img src="{{ thumb($path, 80, 80) }}" srcset="{{ thumb($path, 80, 80) }} 1x, {{ thumb($path, 160, 160) }} 2x" alt="{{ $product->name }}" title="{{ $product->name }}" width="80" height="80" @if($i) loading="lazy" @endif/>
                    </div>
                  @endforeach
                </div>
              </div>
              <div class="swiper-buttons">
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
              </div>
              <div class="swiper-pagination"></div>
            </div>
          </div>
          <div class="lightgallery lightgallery-product-images" data-images='@json($galleryJson)' data-options='{"thumbWidth":80,"thumbConHeight":80,"addClass":"lg-product-images","mode":"lg-slide","download":true,"fullScreen":false}'></div>
        </div>

        <div class="product-right">
          <div id="product" class="product-details" data-price-url="{{ lroute('front.product.price', ['product' => $product->id]) }}" data-cart-url="{{ lroute('front.cart.add', ['product' => $product->id]) }}">
            <input type="hidden" name="product_id" id="product-id" value="{{ $product->id }}"/>
            <div class="bz-buy__head">
              <div class="bz-buy__meta">
                <span class="bz-stock {{ $product->inStock() ? 'is-in' : 'is-out' }}">{{ __(\App\Models\Product::STOCK_STATUSES[$product->stock_status] ?? '') }}</span>
                @if($product->sku)<span class="bz-buy__sku">{{ __('Model') }}: {{ $product->sku }}</span>@endif
                @if($product->brand)<span class="bz-buy__sku">{{ __('Brend') }}: {{ $product->brand->name }}</span>@endif
              </div>
              <a href="#product-tabs" class="bz-rating" title="{{ trans_choice(':count şərh', $reviews->count(), ['count' => $reviews->count()]) }}">
                @for($i = 1; $i <= 5; $i++)<i class="fa {{ $i <= $rating ? 'fa-star' : 'fa-star-o' }}"></i>@endfor
                <span>{{ trans_choice(':count şərh', $reviews->count(), ['count' => $reviews->count()]) }}</span>
              </a>
            </div>

            <div class="bz-buy__price">
              <span class="bz-price bz-price--lg js-unit-price">{{ money($price['unit_price']) }}</span>
              <span class="product-price-old bz-price-old bz-price-old--lg" @if(! $hasDiscount) style="display:none" @endif><span class="js-unit-old-price">{{ $hasDiscount ? money($price['unit_old_price']) : '' }}</span></span>
              @if($hasDiscount)<span class="bz-badge bz-badge--sale">-{{ $discount }}%</span>@endif
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
              <button type="button" id="button-cart" data-loading-text="{{ __('Səbətə at') }}…" class="bz-btn bz-btn--primary bz-btn--lg">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M3 4h2l2.2 10.2a1.5 1.5 0 0 0 1.5 1.2h8.6a1.5 1.5 0 0 0 1.5-1.1L20.5 8H6.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9.5" cy="19.5" r="1.3" fill="currentColor"/><circle cx="17" cy="19.5" r="1.3" fill="currentColor"/></svg>
                <span>{{ __('Səbətə at') }}</span>
              </button>
            </div>
            <a class="bz-btn bz-btn--outline bz-btn--block btn-extra" href="javascript:open_popup(22)" data-product_id="{{ $product->id }}" data-product_url="{{ $canonical }}">{{ __('Bir kliklə al') }}</a>

            <div class="bz-buy__links">
              <a onclick="parent.wishlist.add({{ $product->id }});"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-7.5-10.1A4.4 4.4 0 0 1 12 7.6a4.4 4.4 0 0 1 7.5 2.8c0 5.5-7.5 10.1-7.5 10.1z" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>{{ __('Arzu siyahısına əlavə et') }}</a>
              <a onclick="parent.compare.add({{ $product->id }});"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M7 4v16M17 4v16M3 8h8M13 16h8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>{{ __('Müqayisə et') }}</a>
            </div>
            @if($product->min_qty > 1 && ! $product->isSet())
              <div class="minimum">{{ __('Bu məhsul üçün minimum sifariş sayı: :min', ['min' => $product->min_qty]) }}</div>
            @endif
          </div>
        </div>
      </div>

      @include('front.product.tabs')
    </div>
  </div>
</div>

@if($related->isNotEmpty())
<div id="bottom" class="bottom top-row">
  <div class="grid-rows">
    <div class="grid-row grid-row-bottom-1">
      <div class="grid-cols">
        <div class="grid-col grid-col-bottom-1-1">
          <div class="grid-items">
            <div class="grid-item grid-item-bottom-1-1-1">
              @include('front.partials.products-carousel', ['products' => $related, 'title' => __('Oxşar məhsullar'), 'moduleClass' => 'module-products-310'])
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@'.'context' => 'http://schema.org/',
    '@type' => 'Product',
    'name' => $product->name,
    'image' => thumb($gallery->first(), 1000, 1000),
    'description' => \Illuminate\Support\Str::limit(trim(strip_tags((string) ($product->short_description ?: $product->description))), 300),
    'sku' => $product->sku,
    'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'AZN',
        'price' => number_format($price['unit_price'], 2, '.', ''),
        'itemCondition' => 'http://schema.org/NewCondition',
        'availability' => $product->inStock() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock',
        'url' => $canonical,
    ],
] + ($reviews->count() ? ['aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => round($reviews->avg('rating'), 1), 'reviewCount' => $reviews->count()]] : []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
