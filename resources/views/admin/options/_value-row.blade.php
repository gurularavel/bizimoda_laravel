<tr>
  <td><i class="bi bi-grip-vertical drag-handle"></i><input type="hidden" name="values[{{ $i }}][id]" value="{{ $value?->id }}"></td>
  @foreach(\App\Support\Locales::codes() as $code)
    <td><input type="text" name="values[{{ $i }}][name][{{ $code }}]" value="{{ $value?->getTranslation('name', $code, false) }}" class="form-control form-control-sm"></td>
  @endforeach
  <td><input type="color" name="values[{{ $i }}][color]" value="{{ $value?->color ?: '#ffffff' }}" class="form-control form-control-sm form-control-color"></td>
  <td>
    @if($value?->image)<img src="{{ thumb($value->image, 40, 40) }}" class="thumb-40 mb-1" alt="">@endif
    <input type="file" name="values[{{ $i }}][image]" accept="image/*" class="form-control form-control-sm" style="max-width:180px">
  </td>
  <td><button type="button" class="btn btn-sm btn-light text-danger js-remove-value"><i class="bi bi-x-lg"></i></button></td>
</tr>
