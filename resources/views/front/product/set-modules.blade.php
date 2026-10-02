{{-- Dəstin modulları: say min/max məhdudiyyəti ilə. Dəstin qiyməti seçilmiş say × modul qiymətindən formalaşır. --}}
<div class="bz-set js-set-modules">
  <div class="bz-set__title">{{ __('Dəstin tərkibi') }}</div>
  <div class="bz-set__list">
    @foreach($product->setItems as $item)
      @continue(! $item->component)
      @php
          $qty = (int) ($config['components'][$item->component_id] ?? $item->default_qty);
          $unit = $item->unitPrice();
          $unitOld = $item->unitOldPrice();
      @endphp
      <div class="bz-set__row js-set-row {{ $qty === 0 ? 'module-disabled' : '' }}" id="sub_product_{{ $item->component_id }}"
           data-module-price="{{ money_plain($unit) }}" data-module-old-price="{{ money_plain($unitOld ?? $unit) }}" data-state="{{ $qty > 0 ? 1 : 0 }}">
        <img class="bz-set__img" src="{{ thumb($item->component->images->first()?->path, 96, 96) }}" width="48" height="48" alt="" loading="lazy"/>
        <div class="bz-set__name">
          {{ $item->component->name }}
          @if($item->min_qty > 1)
            <small>{{ __('minimum :min ədəd', ['min' => $item->min_qty]) }}</small>
          @endif
        </div>
        <div class="bz-qty bz-qty--sm js-custom-qty count-input-wrapper">
          <button type="button" class="bz-qty__btn count_down" aria-label="-">&minus;</button>
          <input type="text" class="bz-qty__input count-input"
                 name="components[{{ $item->component_id }}]"
                 value="{{ $qty }}"
                 data-product_id="{{ $item->component_id }}"
                 data-min_qty="{{ $item->min_qty }}"
                 data-max_qty="{{ $item->max_qty ?: '' }}"
                 data-required="{{ $item->is_required ? 1 : 0 }}"
                 readonly>
          <button type="button" class="bz-qty__btn count_up" aria-label="+">+</button>
        </div>
        <div class="bz-set__price">
          <span class="bz-price">{{ money($unit) }}</span>
          @if($unitOld)
            <span class="bz-price-old">{{ money($unitOld) }}</span>
          @endif
        </div>
      </div>
    @endforeach
  </div>
</div>
