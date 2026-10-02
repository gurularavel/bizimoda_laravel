<div class="post-layout">
  <div class="post-thumb">
    <div class="image">
      <a href="{{ $post->url() }}"><img src="{{ thumb($post->cover, 400, 260, 'cover') }}" alt="{{ $post->title }}" title="{{ $post->title }}" width="400" height="260" class="img-responsive" loading="lazy"/></a>
    </div>
    <div class="caption">
      <div class="post-stats">
        @if($post->published_at)<span class="p-date">{{ $post->published_at->translatedFormat('d F Y') }}</span>@endif
        <span class="p-view">{{ $post->views }}</span>
      </div>
      <div class="name"><a href="{{ $post->url() }}">{{ $post->title }}</a></div>
      <div class="description">{{ \Illuminate\Support\Str::limit(strip_tags((string) ($post->excerpt ?: $post->content)), 160) }}</div>
      <div class="button-group">
        <a class="btn btn-read-more" href="{{ $post->url() }}"><span class="btn-text">{{ __('Ətraflı oxu') }}</span></a>
      </div>
    </div>
  </div>
</div>
