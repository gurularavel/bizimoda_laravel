@extends('admin.layouts.app')

@section('title', 'Müştərilər')

@section('content')
<form class="d-flex gap-2 mb-3" method="get">
  <input type="search" name="q" value="{{ request('q') }}" class="form-control" style="max-width:320px" placeholder="Ad, e-poçt, telefon">
  <button class="btn btn-light">Axtar</button>
</form>
<div class="card">
  <table class="table table-hover mb-0">
    <thead><tr><th>Ad</th><th>E-poçt</th><th>Telefon</th><th>Giriş</th><th>Sifariş</th><th class="text-end">Cəmi</th><th>Qeydiyyat</th><th></th></tr></thead>
    <tbody>
      @forelse($customers as $c)
        <tr>
          <td><a href="{{ route('admin.customers.edit', $c) }}" class="fw-semibold">{{ $c->name }}</a> @unless($c->is_active)<span class="badge bg-secondary">deaktiv</span>@endunless</td>
          <td>{{ $c->email }}</td>
          <td>{{ $c->phone }}</td>
          <td>@if($c->provider)<span class="badge badge-soft"><i class="bi bi-{{ $c->provider }}"></i> {{ ucfirst($c->provider) }}</span>@else<span class="badge badge-soft">E-poçt</span>@endif</td>
          <td>{{ $c->orders_count }}</td>
          <td class="text-end">{{ money($c->orders_sum_total ?? 0) }}</td>
          <td><small>{{ $c->created_at->format('d.m.Y') }}</small></td>
          <td class="text-end"><a href="{{ route('admin.customers.edit', $c) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a></td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center text-muted py-4">Müştəri yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mt-3">{{ $customers->links('pagination::bootstrap-5') }}</div>
@endsection
