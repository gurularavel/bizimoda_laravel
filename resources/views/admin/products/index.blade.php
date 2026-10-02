@extends('admin.layouts.app')

@section('title', 'Məhsullar')

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <form class="d-flex flex-wrap gap-2" method="get">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Ad, SKU və ya ID..." style="width:220px">
    <select name="category" class="form-select js-select2" data-placeholder="Kateqoriya" style="width:240px">
      <option value=""></option>
      @foreach($categories as $id => $label)<option value="{{ $id }}" @selected(request('category') == $id)>{{ $label }}</option>@endforeach
    </select>
    <select name="type" class="form-select" style="width:auto">
      <option value="">Bütün tiplər</option>
      @foreach(\App\Models\Product::TYPES as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach
    </select>
    <select name="status" class="form-select" style="width:auto">
      <option value="">Status</option>
      <option value="1" @selected(request('status') === '1')>Aktiv</option>
      <option value="0" @selected(request('status') === '0')>Deaktiv</option>
    </select>
    <select name="sort" class="form-select" style="width:auto">
      <option value="latest">Ən yenilər</option>
      <option value="name" @selected(request('sort') === 'name')>Ad</option>
      <option value="price" @selected(request('sort') === 'price')>Qiymət ↑</option>
      <option value="price_desc" @selected(request('sort') === 'price_desc')>Qiymət ↓</option>
    </select>
    <button class="btn btn-light"><i class="bi bi-funnel"></i> Filtr</button>
    @if(request()->query())<a href="{{ route('admin.products.index') }}" class="btn btn-link">Təmizlə</a>@endif
  </form>
  <div class="dropdown">
    <button class="btn btn-brand dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-plus-lg"></i> Yeni məhsul</button>
    <ul class="dropdown-menu dropdown-menu-end">
      @foreach(\App\Models\Product::TYPES as $k => $v)
        <li><a class="dropdown-item" href="{{ route('admin.products.create', ['type' => $k]) }}">{{ $v }}</a></li>
      @endforeach
    </ul>
  </div>
</div>

<form method="post" action="{{ route('admin.products.bulk') }}" id="bulk-form" data-confirm="Seçilmiş məhsullara əməliyyat tətbiq olunsun?">
  @csrf
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th style="width:30px"><input type="checkbox" class="form-check-input" onclick="$('.js-row-check').prop('checked', this.checked)"></th>
            <th style="width:56px"></th>
            <th>Ad</th>
            <th>Tip</th>
            <th>Əsas kateqoriya</th>
            <th class="text-end">Qiymət</th>
            <th>Stok</th>
            <th>Status</th>
            <th style="width:150px"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($products as $product)
            <tr>
              <td><input type="checkbox" class="form-check-input js-row-check" name="ids[]" value="{{ $product->id }}"></td>
              <td><img src="{{ thumb($product->mainImage(), 80, 80) }}" class="thumb-40" alt=""></td>
              <td>
                <a href="{{ route('admin.products.edit', $product) }}" class="fw-semibold text-decoration-none">{{ $product->name }}</a>
                <div class="small text-muted">#{{ $product->id }} @if($product->sku)· {{ $product->sku }}@endif @if($product->is_featured)· <i class="bi bi-star-fill text-warning"></i>@endif</div>
              </td>
              <td>
                <span class="badge {{ $product->type === 'set' ? 'bg-primary' : ($product->type === 'module' ? 'bg-info text-dark' : 'badge-soft') }}">{{ \App\Models\Product::TYPES[$product->type] }}</span>
                @if($product->type === 'set')<div class="small text-muted">{{ $product->set_items_count }} modul</div>@endif
                @if($product->type === 'module' && ! $product->sold_separately)<div class="small text-muted">ayrıca satılmır</div>@endif
              </td>
              <td><small>{{ $product->mainCategory?->name }}</small></td>
              <td class="text-end">
                {{ money($product->computed_price) }}
                @if($product->hasDiscount())<div class="small text-muted text-decoration-line-through">{{ money($product->computed_old_price) }}</div>@endif
              </td>
              <td><small class="{{ $product->stock_status === 'out_of_stock' ? 'text-danger' : '' }}">{{ \App\Models\Product::STOCK_STATUSES[$product->stock_status] }} ({{ $product->stock_qty }})</small></td>
              <td>{!! $product->is_active ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-secondary">Deaktiv</span>' !!}</td>
              <td class="text-end text-nowrap">
                <a href="{{ $product->url() }}" target="_blank" class="btn btn-sm btn-light" title="Saytda bax"><i class="bi bi-eye"></i></a>
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-light" title="Redaktə"><i class="bi bi-pencil"></i></a>
                <button type="submit" form="dup-{{ $product->id }}" class="btn btn-sm btn-light" title="Surətini çıxar"><i class="bi bi-copy"></i></button>
                <button type="submit" form="del-{{ $product->id }}" class="btn btn-sm btn-light text-danger" title="Sil"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-muted py-5">Məhsul tapılmadı</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap gap-2 align-items-center">
      <select name="action" class="form-select form-select-sm" style="width:auto">
        <option value="activate">Aktiv et</option>
        <option value="deactivate">Deaktiv et</option>
        <option value="delete">Sil</option>
      </select>
      <button class="btn btn-sm btn-light">Seçilmişlərə tətbiq et</button>
      <div class="ms-auto">{{ $products->links('pagination::bootstrap-5') }}</div>
    </div>
  </div>
</form>

@foreach($products as $product)
  <form id="dup-{{ $product->id }}" method="post" action="{{ route('admin.products.duplicate', $product) }}">@csrf</form>
  <form id="del-{{ $product->id }}" method="post" action="{{ route('admin.products.destroy', $product) }}" data-confirm="«{{ $product->name }}» silinsin?">@csrf @method('DELETE')</form>
@endforeach
@endsection
