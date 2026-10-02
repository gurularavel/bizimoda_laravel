@extends('admin.layouts.app')

@section('title', 'Rəylər')

@section('content')
<div class="mb-3">
  <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm {{ request('status') ? 'btn-light' : 'btn-secondary' }}">Hamısı</a>
  <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-secondary' : 'btn-light' }}">Təsdiq gözləyənlər</a>
</div>
<div class="card">
  <table class="table mb-0">
    <thead><tr><th>Məhsul</th><th>Müəllif</th><th>Reytinq</th><th>Rəy</th><th>Tarix</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse($reviews as $review)
        <tr>
          <td><a href="{{ $review->product ? route('admin.products.edit', $review->product) : '#' }}">{{ $review->product?->name }}</a></td>
          <td>{{ $review->author }}</td>
          <td class="text-warning text-nowrap">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
          <td style="max-width:420px">{{ $review->text }}</td>
          <td><small>{{ $review->created_at->format('d.m.Y H:i') }}</small></td>
          <td>{!! $review->is_approved ? '<span class="badge bg-success">Dərc olunub</span>' : '<span class="badge bg-warning text-dark">Gözləyir</span>' !!}</td>
          <td class="text-end text-nowrap">
            <form method="post" action="{{ route('admin.reviews.toggle', $review) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-light" title="{{ $review->is_approved ? 'Gizlət' : 'Təsdiqlə' }}"><i class="bi bi-{{ $review->is_approved ? 'eye-slash' : 'check-lg' }}"></i></button></form>
            <form method="post" action="{{ route('admin.reviews.destroy', $review) }}" class="d-inline" data-confirm="Silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Rəy yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-3">{{ $reviews->links('pagination::bootstrap-5') }}</div>
@endsection
