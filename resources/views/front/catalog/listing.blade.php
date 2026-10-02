@extends('front.layouts.app')

@section('title', $metaTitle ?? $heading)
@section('meta_description', $metaDescription ?? '')

@push('styles')
<link href="catalog/view/theme/journal3/lib/ion-rangeSlider/ion.rangeSlider.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
@endpush
@push('libs')
<script src="catalog/view/theme/journal3/lib/ias/jquery-ias.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/ion-rangeSlider/ion.rangeSlider.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/lib/accounting/accounting.min.js?v=14218c54"></script>
<script src="catalog/view/theme/journal3/js/filter.js?v=14218c54"></script>
@endpush

@php
    $listUrl = function (array $change) {
        $query = array_merge(request()->except(['page']), $change);

        return request()->url().'?'.http_build_query($query);
    };
@endphp

@section('content')
@include('front.partials.breadcrumbs', ['items' => $breadcrumbs])
<h1 class="title page-title"><span>{{ $heading }}</span></h1>
<div class="container">
  <div class="row">
    <aside id="column-left" class="side-column">
      <div class="grid-rows">
        <div class="grid-row grid-row-column-left-1">
          <div class="grid-cols">
            <div class="grid-col grid-col-column-left-1-1">
              <div class="grid-items">
                <div class="grid-item grid-item-column-left-1-1-1">
                  @include('front.catalog.filter', ['panel' => $panel])
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <div id="content">
      @if(! empty($category) && ($category->description || $category->banner))
        <div class="category-description">
          @if($category->banner)<img src="{{ image_url($category->banner) }}" alt="{{ $category->name }}" class="img-responsive" style="margin-bottom:15px"/>@endif
          {!! $category->description !!}
        </div>
      @endif

      @isset($searchTerm)
        <div class="bz-search-head">
          <form action="{{ lroute('front.search') }}" method="get" class="bz-search-form" role="search">
            <span class="bz-search-form__icon"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
            <input type="search" name="search" value="{{ $searchTerm }}" placeholder="{{ __('Məhsul, kateqoriya və ya model axtarın') }}" aria-label="{{ __('Axtar') }}" autocomplete="off"/>
            <button type="submit" class="bz-btn bz-btn--primary"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg><span>{{ __('Axtar') }}</span></button>
          </form>
          @if($searchTerm !== '' && $products->total() > 0)
            <p class="bz-search-summary">{!! __(':term üzrə :count nəticə tapıldı', ['term' => '<b>«'.e($searchTerm).'»</b>', 'count' => '<b>'.$products->total().'</b>']) !!}</p>
          @endif
          @if(($searchCategories ?? collect())->isNotEmpty())
            <div class="bz-chips">
              @foreach($searchCategories as $cat)
                @php $room = $cat->ancestors()->defaultOrder()->first(); @endphp
                <a class="bz-chip" href="{{ $cat->url() }}">{{ $cat->name }}@if($room)<small>· {{ $room->name }}</small>@endif <small>({{ $cat->products_count }})</small></a>
              @endforeach
            </div>
          @endif
        </div>
      @endisset

      <div class="main-products-wrapper">
        @if($products->total() > 0)
          <div class="products-filter bz-toolbar">
            <button type="button" class="bz-toolbar__filter js-open-filter" data-apply-text="{{ __('Nəticələri göstər') }}"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>{{ __('Filtr') }}@if($hasActiveFilters ?? false)<span class="bz-toolbar__dot"></span>@endif</button>
            <div class="bz-toolbar__count">{{ __(':total məhsul', ['total' => $products->total()]) }}</div>
            @php $compareCount = count(session('compare', [])); @endphp
            <a href="{{ lroute('front.compare') }}" id="compare-total" class="bz-toolbar__compare {{ $compareCount ? '' : 'is-empty' }}">{{ __('Müqayisə') }} <span class="count-badge">{{ $compareCount }}</span></a>
            <div class="select-group">
              <div class="input-group input-group-sm sort-by">
                <label class="input-group-addon" for="input-sort">{{ __('Sırala:') }}</label>
                <select id="input-sort" class="form-control" onchange="location = this.value;">
                  @foreach(\App\Services\ProductFilterService::SORTS as $key => $def)
                    <option value="{{ $listUrl(['sort' => $key]) }}" @selected($sort === $key)>{{ __($def[2]) }}</option>
                  @endforeach
                </select>
              </div>
              <div class="input-group input-group-sm per-page">
                <label class="input-group-addon" for="input-limit">{{ __('Göstər:') }}</label>
                <select id="input-limit" class="form-control" onchange="location = this.value;">
                  @foreach(\App\Services\ProductFilterService::LIMITS as $l)
                    <option value="{{ $listUrl(['limit' => $l]) }}" @selected($limit === $l)>{{ $l }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="main-products product-grid bz-grid">
            @foreach($products as $product)
              @include('front.partials.product-card', ['product' => $product])
            @endforeach
          </div>

          <div class="row pagination-results bz-pagination">
            <div class="col-sm-6 text-left">{{ $products->links() }}</div>
            <div class="col-sm-6 text-right">{{ __('Göstərilir: :from - :to, cəmi :total (:pages səhifə)', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total(), 'pages' => $products->lastPage()]) }}</div>
          </div>
        @else
          <div class="bz-empty">
            <span class="bz-empty__icon"><svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M20 20l-3.5-3.5M8.5 11h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></span>
            @if($hasActiveFilters)
              <h3>{{ __('Seçilmiş filtrlərə uyğun məhsul tapılmadı.') }}</h3>
              <p>{{ __('Filtrləri dəyişin və ya təmizləyin.') }}</p>
            @elseif(isset($searchTerm))
              <h3>{{ $searchTerm !== '' ? __('«:term» üzrə heç nə tapılmadı', ['term' => $searchTerm]) : __('Nə axtarırsınız?') }}</h3>
              <p>{{ __('Yazılışı yoxlayın və ya daha qısa, ümumi söz yazın. Məsələn: çarpayı, divan, masa.') }}</p>
            @else
              <h3>{{ __('Bu bölmədə məhsul yoxdur.') }}</h3>
              <p>{{ __('Digər kateqoriyalara baxın.') }}</p>
            @endif
            @php
                $popular = \App\Models\Category::query()->active()->whereIsRoot()->withCount(['products' => fn ($q) => $q->visible()])->orderBy('sort')->get()->where('products_count', '>', 0)->take(8);
            @endphp
            @if($popular->isNotEmpty())
              <div class="bz-chips">
                @foreach($popular as $cat)
                  <a class="bz-chip" href="{{ $cat->url() }}">{{ $cat->name }}</a>
                @endforeach
              </div>
            @endif
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@if($bottomProducts->isNotEmpty())
<div id="bottom" class="bottom top-row">
  <div class="grid-rows">
    <div class="grid-row grid-row-bottom-1">
      <div class="grid-cols">
        <div class="grid-col grid-col-bottom-1-1">
          <div class="grid-items">
            <div class="grid-item grid-item-bottom-1-1-1">
              @include('front.partials.products-carousel', ['products' => $bottomProducts, 'title' => __('Xüsusi təkliflərimiz'), 'moduleClass' => 'module-products-310'])
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
@endsection
