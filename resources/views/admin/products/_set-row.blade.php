<tr data-component-price="{{ $row['component_price'] ?? 0 }}">
  <td><i class="bi bi-grip-vertical drag-handle"></i></td>
  <td>
    <select name="set_items[{{ $i }}][component_id]" class="form-select form-select-sm js-product-search" data-type="component" data-placeholder="Modul axtar...">
      @if(! empty($row['component_id']))
        <option value="{{ $row['component_id'] }}" selected>{{ $row['component_text'] ?? ('#'.$row['component_id']) }}</option>
      @endif
    </select>
    @error("set_items.$i.component_id")<div class="text-danger small">{{ $message }}</div>@enderror
  </td>
  <td>
    <input type="number" min="0" name="set_items[{{ $i }}][default_qty]" value="{{ $row['default_qty'] ?? 1 }}" class="form-control form-control-sm js-default-qty @error("set_items.$i.default_qty") is-invalid @enderror">
    @error("set_items.$i.default_qty")<div class="text-danger small">{{ $message }}</div>@enderror
  </td>
  <td><input type="number" min="1" name="set_items[{{ $i }}][min_qty]" value="{{ $row['min_qty'] ?? 1 }}" class="form-control form-control-sm"></td>
  <td>
    <input type="number" min="1" name="set_items[{{ $i }}][max_qty]" value="{{ $row['max_qty'] ?? '' }}" class="form-control form-control-sm @error("set_items.$i.max_qty") is-invalid @enderror" placeholder="∞">
    @error("set_items.$i.max_qty")<div class="text-danger small">{{ $message }}</div>@enderror
  </td>
  <td class="text-center"><input class="form-check-input" type="checkbox" name="set_items[{{ $i }}][is_required]" value="1" @checked(! empty($row['is_required']))></td>
  <td><input type="number" step="0.01" min="0" name="set_items[{{ $i }}][price_override]" value="{{ $row['price_override'] ?? '' }}" class="form-control form-control-sm js-price-override" placeholder="{{ isset($row['component_price']) ? money_plain($row['component_price']) : 'modulun qiyməti' }}"></td>
  <td><input type="number" step="0.01" min="0" name="set_items[{{ $i }}][old_price_override]" value="{{ $row['old_price_override'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ ! empty($row['component_old_price']) ? money_plain($row['component_old_price']) : '' }}"></td>
  <td class="text-end text-nowrap js-row-total">—</td>
  <td><button type="button" class="btn btn-sm btn-light text-danger js-remove-set-row"><i class="bi bi-x-lg"></i></button></td>
</tr>
