@extends('admin.layouts.app')

@section('title', 'Redirect-lər')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <p class="text-muted mb-0">Köhnə saytın URL-lərini yeni ünvanlara yönləndirin (SEO itkisinin qarşısını almaq üçün). Məs: <code>/yataq-otagi/aypara-yataq-desti</code> → <code>/az/yataq-destleri/aypara-yataq-desti</code></p>
  <a href="{{ route('admin.redirects.create') }}" class="btn btn-brand text-nowrap"><i class="bi bi-plus-lg"></i> Yeni</a>
</div>
<div class="card">
  <table class="table mb-0">
    <thead><tr><th>Köhnə ünvan</th><th>Yeni ünvan</th><th>Kod</th><th>Klik</th><th></th></tr></thead>
    <tbody>
      @forelse($redirects as $r)
        <tr>
          <td><code>{{ $r->from_path }}</code></td>
          <td><code>{{ $r->to_path }}</code></td>
          <td>{{ $r->code }}</td>
          <td>{{ $r->hits }}</td>
          <td class="text-end">
            <a href="{{ route('admin.redirects.edit', $r) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            <form method="post" action="{{ route('admin.redirects.destroy', $r) }}" class="d-inline" data-confirm="Silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Yönləndirmə yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-3">{{ $redirects->links('pagination::bootstrap-5') }}</div>
@endsection
