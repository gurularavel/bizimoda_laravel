@php
    // Uşaqlar sütun nömrəsinə görə qruplaşdırılır; "group" — başlıq + linklər, "link" — tək link (başlıqsız siyahıya yığılır)
    $columns = collect($node['children'])->groupBy(fn ($c) => max(1, (int) $c['column']))->sortKeys();
    $hasBanner = ! empty($node['banner_image']);
    $colCount = max(1, $columns->count() + ($hasBanner ? 1 : 0));
    $width = round(100 / $colCount, 6);
@endphp
<div class="mega-menu-content">
  <div class="grid-rows">
    <div class="grid-row grid-row-1">
      <div class="grid-cols">
        @foreach($columns as $colNo => $items)
          <div class="grid-col grid-col-{{ $loop->iteration + 1 }}" style="width: {{ $width }}%">
            <div class="grid-items">
              @php $loose = $items->filter(fn ($i) => $i['display'] !== 'group' && empty($i['children'])); @endphp
              @foreach($items->filter(fn ($i) => $i['display'] === 'group' || ! empty($i['children'])) as $group)
                <div class="grid-item grid-item-{{ $loop->iteration }}">
                  <div class="links-menu links-menu-322">
                    <h3 class="title module-title">
                      @if($group['url'])<a href="{{ $group['url'] }}">{{ $group['title'] }}</a>@else{{ $group['title'] }}@endif
                    </h3>
                    <ul class="module-body">
                      @foreach($group['children'] as $link)
                        <li class="menu-item links-menu-item links-menu-item-{{ $loop->iteration }}">
                          <a @if($link['url']) href="{{ $link['url'] }}" @endif @if($link['target']) target="_blank" @endif>
                            <span class="links-text">{{ $link['title'] }}</span>
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              @endforeach
              @if($loose->isNotEmpty())
                <div class="grid-item grid-item-loose">
                  <div class="links-menu links-menu-322">
                    <ul class="module-body">
                      @foreach($loose as $link)
                        <li class="menu-item links-menu-item links-menu-item-{{ $loop->iteration }}">
                          <a @if($link['url']) href="{{ $link['url'] }}" @endif @if($link['target']) target="_blank" @endif>
                            <span class="links-text">{{ $link['title'] }}</span>
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              @endif
            </div>
          </div>
        @endforeach
        @if($hasBanner)
          <div class="grid-col grid-col-banner" style="width: {{ $width }}%">
            <div class="grid-items">
              <div class="grid-item grid-item-1">
                <div class="module module-banners module-banners-325">
                  <div class="module-body">
                    <div class="module-item module-item-1">
                      <a @if($node['banner_url']) href="{{ $node['banner_url'] }}" @endif>
                        <img src="{{ thumb($node['banner_image'], 385, 425) }}" alt="{{ $node['title'] }}" width="385" height="425"/>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
