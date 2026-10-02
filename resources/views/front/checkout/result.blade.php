@extends('front.layouts.app')

@php
    $heading = $success ? __('Sifarişiniz qəbul edildi!') : __('Ödəniş baş tutmadı');
    $user = auth('web')->user();
    $address = trim(implode(', ', array_filter([$order->city, $order->address])));
@endphp
@section('title', $heading)

@section('content')
@include('front.partials.breadcrumbs', ['items' => [['title' => $heading, 'url' => url()->current()]]])
<div class="container">
  <div class="row">
    <div id="content" class="col-sm-12">
      <ol class="bz-steps" aria-label="{{ __('Sifariş addımları') }}">
        <li class="is-done"><span>1</span>{{ __('Səbət') }}</li>
        <li class="is-done"><span>2</span>{{ __('Rəsmiləşdirmə') }}</li>
        <li class="{{ $success ? 'is-done' : 'is-failed' }}" aria-current="step"><span>3</span>{{ __('Təsdiq') }}</li>
      </ol>

      <section class="bz-result {{ $success ? 'is-success' : 'is-failed' }}">
        <span class="bz-result__icon" aria-hidden="true">
          @if($success)
            <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
          @else
            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M7 7l10 10M17 7L7 17"/></svg>
          @endif
        </span>
        <h1 class="bz-result__title">{{ $heading }}</h1>
        <p class="bz-result__text">
          @if($success)
            {{ __('Təşəkkür edirik, :name! Operatorumuz sifarişi təsdiqləmək üçün qısa müddətdə sizinlə əlaqə saxlayacaq.', ['name' => $order->first_name]) }}
          @else
            {{ __('Kartla ödəniş tamamlanmadı. Sifarişiniz yadda saxlanılıb — operatorumuz sizinlə əlaqə saxlayacaq və ya bizə özünüz yaza bilərsiniz.') }}
          @endif
        </p>
        <div class="bz-result__number">
          <span>{{ __('Sifariş nömrəsi') }}</span>
          <b>№{{ $order->number }}</b>
        </div>
        @if($success && $order->payment_method === 'kapital')
          <div class="bz-result__pay {{ $order->payment_status === 'paid' ? 'is-paid' : '' }}">
            {{ $order->payment_status === 'paid' ? __('Ödəniş uğurla həyata keçirildi.') : __('Ödəniş statusu yoxlanılır.') }}
          </div>
        @endif
      </section>

      <div class="bz-checkout bz-result-grid">
        <div class="bz-checkout__main">
          <section class="bz-co-card">
            <h2 class="bz-co-card__title">{{ __('Sifariş məlumatları') }}</h2>
            <dl class="bz-result-info">
              <div><dt>{{ __('Tarix') }}</dt><dd>{{ $order->created_at->format('d.m.Y H:i') }}</dd></div>
              <div><dt>{{ __('Ödəniş üsulu') }}</dt><dd>{{ $order->paymentMethodLabel() }}</dd></div>
              <div><dt>{{ __('Alıcı') }}</dt><dd>{{ $order->customer_name }}</dd></div>
              <div><dt>{{ __('Telefon') }}</dt><dd>{{ $order->phone }}</dd></div>
              @if($order->email)<div><dt>{{ __('E-mail') }}</dt><dd>{{ $order->email }}</dd></div>@endif
              @if($address)<div class="is-wide"><dt>{{ __('Çatdırılma ünvanı') }}</dt><dd>{{ $address }}</dd></div>@endif
              @if($order->comment)<div class="is-wide"><dt>{{ __('Qeyd') }}</dt><dd>{{ $order->comment }}</dd></div>@endif
            </dl>
          </section>

          @if($success)
            <section class="bz-co-card">
              <h2 class="bz-co-card__title">{{ __('Bundan sonra nə olacaq?') }}</h2>
              <ol class="bz-result-next">
                <li><b>{{ __('Təsdiq zəngi') }}</b><span>{{ __('Operatorumuz sifarişin detallarını dəqiqləşdirmək üçün sizə zəng edəcək.') }}</span></li>
                <li><b>{{ __('Hazırlıq') }}</b><span>{{ __('Məhsullar yoxlanılır və çatdırılmaya hazırlanır.') }}</span></li>
                <li><b>{{ __('Çatdırılma') }}</b><span>{{ __('Kuryer razılaşdırılmış vaxtda sifarişi ünvanınıza gətirəcək.') }}</span></li>
              </ol>
            </section>
          @endif
        </div>

        <aside class="bz-checkout__aside">
          <div class="bz-summary bz-co-summary">
            <div class="bz-summary__title">{{ __('Sifarişiniz') }}</div>
            <div class="bz-co-items">
              @foreach($order->items as $item)
                @php $image = $item->product?->mainImage(); @endphp
                <div class="bz-co-item">
                  <span class="bz-co-item__img">
                    <img src="{{ thumb($image, 64, 64) }}" srcset="{{ thumb($image, 64, 64) }} 1x, {{ thumb($image, 128, 128) }} 2x" width="52" height="52" alt=""/>
                    <span class="bz-co-item__qty">{{ $item->quantity }}</span>
                  </span>
                  <span class="bz-co-item__info">
                    @if($item->product && ! $item->product->trashed())
                      <a href="{{ $item->product->url() }}">{{ $item->name }}</a>
                    @else
                      <span class="bz-co-item__name">{{ $item->name }}</span>
                    @endif
                    @foreach($item->options ?? [] as $o)
                      <small>{{ $o['name'] }}: {{ $o['value'] }}</small>
                    @endforeach
                    @if($item->components->isNotEmpty())
                      <small>{{ $item->components->map(fn ($c) => $c->name.($c->quantity > 1 ? ' ×'.$c->quantity : ''))->implode(', ') }}</small>
                    @endif
                  </span>
                  <span class="bz-co-item__price">{{ money($item->total) }}</span>
                </div>
              @endforeach
            </div>
            <div class="bz-summary__row"><span>{{ __('Məbləğ') }}</span><span>{{ money($order->subtotal) }}</span></div>
            @if($order->discount > 0)
              <div class="bz-summary__row"><span>{{ __('Endirim') }}</span><span class="bz-text-green">−{{ money($order->discount) }}</span></div>
            @endif
            <div class="bz-summary__row"><span>{{ __('Çatdırılma') }}</span><span class="{{ $order->delivery_fee > 0 ? '' : 'bz-text-green' }}">{{ $order->delivery_fee > 0 ? money($order->delivery_fee) : __('Pulsuz') }}</span></div>
            <div class="bz-summary__row bz-summary__row--total"><span>{{ __('Ümumi məbləğ') }}</span><span>{{ money($order->total) }}</span></div>

            <div class="bz-result-actions">
              <a href="{{ lroute('front.home') }}" class="bz-btn bz-btn--primary bz-btn--lg bz-btn--block">{{ __('Alış-verişə davam et') }}</a>
              @if($user && $order->user_id === $user->id)
                <a href="{{ lroute('front.account.order', ['number' => $order->number]) }}" class="bz-btn bz-btn--outline bz-btn--lg bz-btn--block">{{ __('Sifarişə bax') }}</a>
              @elseif(! $success)
                <a href="{{ lroute('front.contact') }}" class="bz-btn bz-btn--outline bz-btn--lg bz-btn--block">{{ __('Bizimlə əlaqə') }}</a>
              @endif
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</div>
@endsection
