@php
    $hasChildren = ! empty($node['children']);
    $collapseId = 'collapse-main-'.$node['id'];
    $target = $node['target'] ? 'target="_blank"' : '';
@endphp
@if($node['display'] === 'flyout' && $hasChildren)
  <li class="menu-item main-menu-item main-menu-item-{{ $index }} dropdown flyout drop-menu {{ $node['css_class'] }}" data-is-open>
    <a @if($node['url']) href="{{ $node['url'] }}" @endif class="dropdown-toggle" data-toggle="dropdown" {!! $target !!}>
      <span class="links-text">{{ $node['title'] }}</span>
      <span class="open-menu collapsed" data-toggle="collapse" data-target="#{{ $collapseId }}"><i class="fa fa-plus"></i></span>
    </a>
    <div class="dropdown-menu j-dropdown " id="{{ $collapseId }}">
      <div class="flyout-menu flyout-menu-323">
        <ul class="j-menu">
          @foreach($node['children'] as $j => $child)
            @include('front.partials.menu.flyout-item', ['node' => $child, 'index' => $j + 1])
          @endforeach
        </ul>
      </div>
    </div>
  </li>
@elseif($node['display'] === 'mega' && $hasChildren)
  <li class="menu-item main-menu-item main-menu-item-{{ $index }} dropdown mega-menu {{ $node['css_class'] }}">
    <a @if($node['url']) href="{{ $node['url'] }}" @endif class="dropdown-toggle" {!! $target !!}>
      <span class="links-text">{{ $node['title'] }}</span>
      <span class="open-menu collapsed" data-toggle="collapse" data-target="#{{ $collapseId }}"><i class="fa fa-plus"></i></span>
    </a>
    <div class="dropdown-menu j-dropdown " id="{{ $collapseId }}">
      @include('front.partials.menu.mega-content', ['node' => $node])
    </div>
  </li>
@elseif($hasChildren)
  <li class="menu-item main-menu-item main-menu-item-{{ $index }} multi-level dropdown drop-menu {{ $node['css_class'] }}">
    <a @if($node['url']) href="{{ $node['url'] }}" @endif class="dropdown-toggle" {!! $target !!}>
      <span class="links-text">{{ $node['title'] }}</span>
      <span class="open-menu collapsed" data-toggle="collapse" data-target="#{{ $collapseId }}"><i class="fa fa-plus"></i></span>
    </a>
    <div class="dropdown-menu j-dropdown" id="{{ $collapseId }}">
      <ul class="j-menu">
        @foreach($node['children'] as $child)
          <li class="menu-item main-menu-item-{{ $index }}-{{ $loop->iteration }}">
            <a @if($child['url']) href="{{ $child['url'] }}" @endif @if($child['target']) target="_blank" @endif><span class="links-text">{{ $child['title'] }}</span></a>
          </li>
        @endforeach
      </ul>
    </div>
  </li>
@else
  <li class="menu-item main-menu-item main-menu-item-{{ $index }} multi-level drop-menu {{ $node['css_class'] }}">
    <a @if($node['url']) href="{{ $node['url'] }}" @endif {!! $target !!}>
      <span class="links-text">{{ $node['title'] }}</span>
    </a>
  </li>
@endif
