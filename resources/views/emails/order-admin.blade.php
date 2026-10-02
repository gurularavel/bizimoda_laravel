<div style="font-family:Arial,sans-serif;font-size:14px;color:#333;max-width:700px">
  <h2 style="color:#ed3237">Yeni sifariş №{{ $order->number }}</h2>
  <p>
    <b>Tarix:</b> {{ $order->created_at->format('d.m.Y H:i') }}<br/>
    <b>Mənbə:</b> {{ $order->source === 'one_click' ? 'Bir kliklə al' : 'Checkout' }}<br/>
    <b>Müştəri:</b> {{ $order->customer_name }}<br/>
    <b>Telefon:</b> {{ $order->phone }}<br/>
    @if($order->email)<b>E-mail:</b> {{ $order->email }}<br/>@endif
    @if($order->city || $order->address)<b>Ünvan:</b> {{ trim($order->city.', '.$order->address, ', ') }}<br/>@endif
    <b>Ödəniş:</b> {{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}
      ({{ \App\Models\Order::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status }})<br/>
    @if($order->comment)<b>Qeyd:</b> {{ $order->comment }}<br/>@endif
  </p>
  @include('emails._order-table', ['order' => $order])
  <p style="margin-top:20px"><a href="{{ route('admin.orders.show', $order) }}" style="background:#ed3237;color:#fff;padding:10px 18px;text-decoration:none;border-radius:3px">Admin paneldə bax</a></p>
</div>
