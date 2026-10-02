@extends('front.layouts.app')

@section('title', $post->meta_title ?: $post->title)
@section('meta_description', $post->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $post->excerpt), 160))
@section('og_image', thumb($post->cover, 1200, 630, 'cover'))

@section('content')
@include('front.partials.breadcrumbs', ['items' => array_filter([
    ['title' => setting_t('blog.title') ?: __('Bloq'), 'url' => lroute('front.blog')],
    $post->category ? ['title' => $post->category->name, 'url' => $post->category->url()] : null,
    ['title' => $post->title, 'url' => $post->url()],
])])
<h1 class="title page-title"><span>{{ $post->title }}</span></h1>
<div class="container">
  <div class="row">
    <div id="content" class="blog-post">
      <div class="post-details">
        @if($post->cover)
          <div class="post-image"><img src="{{ thumb($post->cover, 1000, 560, 'cover') }}" alt="{{ $post->title }}" class="img-responsive"/></div>
        @endif
        <div class="post-stats">
          @if($post->author)<span class="p-author">{{ $post->author->name }}</span>@endif
          @if($post->published_at)<span class="p-date">{{ $post->published_at->translatedFormat('d F Y') }}</span>@endif
          @if($post->category)<span class="p-category"><a href="{{ $post->category->url() }}">{{ $post->category->name }}</a></span>@endif
          <span class="p-view">{{ $post->views }}</span>
        </div>
        <div class="post-content">{!! $post->content !!}</div>
        @if($tags = $post->tagList())
          <div class="tags">
            <span class="tags-title">{{ __('Teqlər') }}:</span>
            @foreach($tags as $tag)<a href="{{ lroute('front.blog', ['tag' => $tag]) }}">{{ $tag }}</a>@endforeach
          </div>
        @endif
      </div>
      @if($related->isNotEmpty())
        <h3 class="title module-title">{{ __('Oxşar yazılar') }}</h3>
        <div class="main-posts post-grid">
          @foreach($related as $r)
            @include('front.blog.card', ['post' => $r])
          @endforeach
        </div>
      @endif
    </div>
    @include('front.blog.sidebar', ['category' => $post->category])
  </div>
</div>
@endsection

@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@'.'context' => 'http://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post->title,
    'image' => $post->cover ? image_url($post->cover) : null,
    'datePublished' => $post->published_at?->toIso8601String(),
    'dateModified' => $post->updated_at?->toIso8601String(),
    'mainEntityOfPage' => $post->url(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
