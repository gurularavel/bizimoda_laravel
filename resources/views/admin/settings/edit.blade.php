@extends('admin.layouts.app')

@section('title', 'Parametrlər')

@section('content')
<ul class="nav nav-tabs mb-3">
  @foreach($schema as $key => [$label])
    <li class="nav-item"><a class="nav-link {{ $group === $key ? 'active' : '' }}" href="{{ route('admin.settings.edit', $key) }}">{{ $label }}</a></li>
  @endforeach
</ul>

<div class="row g-3">
  <div class="col-lg-8">
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.settings.update', $group) }}" class="card">
      @csrf
      <div class="card-body">
        @foreach($schema[$group][1] as $key => $def)
          @php
            [$type, $label] = $def;
            $help = $def[2] ?? null;
            $choices = $def[3] ?? [];
            $field = str_replace('.', '__', $key);
            $value = in_array($type, ['password']) ? null : $settings->get($key);
          @endphp
          @switch($type)
            @case('trans')
            @case('trans_textarea')
              <x-admin.trans :name="$field" :label="$label" :model="is_array($value) ? $value : []" :type="$type === 'trans' ? 'text' : 'textarea'" :help="$help" />
              @break
            @case('bool')
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="{{ $field }}" @checked($value)>
                <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                @if($help)<div class="form-text">{{ $help }}</div>@endif
              </div>
              @break
            @case('image')
              <x-admin.image :name="$field" :label="$label" :value="$value" :help="$help" />
              @break
            @case('select')
              <div class="mb-3">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <select name="{{ $field }}" id="{{ $field }}" class="form-select">
                  @foreach($choices as $k => $v)<option value="{{ $k }}" @selected((string) $value === (string) $k)>{{ $v }}</option>@endforeach
                </select>
                @if($help)<div class="form-text">{{ $help }}</div>@endif
              </div>
              @break
            @case('page')
              <div class="mb-3">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <select name="{{ $field }}" id="{{ $field }}" class="form-select">
                  <option value="">—</option>
                  @foreach($pages as $p)<option value="{{ $p->id }}" @selected($value == $p->id)>{{ $p->title }}</option>@endforeach
                </select>
                @if($help)<div class="form-text">{{ $help }}</div>@endif
              </div>
              @break
            @case('code')
              <div class="mb-3">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <textarea name="{{ $field }}" id="{{ $field }}" rows="6" class="form-control font-monospace small">{{ $value }}</textarea>
                @if($help)<div class="form-text">{{ $help }}</div>@endif
              </div>
              @break
            @default
              <div class="mb-3">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <input type="{{ $type === 'password' ? 'password' : ($type === 'number' ? 'number' : 'text') }}" @if($type === 'number') step="0.01" @endif name="{{ $field }}" id="{{ $field }}" value="{{ $value }}" class="form-control" @if($type === 'password') autocomplete="new-password" placeholder="{{ $settings->get($key) ? '•••••••• (saxlanılıb)' : '' }}" @endif>
                @if($help)<div class="form-text">{{ $help }}</div>@endif
              </div>
          @endswitch
        @endforeach
      </div>
      <div class="card-footer bg-white"><button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button></div>
    </form>
  </div>

  <div class="col-lg-4">
    @if($group === 'mail')
      <div class="card mb-3">
        <div class="card-header">Test məktubu</div>
        <div class="card-body">
          <p class="small text-muted">Əvvəlcə parametrləri yadda saxlayın, sonra test məktubu göndərin.</p>
          <form method="post" action="{{ route('admin.settings.test-mail') }}" class="d-flex gap-2">
            @csrf
            <input type="email" name="email" value="{{ auth('admin')->user()->email }}" class="form-control form-control-sm" required>
            <button class="btn btn-sm btn-outline-primary text-nowrap">Göndər</button>
          </form>
        </div>
      </div>
    @endif
    @if($group === 'social')
      <div class="card mb-3">
        <div class="card-header">Callback (redirect) URL-lər</div>
        <div class="card-body small">
          <p>Google Cloud Console və Facebook Developers-də bu ünvanları "Authorized redirect URI" kimi əlavə edin:</p>
          <div class="mb-1"><b>Google:</b> <code>{{ $callbacks['google'] }}</code></div>
          <div><b>Facebook:</b> <code>{{ $callbacks['facebook'] }}</code></div>
        </div>
      </div>
    @endif
    @if($group === 'payment')
      <div class="card mb-3">
        <div class="card-header">Kapital Bank</div>
        <div class="card-body small">
          <p>Ödəniş axını: sifariş yaradılır → müştəri bankın ödəniş səhifəsinə yönləndirilir → qayıdışda status bankın API-si ilə yoxlanılır. Uğurlu ödənişdən sonra admin bildirişi göndərilir.</p>
          <p class="mb-0">Qayıdış ünvanı: <code>{{ url('/payment/kapital/return') }}</code></p>
        </div>
      </div>
    @endif
    <div class="card">
      <div class="card-header">Sistem</div>
      <div class="card-body">
        <form method="post" action="{{ route('admin.cache.clear') }}">@csrf<button class="btn btn-sm btn-light w-100"><i class="bi bi-arrow-clockwise"></i> Keşi təmizlə</button></form>
      </div>
    </div>
  </div>
</div>
@endsection
