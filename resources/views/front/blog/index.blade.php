@extends('front.layouts.app')

@section('title', $category?->meta_title ?: $heading)
@section('meta_description', $category?->meta_description)

@section('content')
@include('front.partials.breadcrumbs', ['items' => array_filter([
    ['title' => setting_t('blog.title') ?: __('Bloq'), 'url' => lroute('front.blog')],
    $category ? ['title' => $category->name, 'url' => $category->url()] : null,
])])
<h1 class="title page-title"><span>{{ $heading }}</span></h1>
<div class="container blog-home">
  <div class="row">
    <div id="content">
      @if($category?->description)
        <div class="blog-category-description">{!! $category->description !!}</div>
      @endif
      @if($posts->isEmpty())
        <p>{{ __('Hələ bloq yazısı yoxdur.') }}</p>
        <div class="buttons">
          <div class="pull-right"><a href="{{ lroute('front.home') }}" class="btn btn-primary">{{ __('Davam et') }}</a></div>
        </div>
      @else
        <div class="main-posts post-grid">
          @foreach($posts as $post)
            @include('front.blog.card', ['post' => $post])
          @endforeach
        </div>
        <div class="row pagination-results">
          <div class="col-sm-6 text-left">{{ $posts->links() }}</div>
        </div>
      @endif
    </div>
    @include('front.blog.sidebar')
  </div>
</div>
@endsection
