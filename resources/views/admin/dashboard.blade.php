@extends('admin.layouts.app')

@section('title', 'İdarə paneli')

@section('content')
<div class="row g-3 mb-4">
  @foreach([
      ['Bugünkü sifarişlər', $stats['today_orders'], 'bag', 'primary'],
      ['Bugünkü satış', money($stats['today_sales']), 'cash-coin', 'success'],
      ['Bu ay satış', money($stats['month_sales']), 'graph-up', 'info'],
      ['Yeni (baxılmamış) sifariş', $stats['new_orders'], 'bell', 'danger'],
      ['Aktiv məhsul', $stats['products'], 'box-seam', 'secondary'],
      ['Müştəri', $stats['customers'], 'people', 'secondary'],
      ['Oxunmamış müraciət', $stats['unread_forms'], 'envelope', 'warning'],
  ] as [$label, $value, $icon, $color])
    <div class="col-6 col-md-4 col-xl-3">
      <div class="card stat-card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="rounded-circle bg-{{ $color }} bg-opacity-10 text-{{ $color }} d-flex align-items-center justify-content-center" style="width:46px;height:46px"><i class="bi bi-{{ $icon }} fs-5"></i></div>
          <div><div class="value">{{ $value }}</div><div class="label">{{ $label }}</div></div>
        </div>
      </div>
    </div>
  @endforeach
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">Son sifarişlər <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light">Hamısı</a></div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>№</th><th>Müştəri</th><th>Məbləğ</th><th>Ödəniş</th><th>Status</th><th>Tarix</th></tr></thead>
          <tbody>
            @forelse($latestOrders as $order)
              <tr onclick="location='{{ route('admin.orders.show', $order) }}'" style="cursor:pointer">
                <td><strong>{{ $order->number }}</strong></td>
                <td>{{ $order->customer_name }}<br><small class="text-muted">{{ $order->phone }}</small></td>
                <td>{{ money($order->total) }}</td>
                <td>@include('admin.orders._payment-badge', ['order' => $order])</td>
                <td>@include('admin.orders._status-badge', ['status' => $order->status])</td>
                <td><small>{{ $order->created_at->format('d.m.Y H:i') }}</small></td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted py-4">Hələ sifariş yoxdur</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Ən çox satılanlar</div>
      <ul class="list-group list-group-flush">
        @forelse($topProducts as $p)
          <li class="list-group-item d-flex justify-content-between"><span>{{ $p->name }}</span><span class="text-muted">{{ $p->qty }} əd. · {{ money($p->revenue) }}</span></li>
        @empty
          <li class="list-group-item text-muted">Məlumat yoxdur</li>
        @endforelse
      </ul>
    </div>
  </div>
</div>
@endsection
