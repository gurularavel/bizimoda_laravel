@extends('front.account._layout', ['crumbs' => [['title' => __('Arzu siyahısı'), 'url' => lroute('front.wishlist')]]])

@section('title', __('Arzu siyahısı'))
@section('heading', __('Arzu siyahısı'))

@section('account')
  @if($products->isEmpty())
    <p>{{ __('Arzu siyahınız boşdur.') }}</p>
  @else
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <td class="text-center">{{ __('Şəkil') }}</td>
            <td class="text-left">{{ __('Məhsulun adı') }}</td>
            <td class="text-right">{{ __('Qiyməti') }}</td>
            <td class="text-right"></td>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $product)
            <tr>
              <td class="text-center"><a href="{{ $product->url() }}"><img src="{{ thumb($product->mainImage(), 60, 60) }}" alt="{{ $product->name }}" class="img-thumbnail"/></a></td>
              <td class="text-left"><a href="{{ $product->url() }}">{{ $product->name }}</a></td>
              <td class="text-right">
                <div class="price">
                  @if($product->hasDiscount())<s>{{ money($product->computed_old_price) }}</s> @endif<b>{{ money($product->computed_price) }}</b>
                </div>
              </td>
              <td class="text-right" style="white-space:nowrap">
                <button type="button" onclick="cart.add('{{ $product->id }}');" data-toggle="tooltip" title="{{ __('Səbətə at') }}" class="btn btn-primary"><i class="fa fa-shopping-cart"></i></button>
                <a href="{{ lroute('front.wishlist.remove', ['product' => $product->id]) }}" data-toggle="tooltip" title="{{ __('Sil') }}" class="btn btn-danger"><i class="fa fa-times"></i></a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
@endsection
