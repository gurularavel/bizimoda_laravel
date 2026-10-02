@php $cartCount = $cart->count(); @endphp
<div id="cart" class="dropdown">
  <a data-toggle="dropdown" data-loading-text="{{ __('Yüklənir...') }}" class="dropdown-toggle cart-heading" href="{{ lroute('front.cart') }}">
    <i class="fa fa-shopping-cart bz-cart-ico">@include('front.partials.icon', ['name' => 'cart'])
      <span class="cart-label">{{ __('Səbət') }}</span>
    </i>
    <span id="cart-items" class="count-badge {{ $cartCount ? '' : 'count-zero' }}">{{ $cartCount }}</span>
  </a>
  {{-- Məzmun AJAX ilə "ajax/common/cart/info ul li" seçicisi ilə yenilənir — ul daxilində iç-içə siyahı olmamalıdır --}}
  <div id="cart-content" class="dropdown-menu cart-content j-dropdown">
    <ul>
      @if($cart->isEmpty())
        <li class="bz-mini bz-mini--empty">
          <span class="bz-mini__empty-icon">@include('front.partials.icon', ['name' => 'cart', 'size' => 28])</span>
          <p class="cart-empty">{{ __('Səbətiniz boşdur!') }}</p>
          <span class="bz-mini__empty-text">{{ __('Bəyəndiyiniz məhsulları səbətə əlavə edin.') }}</span>
        </li>
      @else
        <li class="bz-mini">
          <div class="bz-mini__head">
            <span>{{ __('Səbətiniz') }}</span>
            <span class="bz-mini__count">{{ trans_choice(':count məhsul', $cartCount, ['count' => $cartCount]) }}</span>
          </div>
          <div class="bz-mini__list">
            @foreach($cart->lines() as $line)
              @php $url = $line['product']->url(); @endphp
              <div class="bz-mini__item">
                <a href="{{ $url }}" class="bz-mini__img"><img src="{{ thumb($line['product']->mainImage(), 64, 64) }}" srcset="{{ thumb($line['product']->mainImage(), 64, 64) }} 1x, {{ thumb($line['product']->mainImage(), 128, 128) }} 2x" width="56" height="56" alt="{{ $line['product']->name }}"/></a>
                <div class="bz-mini__info">
                  <a href="{{ $url }}" class="bz-mini__name">{{ $line['product']->name }}</a>
                  @foreach($line['options'] as $option)
                    <small>{{ tr($option['name']) }}: {{ tr($option['value']) }}</small>
                  @endforeach
                  <span class="bz-mini__qty">{{ $line['quantity'] }} × {{ money($line['unit_price']) }}</span>
                </div>
                <div class="bz-mini__side">
                  <span class="bz-mini__total">{{ money($line['total']) }}</span>
                  <button type="button" onclick="cart.remove('{{ $line['item']->id }}');" title="{{ __('Sil') }}" aria-label="{{ __('Sil') }}" class="bz-mini__remove cart-remove">
                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M5 7h14M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                </div>
              </div>
            @endforeach
          </div>
          <div class="bz-mini__totals">
            <div class="bz-mini__row"><span>{{ __('Məbləğ') }}</span><span>{{ money($cart->subtotal()) }}</span></div>
            @if($fee = $cart->deliveryFee())
              <div class="bz-mini__row"><span>{{ __('Çatdırılma') }}</span><span>{{ money($fee) }}</span></div>
            @endif
            <div class="bz-mini__row bz-mini__row--total"><span>{{ __('Ümumi məbləğ') }}</span><span>{{ money($cart->total()) }}</span></div>
          </div>
          <div class="bz-mini__actions">
            <a class="bz-btn bz-btn--outline" href="{{ lroute('front.cart') }}">{{ __('Səbətə bax') }}</a>
            <a class="bz-btn bz-btn--primary" href="{{ lroute('front.checkout') }}">{{ __('Sifarişi rəsmiləşdir') }}</a>
          </div>
        </li>
      @endif
    </ul>
  </div>
</div>
