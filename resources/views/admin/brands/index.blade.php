@extends('admin.layouts.app')

@section('title', 'Brendlər')

@section('content')
<div class="d-flex justify-content-end mb-3"><a href="{{ route('admin.brands.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Yeni brend</a></div>
<div class="card">
  <table class="table table-hover mb-0">
    <thead><tr><th></th><th>Ad</th><th>Məhsul</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse($brands as $brand)
        <tr>
          <td style="width:56px">@if($brand->logo)<img src="{{ thumb($brand->logo, 80, 80) }}" class="thumb-40" alt="">@endif</td>
          <td><a href="{{ route('admin.brands.edit', $brand) }}" class="fw-semibold">{{ $brand->name }}</a></td>
          <td>{{ $brand->products_count }}</td>
          <td>{!! $brand->is_active ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-secondary">Deaktiv</span>' !!}</td>
          <td class="text-end">
            <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            <form method="post" action="{{ route('admin.brands.destroy', $brand) }}" class="d-inline" data-confirm="Silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Brend yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
