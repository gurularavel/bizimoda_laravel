@if(! empty($panel))
<div class="module module-filter module-filter-36 bz-filter">
  <h3 class="title module-title">
    <span>{{ __('Filtr') }}</span>
    <button class="reset-filter btn">{{ __('Təmizlə') }}</button>
    <a class="x"></a>
  </h3>
  <div class="module-body">
    <div class="panel-group">
      @foreach($panel as $i => $group)
        @php
            $collapseId = 'filter-collapse-'.$group['key'];
            $open = $group['type'] === 'price' || collect($group['items'] ?? [])->contains('checked', true);
        @endphp
        <div class="module-item module-item-{{ $group['key'] }} panel {{ $open ? 'panel-active' : '' }}">
          <div class="panel-heading">
            <div class="panel-title">
              <a href="#{{ $collapseId }}" class="accordion-toggle {{ $open ? '' : 'collapsed' }}" data-toggle="collapse" aria-expanded="{{ $open ? 'true' : 'false' }}" data-filter="{{ $group['key'] }}">
                {{ $group['title'] }}
                <svg class="bz-filter__chev" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </a>
            </div>
          </div>
          <div class="panel-collapse collapse {{ $open ? 'in' : '' }}" id="{{ $collapseId }}">
            <div class="panel-body">
              @if($group['type'] === 'price')
                <div class="filter-price" id="filter-price-range">
                  <div class="range-slider">
                    <input type="text" class="js-range-slider" value=""/>
                  </div>
                  <div class="extra-controls bz-price-inputs">
                    <input type="text" class="filter-price-min" name="min" data-min="{{ $group['min'] }}" value="{{ $group['from'] }}"/>
                    <span class="currency-symbol currency-right"> ₼</span>
                    <input type="text" class="filter-price-max" name="max" data-max="{{ $group['max'] }}" value="{{ $group['to'] }}"/>
                    <span class="currency-symbol currency-right"> ₼</span>
                  </div>
                </div>
              @else
                <div class="filter-{{ $group['type'] }} {{ ! empty($group['swatch']) ? 'filter-swatch' : '' }}">
                  @foreach($group['items'] as $item)
                    <label class="bz-check {{ $item['checked'] ? 'is-checked' : '' }}">
                      <input type="{{ $group['type'] }}" data-filter-trigger name="{{ $group['name'] }}" value="{{ $item['value'] }}" @checked($item['checked'])>
                      @if(! empty($item['color']))
                        <span class="bz-swatch" style="background:{{ $item['color'] }}"></span>
                      @elseif(! empty($group['images']) || ! empty($item['image']))
                        <img src="{{ thumb($item['image'] ?? null, 38, 38) }}" srcset="{{ thumb($item['image'] ?? null, 38, 38) }} 1x, {{ thumb($item['image'] ?? null, 76, 76) }} 2x" width="38" height="38" alt="{{ $item['label'] }}" title="{{ $item['label'] }}" class="img-responsive" />
                      @endif
                      <span class="links-text">{{ $item['label'] }}</span>
                      <span class="count-badge">{{ $item['count'] }}</span>
                    </label>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endif
