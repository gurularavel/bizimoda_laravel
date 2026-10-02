@extends('admin.layouts.app')

@section('title', 'Bloq kateqoriyaları')

@section('content')
<div class="d-flex justify-content-end mb-3"><a href="{{ route('admin.blog-categories.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Yeni kateqoriya</a></div>
<div class="card">
  <table class="table table-hover mb-0">
    <thead><tr><th>Ad</th><th>Yazı</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse($categories as $c)
        <tr>
          <td><a href="{{ route('admin.blog-categories.edit', $c) }}" class="fw-semibold">{{ $c->name }}</a></td>
          <td>{{ $c->posts_count }}</td>
          <td>{!! $c->is_active ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-secondary">Deaktiv</span>' !!}</td>
          <td class="text-end">
            <a href="{{ route('admin.blog-categories.edit', $c) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            <form method="post" action="{{ route('admin.blog-categories.destroy', $c) }}" class="d-inline" data-confirm="Silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">Kateqoriya yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
