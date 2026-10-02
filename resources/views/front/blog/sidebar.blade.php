<aside id="column-right" class="side-column">
  <div class="grid-rows">
    <div class="grid-row grid-row-column-right-1">
      <div class="grid-cols">
        <div class="grid-col grid-col-column-right-1-1">
          <div class="grid-items">
            @if($blogCategories->isNotEmpty())
              <div class="grid-item">
                <div class="module module-blog_categories">
                  <h3 class="title module-title">{{ __('Kateqoriyalar') }}</h3>
                  <div class="accordion-menu accordion-menu-126">
                    <ul class="j-menu">
                      <li class="menu-item"><a href="{{ lroute('front.blog') }}"><span class="links-text">{{ __('Bütün yazılar') }}</span></a></li>
                      @foreach($blogCategories as $bc)
                        <li class="menu-item {{ isset($category) && $category?->id === $bc->id ? 'active' : '' }}"><a href="{{ $bc->url() }}"><span class="links-text">{{ $bc->name }}</span></a></li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </div>
            @endif
            @if($latestPosts->isNotEmpty())
              <div class="grid-item">
                <div class="module module-blog_posts module-blog_posts-side">
                  <h3 class="title module-title">{{ __('Son yazılar') }}</h3>
                  <div class="module-body">
                    <div class="post-list">
                      @foreach($latestPosts as $lp)
                        <div class="post-layout" style="display:flex;gap:10px;margin-bottom:12px">
                          <a href="{{ $lp->url() }}"><img src="{{ thumb($lp->cover, 70, 70, 'cover') }}" width="70" height="70" alt="{{ $lp->title }}"/></a>
                          <div class="caption">
                            <div class="name"><a href="{{ $lp->url() }}">{{ $lp->title }}</a></div>
                            @if($lp->published_at)<small class="p-date">{{ $lp->published_at->format('d.m.Y') }}</small>@endif
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</aside>
