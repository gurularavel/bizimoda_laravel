<table width="100%" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px">
  <thead>
    <tr style="background:#f5f5f5">
      <th align="left" style="border:1px solid #ddd">{{ __('Məhsul') }}</th>
      <th align="center" style="border:1px solid #ddd">{{ __('Say') }}</th>
      <th align="right" style="border:1px solid #ddd">{{ __('Qiymət') }}</th>
      <th align="right" style="border:1px solid #ddd">{{ __('Məbləğ') }}</th>
    </tr>
  </thead>
  <tbody>
    @foreach($order->items as $item)
      <tr>
        <td style="border:1px solid #ddd">
          <b>{{ $item->name }}</b>@if($item->sku) <small>({{ $item->sku }})</small>@endif
          @foreach($item->options ?? [] as $o)<br/><small>{{ $o['name'] }}: {{ $o['value'] }}</small>@endforeach
          @if($item->unit_discount > 0)<br/><small style="color:#c00">{{ $item->discount_name ?: __('Endirim') }}: -{{ money($item->unit_discount) }}</small>@endif
          @if($item->components->isNotEmpty())
            <table cellpadding="2" style="margin-top:4px;font-size:12px;color:#555">
              @foreach($item->components as $c)
                <tr><td>— {{ $c->name }}</td><td>× {{ $c->quantity }}</td><td align="right">{{ money($c->unit_price) }}</td><td align="right">= {{ money($c->total) }}</td></tr>
              @endforeach
            </table>
          @endif
        </td>
        <td align="center" style="border:1px solid #ddd">{{ $item->quantity }}</td>
        <td align="right" style="border:1px solid #ddd">{{ money($item->unit_price) }}</td>
        <td align="right" style="border:1px solid #ddd">{{ money($item->total) }}</td>
      </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr><td colspan="3" align="right" style="border:1px solid #ddd">{{ __('Məbləğ') }}</td><td align="right" style="border:1px solid #ddd">{{ money($order->subtotal) }}</td></tr>
    <tr><td colspan="3" align="right" style="border:1px solid #ddd">{{ __('Çatdırılma') }}</td><td align="right" style="border:1px solid #ddd">{{ $order->delivery_fee > 0 ? money($order->delivery_fee) : __('Pulsuz') }}</td></tr>
    <tr><td colspan="3" align="right" style="border:1px solid #ddd"><b>{{ __('Ümumi məbləğ') }}</b></td><td align="right" style="border:1px solid #ddd"><b>{{ money($order->total) }}</b></td></tr>
  </tfoot>
</table>
