@extends('admin.layouts.app')

@section('title', 'Səhifələr')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <p class="text-muted mb-0">Məlumat səhifələri (Haqqımızda, Çatdırılma, Əlaqə...). Səhifəni menyuya əlavə etmək üçün <a href="{{ route('admin.menus.index') }}">Menyular</a> bölməsindən istifadə edin.</p>
  <a href="{{ route('admin.pages.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Yeni səhifə</a>
</div>
<div class="card">
  <table class="table table-hover mb-0">
    <thead><tr><th>Başlıq</th><th>URL</th><th>Şablon</th><th>Blok</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse($pages as $page)
        <tr>
          <td><a href="{{ route('admin.pages.edit', $page) }}" class="fw-semibold">{{ $page->title }}</a></td>
          <td><small class="text-muted">/{{ app()->getLocale() }}/{{ $page->slug }}</small></td>
          <td><small>{{ \App\Models\Page::TEMPLATES[$page->template] ?? $page->template }}</small></td>
          <td>{{ $page->blocks_count }}</td>
          <td>{!! $page->is_active ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-secondary">Deaktiv</span>' !!}</td>
          <td class="text-end text-nowrap">
            <a href="{{ $page->url() }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            <form method="post" action="{{ route('admin.pages.destroy', $page) }}" class="d-inline" data-confirm="Səhifə silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Səhifə yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
