@extends('admin.layouts.app')

@section('title', 'Sifarişlər')

@section('content')
<form class="card card-body mb-3" method="get">
  <div class="row g-2">
    <div class="col-md-3"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="№, ad, telefon, e-poçt"></div>
    <div class="col-md-2">
      <select name="status" class="form-select"><option value="">Status</option>@foreach(\App\Models\Order::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select>
    </div>
    <div class="col-md-2">
      <select name="payment_method" class="form-select"><option value="">Ödəniş üsulu</option>@foreach(\App\Models\Order::PAYMENT_METHODS as $k => $v)<option value="{{ $k }}" @selected(request('payment_method') === $k)>{{ $v }}</option>@endforeach</select>
    </div>
    <div class="col-md-2">
      <select name="payment_status" class="form-select"><option value="">Ödəniş statusu</option>@foreach(\App\Models\Order::PAYMENT_STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('payment_status') === $k)>{{ $v }}</option>@endforeach</select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <input type="date" name="from" value="{{ request('from') }}" class="form-control">
      <input type="date" name="to" value="{{ request('to') }}" class="form-control">
    </div>
  </div>
  <div class="mt-2 d-flex gap-2">
    <button class="btn btn-light"><i class="bi bi-funnel"></i> Filtr</button>
    @if(request()->query())<a href="{{ route('admin.orders.index') }}" class="btn btn-link">Təmizlə</a>@endif
    <span class="ms-auto text-muted align-self-center">Cəmi: <b>{{ money($sum) }}</b></span>
  </div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>№</th><th>Müştəri</th><th>Məhsul</th><th class="text-end">Məbləğ</th><th>Ödəniş</th><th>Status</th><th>Mənbə</th><th>Tarix</th><th></th></tr></thead>
      <tbody>
        @forelse($orders as $order)
          <tr>
            <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->number }}</a></td>
            <td>{{ $order->customer_name }}<br><small class="text-muted">{{ $order->phone }}</small></td>
            <td>{{ $order->items_count }}</td>
            <td class="text-end fw-semibold">{{ money($order->total) }}</td>
            <td>@include('admin.orders._payment-badge', ['order' => $order])</td>
            <td>@include('admin.orders._status-badge', ['status' => $order->status])</td>
            <td><small>{{ $order->source === 'one_click' ? 'Bir kliklə' : 'Checkout' }} · {{ strtoupper($order->locale) }}</small></td>
            <td><small>{{ $order->created_at->format('d.m.Y H:i') }}</small></td>
            <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
          </tr>
        @empty
          <tr><td colspan="9" class="text-center text-muted py-5">Sifariş tapılmadı</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="mt-3">{{ $orders->links('pagination::bootstrap-5') }}</div>
@endsection
