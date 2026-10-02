@extends('front.layouts.app')

@php $heading = $success ? __('Sifarişiniz qəbul edildi!') : __('Ödəniş baş tutmadı'); @endphp
@section('title', $heading)

@section('content')
@include('front.partials.breadcrumbs', ['items' => [['title' => $heading, 'url' => url()->current()]]])
<h1 class="title page-title"><span>{{ $heading }}</span></h1>
<div id="common-success" class="container">
  <div class="row">
    <div id="content" class="col-sm-12">
      @if($success)
        <p>{{ __('Sifarişiniz №:number uğurla qəbul edildi. Operatorumuz qısa müddətdə sizinlə əlaqə saxlayacaq.', ['number' => $order->number]) }}</p>
        @if($order->payment_method === 'kapital')
          <p>{{ $order->payment_status === 'paid' ? __('Ödəniş uğurla həyata keçirildi.') : __('Ödəniş statusu yoxlanılır.') }}</p>
        @endif
      @else
        <p>{{ __('Sifariş №:number üçün kartla ödəniş tamamlanmadı. Yenidən cəhd edə və ya bizimlə əlaqə saxlaya bilərsiniz.', ['number' => $order->number]) }}</p>
      @endif

      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <td class="text-left">{{ __('Məhsulun adı') }}</td>
              <td class="text-center">{{ __('Sayı') }}</td>
              <td class="text-right">{{ __('Məbləği') }}</td>
            </tr>
          </thead>
          <tbody>
            @foreach($order->items as $item)
              <tr>
                <td class="text-left">{{ $item->name }}
                  @foreach($item->components as $c)<br/><small>— {{ $c->name }} × {{ $c->quantity }}</small>@endforeach
                  @foreach($item->options ?? [] as $o)<br/><small>{{ $o['name'] }}: {{ $o['value'] }}</small>@endforeach
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ money($item->total) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr><td colspan="2" class="text-right"><strong>{{ __('Çatdırılma') }}:</strong></td><td class="text-right">{{ $order->delivery_fee > 0 ? money($order->delivery_fee) : __('Pulsuz') }}</td></tr>
            <tr><td colspan="2" class="text-right"><strong>{{ __('Ümumi məbləğ') }}:</strong></td><td class="text-right">{{ money($order->total) }}</td></tr>
          </tfoot>
        </table>
      </div>

      <div class="buttons">
        <div class="pull-right"><a href="{{ lroute('front.home') }}" class="btn btn-primary">{{ __('Davam et') }}</a></div>
      </div>
    </div>
  </div>
</div>
@endsection
