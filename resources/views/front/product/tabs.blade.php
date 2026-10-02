@php
    $tabs = [];
    if ($product->dimensions) { $tabs['dimensions'] = __('Ölçülər'); }
    if ($attributeGroups->isNotEmpty() || $product->description) { $tabs['features'] = __('Xüsusiyyətlər'); }
    $tabs['reviews'] = __('Rəylər').' ('.$reviews->count().')';
    $first = array_key_first($tabs);
@endphp
<div class="product-blocks blocks-default" id="product-tabs">
  <div class="tabs-container product_extra product_tabs product_tabs-default">
    <ul class="nav nav-tabs">
      @foreach($tabs as $key => $title)
        <li class="{{ $key === $first ? 'active' : '' }}"><a href="#tab-{{ $key }}" data-toggle="tab">{{ $title }}</a></li>
      @endforeach
    </ul>
    <div class="tab-content">
      @isset($tabs['dimensions'])
        <div class="product_extra-242 tab-pane {{ $first === 'dimensions' ? 'active' : '' }}" id="tab-dimensions">
          <div class="block-body expand-block">
            <div class="block-wrapper">
              <div class="block-content">{!! $product->dimensions !!}</div>
            </div>
          </div>
        </div>
      @endisset

      @isset($tabs['features'])
        <div class="tab-pane {{ $first === 'features' ? 'active' : 'fade' }}" id="tab-features">
          @if($attributeGroups->isNotEmpty())
            <table class="table table-bordered product-attributes">
              @foreach($attributeGroups as $groupName => $attributes)
                @if($groupName)
                  <thead><tr><td colspan="2"><strong>{{ $groupName }}</strong></td></tr></thead>
                @endif
                <tbody>
                  @foreach($attributes as $values)
                    <tr>
                      <td style="width:35%"><strong>{{ $values->first()->attribute->name }}</strong></td>
                      <td>{{ $values->pluck('value')->implode(', ') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              @endforeach
            </table>
          @endif
          @if($product->description)
            <div class="product-description">{!! $product->description !!}</div>
          @endif
        </div>
      @endisset

      <div class="tab-pane {{ $first === 'reviews' ? 'active' : 'fade' }}" id="tab-reviews">
        <div id="review">
          @forelse($reviews as $review)
            <table class="table table-striped table-bordered">
              <tr>
                <td style="width: 50%;"><strong>{{ $review->author }}</strong></td>
                <td class="text-right">{{ $review->created_at->format('d.m.Y') }}</td>
              </tr>
              <tr>
                <td colspan="2"><p>{{ $review->text }}</p>
                  @for($i = 1; $i <= 5; $i++)
                    <span class="fa fa-stack">@if($i <= $review->rating)<i class="fa fa-star fa-stack-2x"></i>@endif<i class="fa fa-star-o fa-stack-2x"></i></span>
                  @endfor
                </td>
              </tr>
            </table>
          @empty
            <p>{{ __('Bu məhsul üçün hələ rəy yoxdur.') }}</p>
          @endforelse
        </div>
        <form class="form-horizontal" id="form-review" data-action="{{ lroute('front.product.review', ['product' => $product->id]) }}">
          <h2>{{ __('Rəy yazın') }}</h2>
          <div class="form-group required">
            <div class="col-sm-12">
              <label class="control-label" for="input-name">{{ __('Adınız') }}</label>
              <input type="text" name="name" value="{{ auth('web')->user()?->name }}" id="input-name" class="form-control" />
            </div>
          </div>
          <div class="form-group required">
            <div class="col-sm-12">
              <label class="control-label" for="input-review">{{ __('Rəyiniz') }}</label>
              <textarea name="text" rows="5" id="input-review" class="form-control"></textarea>
            </div>
          </div>
          <div class="form-group required">
            <div class="col-sm-12">
              <label class="control-label">{{ __('Reytinq') }}</label>
              &nbsp;&nbsp;&nbsp; {{ __('Pis') }}&nbsp;
              @for($i = 1; $i <= 5; $i++)
                <input type="radio" name="rating" value="{{ $i }}" /> &nbsp;
              @endfor
              &nbsp;{{ __('Yaxşı') }}
            </div>
          </div>
          <div class="buttons clearfix">
            <div class="pull-right">
              <button type="button" id="button-review" data-loading-text="{{ __('Yüklənir...') }}" class="btn btn-primary">{{ __('Davam et') }}</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
