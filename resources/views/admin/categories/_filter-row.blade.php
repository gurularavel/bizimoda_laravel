<tr>
  <td><i class="bi bi-grip-vertical drag-handle"></i></td>
  <td>
    <select name="filters[{{ $i }}][type]" class="form-select form-select-sm js-filter-type">
      @foreach(\App\Models\CategoryFilter::TYPES as $type => $label)
        <option value="{{ $type }}" @selected(($row['type'] ?? '') === $type)>{{ $label }}</option>
      @endforeach
    </select>
  </td>
  <td>
    <select name="filters[{{ $i }}][ref_id]" class="form-select form-select-sm js-ref-attribute">
      @foreach($attributes as $attribute)
        <option value="{{ $attribute->id }}" @selected(($row['type'] ?? '') === 'attribute' && ($row['ref_id'] ?? null) == $attribute->id)>{{ $attribute->name }}</option>
      @endforeach
    </select>
    <select name="filters[{{ $i }}][ref_id]" class="form-select form-select-sm js-ref-option">
      @foreach($options as $option)
        <option value="{{ $option->id }}" @selected(($row['type'] ?? '') === 'option' && ($row['ref_id'] ?? null) == $option->id)>{{ $option->name }}</option>
      @endforeach
    </select>
    <span class="text-muted small js-ref-none">—</span>
  </td>
  <td><button type="button" class="btn btn-sm btn-light text-danger js-remove-row"><i class="bi bi-x-lg"></i></button></td>
</tr>
