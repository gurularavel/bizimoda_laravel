<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="utf-8">
  <title>Sifariş {{ $order->number }}</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 13px; color: #222; margin: 30px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background: #f3f3f3; }
    .right { text-align: right; }
    .sub td { font-size: 12px; color: #555; border-top: 0; }
    @media print { .noprint { display: none; } }
  </style>
</head>
<body onload="window.print()">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <img src="{{ asset('images/logo.png') }}" alt="" style="height:36px">
    <div class="right"><h2 style="margin:0">Sifariş №{{ $order->number }}</h2>{{ $order->created_at->format('d.m.Y H:i') }}</div>
  </div>
  <p>
    <b>Müştəri:</b> {{ $order->customer_name }}<br>
    <b>Telefon:</b> {{ $order->phone }}<br>
    @if($order->email)<b>E-poçt:</b> {{ $order->email }}<br>@endif
    <b>Ünvan:</b> {{ trim($order->city.', '.$order->address, ', ') }}<br>
    <b>Ödəniş:</b> {{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }} ({{ \App\Models\Order::PAYMENT_STATUSES[$order->payment_status] ?? '' }})
    @if($order->comment)<br><b>Qeyd:</b> {{ $order->comment }}@endif
  </p>
  <table>
    <thead><tr><th>Məhsul</th><th class="right">Say</th><th class="right">Qiymət</th><th class="right">Məbləğ</th></tr></thead>
    <tbody>
      @foreach($order->items as $item)
        <tr>
          <td><b>{{ $item->name }}</b> @if($item->sku)({{ $item->sku }})@endif
            @foreach($item->options ?? [] as $o)<br>{{ $o['name'] }}: {{ $o['value'] }}@endforeach</td>
          <td class="right">{{ $item->quantity }}</td>
          <td class="right">{{ money($item->unit_price) }}</td>
          <td class="right">{{ money($item->total) }}</td>
        </tr>
        @foreach($item->components as $c)
          <tr class="sub"><td>&nbsp;&nbsp;— {{ $c->name }}</td><td class="right">{{ $c->quantity * $item->quantity }}</td><td class="right">{{ money($c->unit_price) }}</td><td></td></tr>
        @endforeach
      @endforeach
    </tbody>
    <tfoot>
      <tr><td colspan="3" class="right">Məbləğ</td><td class="right">{{ money($order->subtotal) }}</td></tr>
      <tr><td colspan="3" class="right">Çatdırılma</td><td class="right">{{ money($order->delivery_fee) }}</td></tr>
      <tr><td colspan="3" class="right"><b>Ümumi</b></td><td class="right"><b>{{ money($order->total) }}</b></td></tr>
    </tfoot>
  </table>
  <p style="margin-top:40px">Təhvil verdi: ____________________ &nbsp;&nbsp;&nbsp; Təhvil aldı: ____________________</p>
</body>
</html>
