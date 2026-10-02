@extends('admin.layouts.app')

@section('title', 'Ana səhifə bölmələri')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <p class="text-muted mb-0">Ana səhifədə görünən bölmələr. Sürükləyib sıralayın.</p>
  <div class="dropdown">
    <button class="btn btn-brand dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-plus-lg"></i> Bölmə əlavə et</button>
    <ul class="dropdown-menu dropdown-menu-end">
      @foreach(\App\Models\HomeSection::TYPES as $k => $v)<li><a class="dropdown-item" href="{{ route('admin.home-sections.create', ['type' => $k]) }}">{{ $v }}</a></li>@endforeach
    </ul>
  </div>
</div>
<div class="card">
  <ul class="list-group list-group-flush js-sections" data-url="{{ route('admin.home-sections.reorder') }}">
    @forelse($sections as $section)
      <li class="list-group-item d-flex align-items-center gap-3 {{ $section->is_active ? '' : 'opacity-50' }}" data-id="{{ $section->id }}">
        <i class="bi bi-grip-vertical drag-handle"></i>
        <span class="badge badge-soft">{{ \App\Models\HomeSection::TYPES[$section->type] ?? $section->type }}</span>
        <span class="fw-semibold">{{ $section->title ?: '—' }}</span>
        @if($section->type === 'products')<small class="text-muted">{{ \App\Models\HomeSection::PRODUCT_SOURCES[$section->data['source'] ?? 'featured'] ?? '' }} · {{ $section->data['limit'] ?? 12 }} məhsul</small>@endif
        <div class="ms-auto d-flex gap-1">
          <a href="{{ route('admin.home-sections.edit', $section) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
          <form method="post" action="{{ route('admin.home-sections.destroy', $section) }}" data-confirm="Bölmə silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
        </div>
      </li>
    @empty
      <li class="list-group-item text-muted">Bölmə yoxdur</li>
    @endforelse
  </ul>
</div>
@endsection

@push('scripts')
<script>
  var list = document.querySelector('.js-sections');
  new Sortable(list, { handle: '.drag-handle', animation: 150, onEnd: function () {
    $.post($(list).data('url'), { ids: $(list).children('li').map(function () { return $(this).data('id'); }).get() });
  } });
</script>
@endpush
