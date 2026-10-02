@if($section->payload && $section->payload->isNotEmpty())
<div class="module module-blog_posts module-blog_posts-1">
  @if($section->title)
    <h3 class="title module-title">{{ $section->title }}</h3>
  @endif
  <div class="module-body">
    <div class="post-grid">
      @foreach($section->payload as $post)
        @include('front.blog.card', ['post' => $post])
      @endforeach
    </div>
  </div>
</div>
@endif
