@php $current = $languageLinks[$currentLocale] ?? reset($languageLinks); @endphp
<div id="language" class="language">
  {{-- Journal3 common.js .language-select klikində formu göndərir; LegacyController redirect_{code} ünvanına yönləndirir --}}
  <form action="ajax/common/language/language" method="post" enctype="multipart/form-data" id="form-language">
    @csrf
    <div class="dropdown drop-menu">
      <button type="button" class="dropdown-toggle" data-toggle="dropdown">
        <span class="language-flag-title">
          <span class="symbol"><img src="{{ $current['flag'] }}" width="16" height="11" alt="{{ $current['name'] }}" title="{{ $current['name'] }}"/></span>
          <span class="language-title">{{ $current['name'] }}</span>
        </span>
      </button>
      <div class="dropdown-menu j-dropdown">
        <ul class="j-menu">
          @foreach($languageLinks as $code => $link)
            <li>
              <a class="language-select" data-name="{{ $code }}" href="{{ $link['url'] }}">
                <span class="language-flag"><img src="{{ $link['flag'] }}" width="16" height="11" alt="{{ $link['name'] }}" title="{{ $link['name'] }}"/></span>
                <span class="language-title-dropdown">{{ $link['name'] }}</span>
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
    <input type="hidden" name="code" value=""/>
    @foreach($languageLinks as $code => $link)
      <input type="hidden" name="redirect_{{ $code }}" value="{{ $link['url'] }}"/>
    @endforeach
  </form>
</div>
