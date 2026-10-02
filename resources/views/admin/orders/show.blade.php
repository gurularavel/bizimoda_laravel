@extends('admin.layouts.app')

@section('title', 'Sifariş №'.$order->number)

@section('content')
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="{{ route('admin.orders.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Siyahı</a>
  <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="btn btn-light"><i class="bi bi-printer"></i> Çap et</a>
  <a href="tel:{{ preg_replace('/[^\d+]/', '', $order->phone) }}" class="btn btn-light"><i class="bi bi-telephone"></i> Zəng et</a>
  <a href="https://wa.me/{{ preg_replace('/\D/', '', $order->phone) }}" target="_blank" class="btn btn-light"><i class="bi bi-whatsapp text-success"></i> WhatsApp</a>
  <form method="post" action="{{ route('admin.orders.destroy', $order) }}" class="ms-auto" data-confirm="Sifariş tamamilə silinsin?">@csrf @method('DELETE')<button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Sil</button></form>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-header">Məhsullar</div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>Məhsul</th><th class="text-center">Say</th><th class="text-end">Qiymət</th><th class="text-end">Məbləğ</th></tr></thead>
          <tbody>
            @foreach($order->items as $item)
              <tr>
                <td>
                  <div class="fw-semibold">
                    @if($item->product)<a href="{{ route('admin.products.edit', $item->product_id) }}">{{ $item->name }}</a>@else{{ $item->name }}@endif
                    @if($item->type === 'set')<span class="badge bg-primary ms-1">dəst</span>@endif
                  </div>
                  @if($item->sku)<small class="text-muted">{{ $item->sku }}</small>@endif
                  @if($item->unit_discount > 0)<div class="small text-danger"><i class="bi bi-tag"></i> {{ $item->discount_name ?: 'Endirim' }}: -{{ money($item->unit_discount) }} / ədəd</div>@endif
                  @foreach($item->options ?? [] as $o)<div class="small">{{ $o['name'] }}: <b>{{ $o['value'] }}</b>@if($o['modifier'] ?? 0) (+{{ money($o['modifier']) }})@endif</div>@endforeach
                  @if($item->components->isNotEmpty())
                    <table class="table table-sm table-borderless small mb-0 mt-1 bg-light">
                      @foreach($item->components as $c)
                        <tr><td>— {{ $c->name }}</td><td class="text-center">× {{ $c->quantity }}</td><td class="text-end">{{ money($c->unit_price) }}</td><td class="text-end">{{ money($c->total) }}</td></tr>
                      @endforeach
                    </table>
                  @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-end">{{ money($item->unit_price) }}@if($item->unit_old_price)<div class="small text-muted text-decoration-line-through">{{ money($item->unit_old_price) }}</div>@endif</td>
                <td class="text-end fw-semibold">{{ money($item->total) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr><td colspan="3" class="text-end">Məbləğ</td><td class="text-end">{{ money($order->subtotal) }}</td></tr>
            <tr><td colspan="3" class="text-end">Çatdırılma</td><td class="text-end">{{ money($order->delivery_fee) }}</td></tr>
            <tr><td colspan="3" class="text-end fw-bold">Ümumi</td><td class="text-end fw-bold fs-5">{{ money($order->total) }}</td></tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Tarixçə</div>
      <ul class="list-group list-group-flush">
        @foreach($order->histories as $h)
          <li class="list-group-item d-flex gap-3">
            <small class="text-muted text-nowrap">{{ $h->created_at->format('d.m.Y H:i') }}</small>
            <div>@include('admin.orders._status-badge', ['status' => $h->status]) @if($h->admin)<small class="text-muted">— {{ $h->admin->name }}</small>@endif
              @if($h->comment)<div class="small">{{ $h->comment }}</div>@endif</div>
          </li>
        @endforeach
      </ul>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-header">Status</div>
      <div class="card-body">
        <p>Hazırkı: @include('admin.orders._status-badge', ['status' => $order->status])</p>
        <form method="post" action="{{ route('admin.orders.status', $order) }}">
          @csrf
          <select name="status" class="form-select mb-2">@foreach(\App\Models\Order::STATUSES as $k => $v)<option value="{{ $k }}" @selected($order->status === $k)>{{ $v }}</option>@endforeach</select>
          <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Qeyd (istəyə bağlı)"></textarea>
          <button class="btn btn-brand w-100">Statusu dəyiş</button>
        </form>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Müştəri</div>
      <div class="card-body">
        <div class="fw-semibold">{{ $order->customer_name }}</div>
        <div><i class="bi bi-telephone"></i> {{ $order->phone }}</div>
        @if($order->email)<div><i class="bi bi-envelope"></i> {{ $order->email }}</div>@endif
        @if($order->city || $order->address)<div class="mt-2"><i class="bi bi-geo-alt"></i> {{ trim($order->city.', '.$order->address, ', ') }}</div>@endif
        @if($order->comment)<div class="mt-2 p-2 bg-light rounded small"><b>Qeyd:</b> {{ $order->comment }}</div>@endif
        @if($order->user)<div class="mt-2 small">Qeydiyyatlı müştəri: <a href="{{ route('admin.customers.edit', $order->user) }}">{{ $order->user->email }}</a></div>@endif
        <div class="mt-2 small text-muted">Mənbə: {{ $order->source === 'one_click' ? 'Bir kliklə al' : 'Checkout' }} · Dil: {{ strtoupper($order->locale) }} · IP: {{ $order->ip }}</div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Ödəniş</div>
      <div class="card-body">
        <div class="mb-2">@include('admin.orders._payment-badge', ['order' => $order])</div>
        <form method="post" action="{{ route('admin.orders.payment-status', $order) }}" class="d-flex gap-2 mb-2">
          @csrf
          <select name="payment_status" class="form-select form-select-sm">@foreach(\App\Models\Order::PAYMENT_STATUSES as $k => $v)<option value="{{ $k }}" @selected($order->payment_status === $k)>{{ $v }}</option>@endforeach</select>
          <button class="btn btn-sm btn-light">Yenilə</button>
        </form>
        @if($order->payment_method === 'kapital')
          <form method="post" action="{{ route('admin.orders.verify-payment', $order) }}">@csrf<button class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-arrow-repeat"></i> Bankdan statusu yoxla</button></form>
          @foreach($order->payments as $p)
            <div class="small text-muted mt-2">Kapital order #{{ $p->provider_order_id ?: '—' }} · {{ $p->status }} · {{ $p->updated_at->format('d.m H:i') }}</div>
          @endforeach
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
