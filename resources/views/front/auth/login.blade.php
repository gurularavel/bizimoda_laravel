@extends('front.layouts.app')

@section('title', __('Hesab girişi'))

@section('content')
@include('front.partials.breadcrumbs', ['items' => [['title' => __('Hesab'), 'url' => lroute('front.account')], ['title' => __('Giriş'), 'url' => lroute('front.login')]]])
@include('front.auth._page', ['mode' => 'login'])
@endsection
