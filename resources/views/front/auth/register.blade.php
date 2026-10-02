@extends('front.layouts.app')

@section('title', __('Qeydiyyat'))

@section('content')
@include('front.partials.breadcrumbs', ['items' => [['title' => __('Hesab'), 'url' => lroute('front.account')], ['title' => __('Qeydiyyat'), 'url' => lroute('front.register')]]])
@include('front.auth._page', ['mode' => 'register'])
@endsection
