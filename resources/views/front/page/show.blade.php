@extends('front.layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description)

@section('content')
@include('front.partials.breadcrumbs', ['items' => [['title' => $page->title, 'url' => $page->url()]]])
<h1 class="title page-title"><span>{{ $page->title }}</span></h1>
<div id="information-information" class="container">
  <div class="row">
    <div id="content" class="{{ $page->template === 'full-width' ? 'col-sm-12' : 'col-sm-9' }}">
      @if($page->content)
        <div class="content">{!! $page->content !!}</div>
      @endif
      @include('front.page.blocks', ['blocks' => $page->blocks])
    </div>
    @if($page->template !== 'full-width')
      @include('front.partials.column-right')
    @endif
  </div>
</div>
@endsection
