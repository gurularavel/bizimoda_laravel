@php $pc = ['pending' => 'warning text-dark', 'paid' => 'success', 'failed' => 'danger', 'refunded' => 'secondary']; @endphp
<small>{{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}</small><br>
<span class="badge bg-{{ $pc[$order->payment_status] ?? 'secondary' }}">{{ \App\Models\Order::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status }}</span>
