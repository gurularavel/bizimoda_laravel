@php
    $hasChildren = ! empty($node['children']);
    $collapseId = 'collapse-fly-'.$node['id'];
@endphp
@if($hasChildren && in_array($node['display'], ['mega', 'group', 'flyout']))
  <li class="menu-item flyout-menu-item flyout-menu-item-{{ $index }} dropdown mega-menu {{ $node['css_class'] }}">
    <a @if($node['url']) href="{{ $node['url'] }}" @endif class="dropdown-toggle" @if($node['target']) target="_blank" @endif>
      <span class="links-text">{{ $node['title'] }}</span>
      <span class="open-menu collapsed" data-toggle="collapse" data-target="#{{ $collapseId }}"><i class="fa fa-plus"></i></span>
    </a>
    <div class="dropdown-menu j-dropdown " id="{{ $collapseId }}">
      @include('front.partials.menu.mega-content', ['node' => $node])
    </div>
  </li>
@elseif($hasChildren)
  <li class="menu-item flyout-menu-item flyout-menu-item-{{ $index }} dropdown drop-menu multi-level {{ $node['css_class'] }}">
    <a @if($node['url']) href="{{ $node['url'] }}" @endif class="dropdown-toggle" @if($node['target']) target="_blank" @endif>
      <span class="links-text">{{ $node['title'] }}</span>
      <span class="open-menu collapsed" data-toggle="collapse" data-target="#{{ $collapseId }}"><i class="fa fa-plus"></i></span>
    </a>
    <div class="dropdown-menu j-dropdown" id="{{ $collapseId }}">
      <ul class="j-menu">
        @foreach($node['children'] as $child)
          <li class="menu-item"><a @if($child['url']) href="{{ $child['url'] }}" @endif><span class="links-text">{{ $child['title'] }}</span></a></li>
        @endforeach
      </ul>
    </div>
  </li>
@else
  <li class="menu-item flyout-menu-item flyout-menu-item-{{ $index }} multi-level {{ $node['css_class'] }}">
    <a @if($node['url']) href="{{ $node['url'] }}" @endif @if($node['target']) target="_blank" @endif>
      <span class="links-text">{{ $node['title'] }}</span>
    </a>
  </li>
@endif
