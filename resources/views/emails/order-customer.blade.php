<div style="font-family:Arial,sans-serif;font-size:14px;color:#333;max-width:700px">
  <h2 style="color:#ed3237">{{ setting('site.name', 'bizimoda') }}</h2>
  <p>{{ __('Hörmətli :name,', ['name' => $order->customer_name]) }}</p>
  <p>{{ __('Sifarişiniz №:number uğurla qəbul edildi. Operatorumuz qısa müddətdə sizinlə əlaqə saxlayacaq.', ['number' => $order->number]) }}</p>
  @include('emails._order-table', ['order' => $order])
  <p style="margin-top:15px">
    <b>{{ __('Ödəniş üsulu') }}:</b> {{ $order->paymentMethodLabel() }}<br/>
    @if($order->address)<b>{{ __('Ünvan') }}:</b> {{ trim($order->city.', '.$order->address, ', ') }}@endif
  </p>
  <p>{{ __('Təşəkkür edirik!') }}<br/>{{ setting('site.name', 'bizimoda') }} — {{ url('/') }}</p>
</div>
