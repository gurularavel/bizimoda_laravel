@extends('front.layouts.app')

@section('content')
@include('front.partials.breadcrumbs', ['items' => array_merge([['title' => __('Hesab'), 'url' => lroute('front.account')]], $crumbs ?? [])])
<h1 class="title page-title"><span>@yield('heading')</span></h1>
<div id="account-account" class="container">
  <div class="row">
    <div id="content" class="col-sm-9">
      @yield('account')
    </div>
    @include('front.partials.column-right')
  </div>
</div>
@endsection
