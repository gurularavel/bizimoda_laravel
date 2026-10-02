@extends('front.account._layout')

@section('title', __('Hesabım'))
@section('heading', __('Hesabım'))

@section('account')
  <p>{{ __('Xoş gəlmisiniz, :name!', ['name' => $user->name]) }}</p>
  <h2 class="title">{{ __('Hesabım') }}</h2>
  <ul class="list-unstyled account-list">
    <li><a href="{{ lroute('front.account.edit') }}">{{ __('Şəxsi məlumatları redaktə et') }}</a></li>
    <li><a href="{{ lroute('front.account.password') }}">{{ __('Şifrəni dəyiş') }}</a></li>
    <li><a href="{{ lroute('front.account.addresses') }}">{{ __('Ünvanlarım') }}</a></li>
    <li><a href="{{ lroute('front.wishlist') }}">{{ __('Arzu siyahısı') }}</a></li>
  </ul>
  <h2 class="title">{{ __('Son sifarişlər') }}</h2>
  @include('front.account._orders-table', ['orders' => $orders])
  <p><a href="{{ lroute('front.account.orders') }}">{{ __('Bütün sifarişlər') }} &rarr;</a></p>
@endsection
