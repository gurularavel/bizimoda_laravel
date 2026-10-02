@php $colors = ['new' => 'danger', 'confirmed' => 'primary', 'shipping' => 'info', 'delivered' => 'success', 'cancelled' => 'secondary']; @endphp
<span class="badge bg-{{ $colors[$status] ?? 'secondary' }}">{{ \App\Models\Order::STATUSES[$status] ?? $status }}</span>
