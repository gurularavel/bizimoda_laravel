<div class="product-options js-product-options">
  @foreach($product->productOptions as $productOption)
    @php
        $option = $productOption->option;
        $selected = $config['options'][$productOption->option_id] ?? null;
        $inputName = 'options['.$productOption->option_id.']';
    @endphp
    <div class="form-group {{ $productOption->is_required ? 'required' : '' }} product-option-{{ $option->type === 'select' ? 'select' : 'radio' }} {{ in_array($option->type, ['color', 'image']) ? 'push-option' : '' }}" id="input-option{{ $productOption->option_id }}">
      <label class="control-label">{{ $option->name }}</label>
      @if($option->type === 'select')
        <select name="{{ $inputName }}" class="form-control">
          <option value=""> --- {{ __('Seçin') }} --- </option>
          @foreach($productOption->values as $value)
            <option value="{{ $value->option_value_id }}" @selected($selected == $value->option_value_id)>
              {{ $value->optionValue->name }}@if((float) $value->price_modifier != 0) ({{ $value->price_modifier > 0 ? '+' : '' }}{{ $value->modifier_type === 'percent' ? rtrim(rtrim(number_format($value->price_modifier, 2), '0'), '.').'%' : money($value->price_modifier) }})@endif
            </option>
          @endforeach
        </select>
      @else
        <div>
          @foreach($productOption->values as $value)
            @php $ov = $value->optionValue; @endphp
            <div class="radio">
              <label class="{{ $option->type === 'color' ? 'option-swatch' : '' }}" title="{{ $ov->name }}">
                <input type="radio" name="{{ $inputName }}" value="{{ $value->option_value_id }}" @checked($selected == $value->option_value_id) />
                @if($option->type === 'color' && $ov->color)
                  <span class="option-color" style="display:inline-block;width:26px;height:26px;border-radius:50%;border:1px solid #ddd;vertical-align:middle;background:{{ $ov->color }}"></span>
                @elseif($ov->image)
                  <img src="{{ thumb($ov->image, 40, 40) }}" alt="{{ $ov->name }}" class="img-thumbnail" width="40" height="40"/>
                @endif
                <span class="option-value">{{ $ov->name }}</span>
                @if((float) $value->price_modifier != 0)
                  <span class="option-price">({{ $value->price_modifier > 0 ? '+' : '' }}{{ $value->modifier_type === 'percent' ? rtrim(rtrim(number_format($value->price_modifier, 2), '0'), '.').'%' : money($value->price_modifier) }})</span>
                @endif
              </label>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  @endforeach
</div>
