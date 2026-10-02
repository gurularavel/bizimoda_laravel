@extends('admin.layouts.app')

@section('title', 'Müraciətlər')

@section('content')
<div class="mb-3 d-flex gap-2">
  <a href="{{ route('admin.forms.index') }}" class="btn btn-sm {{ request('type') ? 'btn-light' : 'btn-secondary' }}">Hamısı</a>
  @foreach(\App\Models\FormSubmission::TYPES as $k => $v)
    <a href="{{ route('admin.forms.index', ['type' => $k]) }}" class="btn btn-sm {{ request('type') === $k ? 'btn-secondary' : 'btn-light' }}">{{ $v }}</a>
  @endforeach
</div>
<div class="card">
  <table class="table table-hover mb-0">
    <thead><tr><th>Tip</th><th>Ad</th><th>Əlaqə</th><th>Mövzu / Məhsul</th><th>Tarix</th><th></th></tr></thead>
    <tbody>
      @forelse($items as $item)
        <tr class="{{ $item->is_read ? '' : 'fw-semibold' }}">
          <td><span class="badge badge-soft">{{ \App\Models\FormSubmission::TYPES[$item->type] ?? $item->type }}</span></td>
          <td>{{ $item->name }}</td>
          <td><small>{{ $item->phone }} {{ $item->email }}</small></td>
          <td><small>{{ $item->product?->name ?? $item->subject }}</small></td>
          <td><small>{{ $item->created_at->format('d.m.Y H:i') }}</small></td>
          <td class="text-end"><a href="{{ route('admin.forms.show', $item) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Müraciət yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>
@endsection
