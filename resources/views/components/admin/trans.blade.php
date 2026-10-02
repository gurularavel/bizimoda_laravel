@props([
    'name',
    'label',
    'model' => null,
    'type' => 'text',      // text | textarea | editor
    'required' => false,
    'rows' => 3,
    'help' => null,
])
@php
    $locales = \App\Support\Locales::all();
    $default = \App\Support\Locales::default();
    $uid = 'tr-'.str_replace(['[', ']', '.'], '-', $name).'-'.\Illuminate\Support\Str::random(4);
    $valueFor = function ($code) use ($name, $model) {
        $old = old($name.'.'.$code);
        if ($old !== null) {
            return $old;
        }
        if (is_object($model) && method_exists($model, 'getTranslation')) {
            return $model->getTranslation($name, $code, false);
        }
        if (is_array($model)) {
            return $model[$code] ?? '';
        }

        return '';
    };
@endphp
<div class="mb-3 trans-field">
  <div class="d-flex align-items-center justify-content-between mb-1">
    <label class="form-label mb-0">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <ul class="nav nav-pills nav-pills-sm trans-tabs" role="tablist">
      @foreach($locales as $code => $lang)
        <li class="nav-item">
          <button type="button" class="nav-link {{ $code === $default ? 'active' : '' }} {{ $errors->has($name.'.'.$code) ? 'text-danger' : '' }}" data-bs-toggle="tab" data-bs-target="#{{ $uid }}-{{ $code }}">
            {{ strtoupper($code) }}@if(trim((string) $valueFor($code)) === '' && $code !== $default)<span class="dot-empty" title="Boşdur"></span>@endif
          </button>
        </li>
      @endforeach
    </ul>
  </div>
  <div class="tab-content">
    @foreach($locales as $code => $lang)
      <div class="tab-pane {{ $code === $default ? 'show active' : '' }}" id="{{ $uid }}-{{ $code }}">
        @if($type === 'text')
          <input type="text" name="{{ $name }}[{{ $code }}]" value="{{ $valueFor($code) }}" class="form-control @error($name.'.'.$code) is-invalid @enderror" placeholder="{{ $lang['name'] }}" @if($required && $code === $default) required @endif>
        @else
          <textarea name="{{ $name }}[{{ $code }}]" rows="{{ $rows }}" class="form-control {{ $type === 'editor' ? 'js-editor' : '' }} @error($name.'.'.$code) is-invalid @enderror" placeholder="{{ $lang['name'] }}">{{ $valueFor($code) }}</textarea>
        @endif
      </div>
    @endforeach
  </div>
  @if($help)<div class="form-text">{{ $help }}</div>@endif
</div>
