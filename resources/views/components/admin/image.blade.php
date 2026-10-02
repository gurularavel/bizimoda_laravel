@props(['name', 'label', 'value' => null, 'help' => null])
<div class="mb-3 image-field">
  <label class="form-label">{{ $label }}</label>
  <div class="d-flex gap-3 align-items-start">
    <div class="image-preview">
      @if($value)
        <img src="{{ thumb($value, 160, 160) }}" alt="">
      @else
        <i class="bi bi-image text-muted fs-2"></i>
      @endif
    </div>
    <div class="flex-grow-1">
      <input type="file" name="{{ $name }}" accept="image/*" class="form-control form-control-sm js-image-input">
      @if($value)
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" name="{{ $name }}_remove" value="1" id="{{ $name }}_remove">
          <label class="form-check-label small" for="{{ $name }}_remove">Şəkli sil</label>
        </div>
      @endif
      @if($help)<div class="form-text">{{ $help }}</div>@endif
    </div>
  </div>
</div>
