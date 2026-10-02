@extends('front.layouts.app')

@section('title', __('Məhsul müqayisəsi'))

@section('content')
@include('front.partials.breadcrumbs', ['items' => [['title' => __('Məhsul müqayisəsi'), 'url' => lroute('front.compare')]]])
<h1 class="title page-title"><span>{{ __('Məhsul müqayisəsi') }}</span></h1>
<div id="product-compare" class="container">
  <div class="row">
    <div id="content" class="col-sm-12">
      @if($products->isEmpty())
        <p>{{ __('Müqayisə üçün məhsul seçməmisiniz.') }}</p>
        <div class="buttons"><div class="pull-right"><a href="{{ lroute('front.home') }}" class="btn btn-default">{{ __('Davam et') }}</a></div></div>
      @else
        <div class="table-responsive">
          <table class="table table-bordered compare-table">
            <thead>
              <tr><td colspan="{{ $products->count() + 1 }}"><strong>{{ __('Məhsul haqqında') }}</strong></td></tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ __('Məhsul') }}</td>
                @foreach($products as $p)<td><a href="{{ $p->url() }}"><strong>{{ $p->name }}</strong></a></td>@endforeach
              </tr>
              <tr>
                <td>{{ __('Şəkil') }}</td>
                @foreach($products as $p)<td class="text-center"><img src="{{ thumb($p->mainImage(), 150, 150) }}" alt="{{ $p->name }}" class="img-thumbnail"/></td>@endforeach
              </tr>
              <tr>
                <td>{{ __('Qiymət') }}</td>
                @foreach($products as $p)<td>@if($p->hasDiscount())<s>{{ money($p->computed_old_price) }}</s><br/>@endif<b>{{ money($p->computed_price) }}</b></td>@endforeach
              </tr>
              <tr>
                <td>{{ __('Brend') }}</td>
                @foreach($products as $p)<td>{{ $p->brand?->name }}</td>@endforeach
              </tr>
              <tr>
                <td>{{ __('Mövcudluq') }}</td>
                @foreach($products as $p)<td>{{ __(\App\Models\Product::STOCK_STATUSES[$p->stock_status] ?? '') }}</td>@endforeach
              </tr>
              @foreach($attributes as $attribute)
                <tr>
                  <td>{{ $attribute->name }}</td>
                  @foreach($products as $p)
                    <td>{{ $p->attributeValues->where('attribute_id', $attribute->id)->pluck('value')->implode(', ') }}</td>
                  @endforeach
                </tr>
              @endforeach
              <tr>
                <td></td>
                @foreach($products as $p)
                  <td>
                    <button type="button" class="btn btn-primary btn-block" onclick="cart.add('{{ $p->id }}');">{{ __('Səbətə at') }}</button>
                    <a href="{{ lroute('front.compare.remove', ['product' => $p->id]) }}" class="btn btn-danger btn-block">{{ __('Sil') }}</a>
                  </td>
                @endforeach
              </tr>
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
