@extends('admin.layouts.app')

@section('title', 'Bloq yazıları')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <form class="d-flex gap-2" method="get"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Başlıq..."><button class="btn btn-light">Axtar</button></form>
  <a href="{{ route('admin.blog-posts.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Yeni yazı</a>
</div>
<div class="card">
  <table class="table table-hover mb-0">
    <thead><tr><th></th><th>Başlıq</th><th>Kateqoriya</th><th>Dərc tarixi</th><th>Baxış</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse($posts as $post)
        <tr>
          <td style="width:56px"><img src="{{ thumb($post->cover, 80, 80, 'cover') }}" class="thumb-40" alt=""></td>
          <td><a href="{{ route('admin.blog-posts.edit', $post) }}" class="fw-semibold">{{ $post->title }}</a></td>
          <td>{{ $post->category?->name }}</td>
          <td><small>{{ $post->published_at?->format('d.m.Y H:i') ?? '—' }}</small></td>
          <td>{{ $post->views }}</td>
          <td>
            @if(! $post->is_active)<span class="badge bg-secondary">Qaralama</span>
            @elseif($post->published_at && $post->published_at->isFuture())<span class="badge bg-info text-dark">Planlaşdırılıb</span>
            @else<span class="badge bg-success">Dərc olunub</span>@endif
          </td>
          <td class="text-end text-nowrap">
            <a href="{{ $post->url() }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a>
            <a href="{{ route('admin.blog-posts.edit', $post) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            <form method="post" action="{{ route('admin.blog-posts.destroy', $post) }}" class="d-inline" data-confirm="Silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Yazı yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-3">{{ $posts->links('pagination::bootstrap-5') }}</div>
@endsection
