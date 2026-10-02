@extends('front.layouts.popup', ['popupClass' => 'popup-login'])

@section('content')
@include('front.auth._popup', ['tab' => 'login'])
@endsection
