@extends('front.account._layout', ['crumbs' => [
    ['title' => __('Sifariş tarixçəsi'), 'url' => lroute('front.account.orders')],
    ['title' => $order->number, 'url' => lroute('front.account.order', ['number' => $order->number])],
]])

@section('title', __('Sifariş').' '.$order->number)
@section('heading', __('Sifariş').' №'.$order->number)

@section('account')
  <table class="table table-bordered table-hover">
    <tbody>
      <tr>
        <td class="text-left" style="width:50%">
          <b>{{ __('Sifariş №') }}:</b> {{ $order->number }}<br/>
          <b>{{ __('Tarix') }}:</b> {{ $order->created_at->format('d.m.Y H:i') }}<br/>
          <b>{{ __('Status') }}:</b> {{ $order->statusLabel() }}
        </td>
        <td class="text-left">
          <b>{{ __('Ödəniş üsulu') }}:</b> {{ $order->paymentMethodLabel() }}<br/>
          <b>{{ __('Ödəniş statusu') }}:</b> {{ __(\App\Models\Order::PAYMENT_STATUSES[$order->payment_status] ?? $order->payment_status) }}<br/>
          <b>{{ __('Ünvan') }}:</b> {{ trim($order->city.', '.$order->address, ', ') }}
        </td>
      </tr>
    </tbody>
  </table>
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <td class="text-left">{{ __('Məhsulun adı') }}</td>
          <td class="text-right">{{ __('Sayı') }}</td>
          <td class="text-right">{{ __('Qiyməti') }}</td>
          <td class="text-right">{{ __('Məbləği') }}</td>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
          <tr>
            <td class="text-left">{{ $item->name }}
              @foreach($item->options ?? [] as $o)<br/><small>{{ $o['name'] }}: {{ $o['value'] }}</small>@endforeach
              @foreach($item->components as $c)<br/><small>— {{ $c->name }} × {{ $c->quantity }} ({{ money($c->unit_price) }})</small>@endforeach
            </td>
            <td class="text-right">{{ $item->quantity }}</td>
            <td class="text-right">{{ money($item->unit_price) }}</td>
            <td class="text-right">{{ money($item->total) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr><td colspan="3" class="text-right"><b>{{ __('Məbləğ') }}</b></td><td class="text-right">{{ money($order->subtotal) }}</td></tr>
        <tr><td colspan="3" class="text-right"><b>{{ __('Çatdırılma') }}</b></td><td class="text-right">{{ $order->delivery_fee > 0 ? money($order->delivery_fee) : __('Pulsuz') }}</td></tr>
        <tr><td colspan="3" class="text-right"><b>{{ __('Ümumi məbləğ') }}</b></td><td class="text-right">{{ money($order->total) }}</td></tr>
      </tfoot>
    </table>
  </div>
  @if($order->histories->isNotEmpty())
    <h3>{{ __('Sifariş tarixçəsi') }}</h3>
    <table class="table table-bordered table-hover">
      <thead><tr><td>{{ __('Tarix') }}</td><td>{{ __('Status') }}</td><td>{{ __('Qeyd') }}</td></tr></thead>
      <tbody>
        @foreach($order->histories as $h)
          <tr><td>{{ $h->created_at->format('d.m.Y H:i') }}</td><td>{{ __(\App\Models\Order::STATUSES[$h->status] ?? $h->status) }}</td><td>{{ $h->comment }}</td></tr>
        @endforeach
      </tbody>
    </table>
  @endif
@endsection
