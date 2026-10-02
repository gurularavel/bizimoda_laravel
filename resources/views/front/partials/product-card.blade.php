@php
    /** @var \App\Models\Product $product */
    $url = $product->url();
    $image = $product->mainImage();
    $secondImage = $product->relationLoaded('images') ? $product->images->get(1)?->path : null;
    $label = $product->getTranslation('label', app()->getLocale(), false);
    $layoutClass = $layoutClass ?? 'product-layout';
    $minQty = max(1, (int) $product->min_qty);
    $spec = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>'], ' ', (string) ($product->short_description ?: $product->dimensions)))))), 80, '…');
@endphp
<div class="{{ $layoutClass }}">
  <div class="bz-card" data-product-id="{{ $product->id }}">
    <div class="bz-card__media">
      <a href="{{ $url }}" class="bz-card__img {{ $secondImage ? 'has-second' : '' }}" tabindex="-1">
        <img src="{{ thumb($image, 400, 400) }}" srcset="{{ thumb($image, 400, 400) }} 1x, {{ thumb($image, 800, 800) }} 2x" width="400" height="400" alt="{{ $product->name }}" loading="lazy" class="bz-card__img-first"/>
        @if($secondImage)
          <img src="{{ thumb($secondImage, 400, 400) }}" srcset="{{ thumb($secondImage, 400, 400) }} 1x, {{ thumb($secondImage, 800, 800) }} 2x" width="400" height="400" alt="" loading="lazy" class="bz-card__img-second"/>
        @endif
      </a>

      @if($label || $product->hasDiscount())
        <div class="bz-card__badges">
          @if($product->hasDiscount())<span class="bz-badge bz-badge--sale">-{{ $product->discountPercent() }}%</span>@endif
          @if($label)<span class="bz-badge bz-badge--{{ \Illuminate\Support\Str::slug($label) }}">{{ $label }}</span>@endif
        </div>
      @endif

      <div class="bz-card__tools">
        <button type="button" class="bz-icon-btn" title="{{ __('Arzu siyahısına əlavə et') }}" aria-label="{{ __('Arzu siyahısına əlavə et') }}" onclick="wishlist.add('{{ $product->id }}')">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-7.5-10.1A4.4 4.4 0 0 1 12 7.6a4.4 4.4 0 0 1 7.5 2.8c0 5.5-7.5 10.1-7.5 10.1z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        </button>
        <button type="button" class="bz-icon-btn" title="{{ __('Müqayisə et') }}" aria-label="{{ __('Müqayisə et') }}" onclick="compare.add('{{ $product->id }}')">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M7 4v16M17 4v16M3 8h8M13 16h8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>
        <button type="button" class="bz-icon-btn" title="{{ __('Cəld baxış') }}" aria-label="{{ __('Cəld baxış') }}" onclick="quickview('{{ $product->id }}')">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
        </button>
      </div>
    </div>

    <div class="bz-card__body">
      <a href="{{ $url }}" class="bz-card__name">{{ $product->name }}</a>
      @if($spec !== '')
        <div class="bz-card__spec" title="{{ $spec }}">{{ $spec }}</div>
      @endif

      <div class="bz-card__price">
        <span class="bz-price">{{ money($product->computed_price) }}</span>
        @if($product->hasDiscount())
          <span class="bz-price-old">{{ money($product->computed_old_price) }}</span>
        @endif
      </div>

      <div class="bz-card__actions">
        @if(! $product->isSet())
          <div class="bz-qty bz-qty--sm" data-min="{{ $minQty }}">
            <button type="button" class="bz-qty__btn" data-step="-1" aria-label="-">&minus;</button>
            <input type="text" inputmode="numeric" name="quantity" value="{{ $minQty }}" class="bz-qty__input" aria-label="{{ __('Sayı') }}"/>
            <button type="button" class="bz-qty__btn" data-step="1" aria-label="+">+</button>
          </div>
        @endif
        <button type="button" class="bz-btn bz-btn--primary bz-card__cart"
                onclick="cart.add('{{ $product->id }}', $(this).closest('.bz-card').find('input[name=\'quantity\']').val() || 1);">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M3 4h2l2.2 10.2a1.5 1.5 0 0 0 1.5 1.2h8.6a1.5 1.5 0 0 0 1.5-1.1L20.5 8H6.2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9.5" cy="19.5" r="1.3" fill="currentColor"/><circle cx="17" cy="19.5" r="1.3" fill="currentColor"/></svg>
          <span>{{ __('Səbətə at') }}</span>
        </button>
      </div>

      <a class="bz-card__oneclick btn-extra" href="javascript:open_popup(22)" data-product_id="{{ $product->id }}" data-product_url="{{ $url }}">{{ __('Bir kliklə al') }}</a>
    </div>
  </div>
</div>
