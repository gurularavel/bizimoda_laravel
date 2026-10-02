<header class="header-classic">
  <div class="header header-classic header-lg">
    <div class="top-bar navbar-nav">
      <div class="top-menu top-menu-2">
        <ul class="j-menu">
          @foreach($menus['top'] as $i => $item)
            <li class="menu-item top-menu-item top-menu-item-{{ $i + 2 }}">
              <a @if($item['url']) href="{{ $item['url'] }}" @endif @if($item['target']) target="_blank" @endif><span class="links-text">{{ $item['title'] }}</span></a>
            </li>
          @endforeach
          {{-- Journal teması sonuncu elementi mərkəzə (absolute) qoyur; orijinaldakı kimi boş element --}}
          <li class="menu-item top-menu-item top-menu-item-{{ count($menus['top']) + 2 }}">
            <a><span class="links-text">{{ setting_t('site.topbar_center_text') }}</span></a>
          </li>
        </ul>
      </div>

      <div class="language-currency top-menu">
        <div class="desktop-language-wrapper">
          @include('front.partials.language')
        </div>
        <div class="desktop-currency-wrapper"></div>
      </div>
      <div class="third-menu"></div>
    </div>

    <div class="mid-bar navbar-nav">
      <div class="desktop-logo-wrapper">
        <div id="logo">
          <a href="{{ lroute('front.home') }}">
            @php $logo = setting('site.logo') ? image_url(setting('site.logo')) : asset('images/logo.png'); @endphp
            <img src="{{ $logo }}" srcset="{{ $logo }} 1x, {{ $logo }} 2x" width="136" height="36" alt="{{ setting('site.name', 'bizimoda') }}" title="{{ setting('site.name', 'bizimoda') }}"/>
          </a>
        </div>
      </div>

      <div class="desktop-search-wrapper full-search default-search-wrapper">
        <div class="bar0"> </div>
        <div id="search" class="dropdown">
          <button class="dropdown-toggle search-trigger" data-toggle="dropdown"></button>
          <div class="dropdown-menu j-dropdown">
            <div class="header-search">
              <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Axtar...') }}" class="search-input" data-category_id=""/>
              <button type="button" class="search-button" data-search-url="{{ lroute('front.search') }}?search="></button>
            </div>
          </div>
        </div>
      </div>

      <div class="classic-cart-wrapper">
        <div class="top-menu secondary-menu">
          <div class="top-menu top-menu-240">
            <ul class="j-menu">
              @if($customer)
                <li class="menu-item top-menu-item top-menu-item-3">
                  <a href="{{ lroute('front.account') }}" class="bz-hicon">@include('front.partials.icon', ['name' => 'user'])<span class="links-text">{{ $customer->first_name ?: __('Hesabım') }}</span></a>
                </li>
                <li class="menu-item top-menu-item top-menu-item-7">
                  <a href="javascript:void(0)" class="bz-hicon" onclick="document.getElementById('form-logout').submit()">@include('front.partials.icon', ['name' => 'logout'])<span class="links-text">{{ __('Çıxış') }}</span></a>
                </li>
              @else
                @forelse($menus['account'] as $i => $item)
                  {{-- CSS ikonları top-menu-item-3 (qeydiyyat) və -7 (giriş) siniflərinə bağlıdır --}}
                  <li class="menu-item top-menu-item top-menu-item-{{ [3, 7][$i] ?? $i + 8 }}">
                    <a @if($item['url']) href="{{ $item['url'] }}" @endif class="bz-hicon">@include('front.partials.icon', ['name' => $i === 0 ? 'user-plus' : 'user'])<span class="links-text">{{ $item['title'] }}</span></a>
                  </li>
                @empty
                  <li class="menu-item top-menu-item top-menu-item-3">
                    <a href="javascript:open_register_popup()" class="bz-hicon">@include('front.partials.icon', ['name' => 'user-plus'])<span class="links-text">{{ __('Qeydiyyat') }}</span></a>
                  </li>
                  <li class="menu-item top-menu-item top-menu-item-7">
                    <a href="javascript:open_login_popup()" class="bz-hicon">@include('front.partials.icon', ['name' => 'user'])<span class="links-text">{{ __('Daxil ol') }}</span></a>
                  </li>
                @endforelse
              @endif
            </ul>
          </div>
        </div>
        @if($customer)
          <form id="form-logout" action="{{ lroute('front.logout') }}" method="post" style="display:none">@csrf</form>
        @endif
        <div class="desktop-cart-wrapper default-cart-wrapper">
          @include('front.partials.cart-dropdown')
        </div>
      </div>
    </div>

    <div class="desktop-main-menu-wrapper menu-default  navbar-nav">
      <div class="menu-trigger menu-item main-menu-item"><ul class="j-menu"><li><a>{{ __('Menyu') }}</a></li></ul></div>
      <div id="main-menu" class="main-menu main-menu-3">
        <ul class="j-menu">
          @foreach($menus['main'] as $i => $node)
            @include('front.partials.menu.main-item', ['node' => $node, 'index' => $i + 1])
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  <div class="mobile-header mobile-default mobile-1">
    <div class="mobile-top-bar">
      <div class="mobile-top-menu-wrapper">
        <div class="top-menu top-menu-13">
          <ul class="j-menu">
            @foreach($menus['mobile_top'] as $i => $item)
              <li class="menu-item top-menu-item top-menu-item-{{ $i + 2 }}">
                <a @if($item['url']) href="{{ $item['url'] }}" @endif><span class="links-text">{{ $item['title'] }}</span></a>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
      <div class="language-currency top-menu">
        <div class="mobile-currency-wrapper"></div>
        <div class="mobile-language-wrapper"></div>
      </div>
    </div>
    <div class="mobile-bar sticky-bar">
      <div class="mobile-logo-wrapper"></div>
      <div class="mobile-bar-group">
        <div class="menu-trigger"></div>
        <div class="mobile-search-wrapper mini-search"></div>
        <div class="mobile-cart-wrapper mini-cart"></div>
      </div>
    </div>
  </div>
</header>
