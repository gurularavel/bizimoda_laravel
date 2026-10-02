@extends('front.layouts.app')

@section('title', __('Şifrənin bərpası'))

@section('content')
@include('front.partials.breadcrumbs', ['items' => [['title' => __('Giriş'), 'url' => lroute('front.login')], ['title' => __('Şifrənin bərpası'), 'url' => lroute('front.password.request')]]])
@include('front.auth._page', ['mode' => 'forgot'])
@endsection
