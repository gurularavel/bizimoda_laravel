@extends('admin.layouts.app')

@section('title', 'Menyular')

@section('content')
<p class="text-muted">Saytın bütün menyuları buradan idarə olunur: istənilən kateqoriya, məhsul, səhifə, bloq və ya daxili/xarici linki menyuya əlavə edə, çıxara, sıralaya bilərsiniz.</p>
<div class="row g-3">
  @foreach($menus as $menu)
    <div class="col-md-6 col-xl-4">
      <a href="{{ route('admin.menus.edit', $menu) }}" class="card text-decoration-none h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <i class="bi bi-{{ $menu->key === 'main' ? 'menu-button-wide-fill text-danger' : 'list-ul text-secondary' }} fs-3"></i>
          <div>
            <div class="fw-semibold text-dark">{{ \App\Models\Menu::LOCATIONS[$menu->key] ?? $menu->name }}</div>
            <small class="text-muted">{{ $menu->items_count }} element · <code>{{ $menu->key }}</code></small>
          </div>
          <i class="bi bi-chevron-right ms-auto text-muted"></i>
        </div>
      </a>
    </div>
  @endforeach
</div>
@endsection
