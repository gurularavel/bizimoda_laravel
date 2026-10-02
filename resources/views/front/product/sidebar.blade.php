@php
    // Aktiv kateqoriya və onun bütün valideynləri (vurğulamaq və uyğun otağı açıq göstərmək üçün)
    $activeIds = $active ? $active->ancestors()->pluck('id')->push($active->id)->all() : [];
@endphp
<aside id="column-left" class="side-column">
  <nav class="bz-side" aria-label="{{ __('Kateqoriyalar') }}">
    <div class="bz-side__title">{{ __('Kateqoriyalar') }}</div>
    @foreach($categories as $root)
      @php $rootActive = in_array($root->id, $activeIds, true); @endphp
      @if($root->children->isEmpty())
        <a href="{{ $root->url() }}" class="bz-side__root bz-side__root--link {{ $rootActive ? 'is-active' : '' }}">{{ $root->name }}</a>
      @else
        <details class="bz-side__group" @if($rootActive) open @endif>
          <summary class="bz-side__root {{ $rootActive ? 'is-active' : '' }}">
            <span>{{ $root->name }}</span>
            <svg class="bz-side__chev" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </summary>
          <div class="bz-side__body">
            <a href="{{ $root->url() }}" class="bz-side__all">{{ __('Hamısına bax') }}</a>
            @foreach($root->children as $child)
              @if($child->children->isEmpty())
                <a href="{{ $child->url() }}" class="bz-side__link {{ in_array($child->id, $activeIds, true) ? 'is-active' : '' }}">{{ $child->name }}</a>
              @else
                <div class="bz-side__section">
                  <a href="{{ $child->url() }}" class="bz-side__heading {{ in_array($child->id, $activeIds, true) ? 'is-active' : '' }}">{{ $child->name }}</a>
                  @foreach($child->children as $leaf)
                    <a href="{{ $leaf->url() }}" class="bz-side__link {{ in_array($leaf->id, $activeIds, true) ? 'is-active' : '' }}" @if($active && $leaf->id === $active->id) aria-current="page" @endif>{{ $leaf->name }}</a>
                  @endforeach
                </div>
              @endif
            @endforeach
          </div>
        </details>
      @endif
    @endforeach
  </nav>
</aside>
