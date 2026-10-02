@foreach($nodes as $node)
  @php $childCount = $node->children->count(); @endphp
  {{-- 5 və daha çox alt kateqoriyası olanlar standart olaraq yığılı açılır --}}
  <li class="tree-item {{ $node->is_active ? '' : 'inactive' }} {{ $childCount >= 5 ? 'is-collapsed' : '' }}" data-id="{{ $node->id }}">
    <div class="tree-row">
      <i class="bi bi-grip-vertical drag-handle"></i>
      @if($childCount)
        <button type="button" class="tree-toggle js-tree-toggle" title="Alt elementləri aç / yığ"><i class="bi bi-chevron-down"></i></button>
      @else
        <span class="tree-toggle-spacer"></span>
      @endif
      @if($node->image)<img src="{{ thumb($node->image, 40, 40) }}" class="thumb-40" alt="">@endif
      <a href="{{ route('admin.categories.edit', $node) }}" class="fw-semibold text-decoration-none">{{ $node->name }}</a>
      @if($childCount)<span class="badge rounded-pill bg-light text-secondary border tree-count" title="Alt kateqoriya sayı">{{ $childCount }}</span>@endif
      <span class="meta">/{{ $node->getTranslation('slug', \App\Support\Locales::default()) }}</span>
      <span class="badge badge-soft" title="Məhsul sayı (əsas kateqoriya kimi / ümumi)">{{ $node->main_products_count }} / {{ $node->products_count }}</span>
      @unless($node->is_active)<span class="badge bg-secondary">deaktiv</span>@endunless
      @unless($node->show_in_filter)<span class="badge bg-light text-muted" title="Filtr panelində göstərilmir"><i class="bi bi-funnel"></i> gizli</span>@endunless
      <div class="ms-auto d-flex gap-1">
        <a href="{{ $node->url() }}" target="_blank" class="btn btn-sm btn-light" title="Saytda bax"><i class="bi bi-eye"></i></a>
        <a href="{{ route('admin.categories.create', ['parent_id' => $node->id]) }}" class="btn btn-sm btn-light" title="Alt kateqoriya əlavə et"><i class="bi bi-plus"></i></a>
        <a href="{{ route('admin.categories.edit', $node) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
        <form method="post" action="{{ route('admin.categories.destroy', $node) }}" data-confirm="Kateqoriya silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
      </div>
    </div>
    <ul>@include('admin.categories._branch', ['nodes' => $node->children])</ul>
  </li>
@endforeach
