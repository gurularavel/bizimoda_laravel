@extends('front.layouts.popup', ['popupClass' => 'popup-register'])

@section('content')
@include('front.auth._popup', ['tab' => 'register'])
@endsection
