@php
    $moduleId = $moduleClass ?? 'module-products-310';
    $tooltipClass = $moduleId.' module-products-grid';
@endphp
@if($products->isNotEmpty())
<div class="module module-products {{ $moduleId }} module-products-grid carousel-mode">
  <div class="module-body">
    <div class="module-item module-item-1 swiper-slide">
      @if(! empty($title))
        <h3 class="title module-title">{{ $title }}</h3>
      @endif
      <div class="swiper" data-items-per-row='{"c0":{"0":{"items":5,"spacing":20},"1024":{"items":3,"spacing":20},"980":{"items":3,"spacing":20},"760":{"items":2,"spacing":12}},"c1":{"0":{"items":6,"spacing":20},"980":{"items":3,"spacing":20},"760":{"items":2,"spacing":12}},"c2":{"0":{"items":3,"spacing":20},"760":{"items":2,"spacing":12}},"sc":{"0":{"items":1,"spacing":0}}}' data-options='{"speed":400,"autoplay":{"delay":4000},"pauseOnHover":true,"loop":true}'>
        <div class="swiper-container">
          <div class="swiper-wrapper product-grid">
            @foreach($products as $product)
              @include('front.partials.product-card', ['product' => $product, 'moduleClass' => $tooltipClass, 'layoutClass' => 'product-layout swiper-slide'])
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
  </div>
</div>
@endif
