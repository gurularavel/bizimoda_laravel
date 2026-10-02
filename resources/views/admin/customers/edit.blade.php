@extends('admin.layouts.app')

@section('title', 'Müştəri: '.$customer->name)

@section('content')
<div class="row g-3">
  <div class="col-lg-5">
    <form method="post" action="{{ route('admin.customers.update', $customer) }}" class="card">
      @csrf @method('PUT')
      <div class="card-body">
        <div class="mb-3"><label class="form-label">Ad</label><input name="name" value="{{ old('name', $customer->name) }}" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">E-poçt</label><input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Telefon</label><input name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Yeni şifrə</label><input type="password" name="password" class="form-control" placeholder="Dəyişmək istəmirsinizsə boş qoyun" autocomplete="new-password"></div>
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked($customer->is_active)><label class="form-check-label" for="ia">Aktiv</label></div>
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="newsletter" value="1" id="nl" @checked($customer->newsletter)><label class="form-check-label" for="nl">Xəbər bülletenə abunə</label></div>
        @if($customer->provider)<div class="small text-muted mt-2">Sosial giriş: {{ ucfirst($customer->provider) }}</div>@endif
      </div>
      <div class="card-footer bg-white d-flex gap-2">
        <button class="btn btn-brand">Yadda saxla</button>
      </div>
    </form>
    <form method="post" action="{{ route('admin.customers.destroy', $customer) }}" class="mt-2" data-confirm="Müştəri silinsin? Sifarişlər qalacaq.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Müştərini sil</button></form>
    @if($customer->addresses->isNotEmpty())
      <div class="card mt-3"><div class="card-header">Ünvanlar</div>
        <ul class="list-group list-group-flush">@foreach($customer->addresses as $a)<li class="list-group-item small">{{ $a->full }} @if($a->phone)· {{ $a->phone }}@endif</li>@endforeach</ul>
      </div>
    @endif
  </div>
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">Sifarişlər</div>
      <table class="table mb-0">
        <thead><tr><th>№</th><th class="text-end">Məbləğ</th><th>Status</th><th>Tarix</th></tr></thead>
        <tbody>
          @forelse($orders as $order)
            <tr><td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a></td><td class="text-end">{{ money($order->total) }}</td><td>@include('admin.orders._status-badge', ['status' => $order->status])</td><td><small>{{ $order->created_at->format('d.m.Y') }}</small></td></tr>
          @empty
            <tr><td colspan="4" class="text-muted text-center py-3">Sifariş yoxdur</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
