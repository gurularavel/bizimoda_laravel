@if($orders->isEmpty())
  <p>{{ __('Hələ sifarişiniz yoxdur.') }}</p>
@else
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <td class="text-right">{{ __('Sifariş №') }}</td>
          <td class="text-left">{{ __('Status') }}</td>
          <td class="text-left">{{ __('Ödəniş') }}</td>
          <td class="text-right">{{ __('Məbləğ') }}</td>
          <td class="text-left">{{ __('Tarix') }}</td>
          <td></td>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $order)
          <tr>
            <td class="text-right">{{ $order->number }}</td>
            <td class="text-left">{{ $order->statusLabel() }}</td>
            <td class="text-left">{{ $order->paymentMethodLabel() }}</td>
            <td class="text-right">{{ money($order->total) }}</td>
            <td class="text-left">{{ $order->created_at->format('d.m.Y H:i') }}</td>
            <td class="text-right"><a href="{{ lroute('front.account.order', ['number' => $order->number]) }}" data-toggle="tooltip" title="{{ __('Bax') }}" class="btn btn-info"><i class="fa fa-eye"></i></a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
