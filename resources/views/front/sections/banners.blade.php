@php $items = $section->data['items'] ?? []; @endphp
@if($items)
<div class="module module-banners module-banners-304">
  @if($section->title)
    <h3 class="title module-title">{{ $section->title }}</h3>
  @endif
  <div class="module-body" style="display:flex;flex-wrap:wrap;gap:20px">
    @foreach($items as $banner)
      <div class="module-item module-item-{{ $loop->iteration }}" style="flex:1 1 {{ floor(100 / max(1, count($items))) - 2 }}%">
        <a @if(! empty($banner['link'])) href="{{ $banner['link'] }}" @endif>
          <img src="{{ image_url($banner['image'] ?? null) }}" alt="{{ tr($banner['alt'] ?? '') }}" style="width:100%;height:auto"/>
        </a>
      </div>
    @endforeach
  </div>
</div>
@endif
