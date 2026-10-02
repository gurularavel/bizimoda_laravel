<div class="module module-html">
  @if($section->title)
    <h3 class="title module-title">{{ $section->title }}</h3>
  @endif
  <div class="module-body">{!! tr($section->data['html'] ?? '') !!}</div>
</div>
