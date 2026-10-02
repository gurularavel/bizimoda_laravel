@foreach($nodes as $node)
  @php $childCount = $node->children->count(); @endphp
  @php
      $titleText = $node->getTranslation('title', app()->getLocale(), false);
      $linkLabel = $labels[$node->type][$node->linkable_id] ?? null;
      $label = $titleText ?: ($linkLabel ?: '#'.$node->id);
      $payload = [
          'id' => $node->id, 'label' => $label, 'type' => $node->type, 'linkable_id' => $node->linkable_id, 'linkable_label' => $linkLabel,
          'url' => $node->url, 'title' => $node->getTranslations('title'), 'display' => $node->display, 'column' => $node->column,
          'parent_id' => $node->parent_id, 'is_active' => $node->is_active, 'target_blank' => $node->target_blank,
          'auto_children' => $node->auto_children, 'css_class' => $node->css_class, 'banner_url' => $node->banner_url,
          'banner_thumb' => $node->banner_image ? thumb($node->banner_image, 160, 160) : null,
      ];
      $displayColors = ['flyout' => 'bg-warning text-dark', 'mega' => 'bg-danger', 'group' => 'bg-info text-dark', 'link' => 'badge-soft'];
  @endphp
  {{-- 5 və daha çox alt elementi olanlar standart olaraq yığılı açılır --}}
  <li class="tree-item {{ $node->is_active ? '' : 'inactive' }} {{ $childCount >= 5 ? 'is-collapsed' : '' }}" data-id="{{ $node->id }}">
    <div class="tree-row">
      <i class="bi bi-grip-vertical drag-handle"></i>
      @if($childCount)
        <button type="button" class="tree-toggle js-tree-toggle" title="Alt elementləri aç / yığ"><i class="bi bi-chevron-down"></i></button>
      @else
        <span class="tree-toggle-spacer"></span>
      @endif
      <span class="fw-semibold">{{ $label }}</span>
      @if($childCount)<span class="badge rounded-pill bg-light text-secondary border tree-count" title="Alt element sayı">{{ $childCount }}</span>@endif
      <span class="badge badge-soft">{{ \App\Models\MenuItem::TYPES[$node->type] ?? $node->type }}@if($linkLabel && $titleText): {{ \Illuminate\Support\Str::limit($linkLabel, 25) }}@endif</span>
      @if($node->display !== 'link')<span class="badge {{ $displayColors[$node->display] }}">{{ $node->display }}{{ $node->display === 'group' ? ' · sütun '.$node->column : '' }}</span>@endif
      @if($node->url)<span class="meta">{{ \Illuminate\Support\Str::limit($node->url, 40) }}</span>@endif
      @if($node->target_blank)<i class="bi bi-box-arrow-up-right text-muted" title="Yeni pəncərə"></i>@endif
      @if($node->banner_image)<i class="bi bi-image text-muted" title="Banner var"></i>@endif
      @if($node->auto_children)<span class="badge bg-light text-muted">auto</span>@endif
      <div class="ms-auto d-flex gap-1">
        <button type="button" class="btn btn-sm btn-light js-edit-item" data-item='@json($payload)'><i class="bi bi-pencil"></i></button>
        <button type="submit" form="del-item-{{ $node->id }}" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
      </div>
    </div>
    <ul>@include('admin.menus._branch', ['nodes' => $node->children])</ul>
  </li>
@endforeach
