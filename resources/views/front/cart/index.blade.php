@extends('front.layouts.app')

@section('title', __('Səbət'))

@section('content')
@include('front.partials.breadcrumbs', ['items' => [['title' => __('Səbət'), 'url' => lroute('front.cart')]]])
<h1 class="title page-title">
  <span>{{ __('Səbət') }} @if($lines->isNotEmpty())&nbsp;({{ money($total) }})@endif</span>
</h1>
<div id="checkout-cart" class="container">
  <div class="row">
    <div id="content" class="col-sm-12">
      @if($lines->isEmpty())
        <p>{{ __('Səbətiniz boşdur!') }}</p>
        <div class="buttons clearfix">
          <div class="pull-right"><a href="{{ lroute('front.home') }}" class="btn btn-primary">{{ __('Davam et') }}</a></div>
        </div>
      @else
        <div class="cart-page" data-update-url="{{ lroute('front.cart.update') }}">
          <div class="cart-table">
            <div class="basket-product-wrapper">
              <div class="basket-product-list">
                @foreach($lines as $line)
                  @php $product = $line['product']; $url = $product->url(); $item = $line['item']; @endphp
                  <div class="cart-product-table pr-wrapper {{ $line['components'] ? 'hasSubProduct' : '' }}" data-module-price="{{ money_plain($line['total']) }}" data-module-old-price="{{ money_plain($line['old_total'] ?? $line['total']) }}" data-state="1">
                    <div class="basket-product-image-row">
                      <a href="{{ $url }}"><img src="{{ thumb($product->mainImage(), 140, 140) }}" alt="{{ $product->name }}" title="{{ $product->name }}" class="basket-product-image"/></a>
                    </div>
                    <div class="basket-product-name-row">
                      <h4 class="fm-poppins_bold mb-0"><a href="{{ $url }}">{{ $product->name }}</a></h4>
                      <div class="text4">
                        @foreach($line['options'] as $option)
                          <small>{{ tr($option['name']) }}: {{ tr($option['value']) }}</small><br/>
                        @endforeach
                        @if($product->isSet())
                          <small>{{ __('1 dəstin qiyməti') }}: {{ money($line['unit_price']) }}</small><br/>
                        @endif
                        @if($line['discount'] > 0)
                          <small class="text-danger">{{ $line['discount_name'] ?: __('Endirim') }}: -{{ money($line['discount'] * $line['quantity']) }}</small>
                        @endif
                      </div>
                    </div>
                    <div class="basket-product-count-row pr-count">
                      @php $cartMin = $product->isSet() ? 1 : max(1, (int) $product->min_qty); @endphp
                      <div class="bz-qty js-custom-qty count-input-wrapper">
                        <button type="button" class="bz-qty__btn count_down" aria-label="-" @disabled($line['quantity'] <= $cartMin)>&minus;</button>
                        <input type="text" class="bz-qty__input count-input" name="quantity[{{ $item->id }}]" data-key="{{ $item->id }}" data-min="{{ $cartMin }}" value="{{ $line['quantity'] }}" readonly>
                        <button type="button" class="bz-qty__btn count_up" aria-label="+">+</button>
                      </div>
                    </div>
                    <div class="basket-product-price-row">
                      <div class="basket-product-price"><span>{{ money($line['total']) }}</span></div>
                      @if($line['old_total'])
                        <div class="basket-product-old-price"><span>{{ money($line['old_total']) }}</span></div>
                      @endif
                    </div>
                    <div class="basket-product-delete-row">
                      <a onclick="cart.remove('{{ $item->id }}');" title="{{ __('Sil') }}"><div class="delete-cart-item"><i class="fa fa-trash"></i></div></a>
                    </div>
                  </div>
                  @foreach($line['components'] as $component)
                    <div class="cart-product-table cart-sub-product ml-3">
                      <div class="basket-product-image-row">
                        <img src="{{ thumb($component['product']->images->first()?->path, 140, 140) }}" alt="{{ $component['product']->name }}" class="basket-product-image"/>
                      </div>
                      <div class="basket-product-name-row">
                        <h4 class="mb-0" style="font-size:14px">{{ $component['product']->name }}</h4>
                      </div>
                      <div class="basket-product-count-row">x {{ $component['qty'] * $line['quantity'] }}</div>
                      <div class="basket-product-price-row">
                        <div class="basket-product-price" style="font-size:14px"><span>{{ money($component['total'] * $line['quantity']) }}</span></div>
                      </div>
                      <div class="basket-product-delete-row"></div>
                    </div>
                  @endforeach
                @endforeach
              </div>
            </div>
          </div>

          <div class="cart-bottom">
            <div class="bz-summary">
              <div class="bz-summary__title">{{ __('Sifariş xülasəsi') }}</div>
              @if($oldSubtotal > $subtotal)
                <div class="bz-summary__row">
                  <span>{{ __('Səbətin endirimsiz dəyəri') }}</span>
                  <span class="bz-price-old">{{ money($oldSubtotal) }}</span>
                </div>
                <div class="bz-summary__row bz-summary__row--saving">
                  <span>{{ __('Qənaət') }}</span>
                  <span>-{{ money($oldSubtotal - $subtotal) }}</span>
                </div>
              @endif
              <div class="bz-summary__row">
                <span>{{ __('Məbləğ') }}</span>
                <span>{{ money($subtotal) }}</span>
              </div>
              <div class="bz-summary__row">
                <span>{{ __('Çatdırılma') }}</span>
                <span>{{ $deliveryFee > 0 ? money($deliveryFee) : __('Pulsuz') }}</span>
              </div>
              <div class="bz-summary__row bz-summary__row--total">
                <span>{{ __('Ümumi məbləğ') }}</span>
                <span>{{ money($total) }}</span>
              </div>
              <div class="bz-summary__actions">
                <a href="{{ lroute('front.checkout') }}" class="bz-btn bz-btn--primary bz-btn--lg bz-btn--block">{{ __('Sifarişi rəsmiləşdir') }}</a>
                <a href="{{ lroute('front.home') }}" class="bz-btn bz-btn--outline bz-btn--block">{{ __('Alış-verişə davam') }}</a>
              </div>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Səbət səhifəsində silinmədən sonra səhifəni yenilə
  (function () {
    var original = window.cart.remove;
    window.cart.remove = function (key) {
      $.post('ajax/checkout/cart/remove', { key: key }, function () { location.reload(); }, 'json');
    };
  })();
</script>
@endpush
