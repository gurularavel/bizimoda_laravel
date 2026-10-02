@extends('front.layouts.app')

@section('title', __('Yeni şifrə'))

@section('content')
@include('front.auth._page', ['mode' => 'reset'])
@endsection
