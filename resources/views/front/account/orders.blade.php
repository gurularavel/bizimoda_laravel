@extends('front.account._layout', ['crumbs' => [['title' => __('Sifariş tarixçəsi'), 'url' => lroute('front.account.orders')]]])

@section('title', __('Sifariş tarixçəsi'))
@section('heading', __('Sifariş tarixçəsi'))

@section('account')
  @include('front.account._orders-table', ['orders' => $orders])
  <div class="text-right">{{ $orders->links() }}</div>
@endsection
