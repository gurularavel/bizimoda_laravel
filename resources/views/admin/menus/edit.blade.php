@extends('admin.layouts.app')

@section('title', 'Menyu: '.(\App\Models\Menu::LOCATIONS[$menu->key] ?? $menu->name))

@php
    $parentOptions = [];
    $flatten = function ($nodes, $depth) use (&$flatten, &$parentOptions, $labels) {
        foreach ($nodes as $n) {
            $parentOptions[$n->id] = str_repeat('— ', $depth).($n->getTranslation('title', app()->getLocale(), false) ?: ($labels[$n->type][$n->linkable_id] ?? '#'.$n->id));
            $flatten($n->children, $depth + 1);
        }
    };
    $flatten($tree, 0);
@endphp

@section('content')
<div class="row g-3">
  <div class="col-xl-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Menyu strukturu</span>
        <div class="d-flex gap-1">
          <button type="button" class="btn btn-sm btn-light js-tree-expand-all" data-target="#menu-tree" title="Hamısını aç"><i class="bi bi-arrows-expand"></i></button>
          <button type="button" class="btn btn-sm btn-light js-tree-collapse-all" data-target="#menu-tree" title="Hamısını yığ"><i class="bi bi-arrows-collapse"></i></button>
          <button type="button" class="btn btn-sm btn-brand js-new-item"><i class="bi bi-plus-lg"></i> Element əlavə et</button>
        </div>
      </div>
      <div class="card-body">
        @if($menu->key === 'main')
          <div class="alert alert-light small border">
            <b>Meqa menyu necə qurulur:</b> üst səviyyədə <i>Açılan siyahı (flyout)</i> elementi (məs. "Kateqoriyalar") → onun içində <i>Meqa menyu</i> elementləri (otaqlar) →
            onların içində <i>Sütun qrupu</i> elementləri (başlıq, sütun nömrəsi ilə) → qrupun içində adi linklər. Meqa elementə banner şəkli əlavə etmək olar.
            "Alt kateqoriyaları avtomatik göstər" seçilərsə kateqoriyanın alt kateqoriyaları avtomatik sütunlara düzülür.
          </div>
        @endif
        <ul class="tree-list js-tree" id="menu-tree" data-storage-key="menu-{{ $menu->id }}" data-reorder-url="{{ route('admin.menus.reorder', $menu) }}">
          @include('admin.menus._branch', ['nodes' => $tree])
        </ul>
        @if($tree->isEmpty())<p class="text-muted mb-0">Menyu boşdur.</p>@endif
        <div class="text-success small mt-2 d-none js-tree-saved"><i class="bi bi-check2"></i> Sıralama yadda saxlanıldı</div>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="card" id="item-form-card">
      <div class="card-header js-form-title">Yeni element</div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="{{ route('admin.menus.items.store', $menu) }}" id="item-form" data-store-url="{{ route('admin.menus.items.store', $menu) }}" data-update-url="{{ route('admin.menus.items.update', [$menu, '__ID__']) }}">
          @csrf
          <input type="hidden" name="_method" value="POST" id="item-method">

          <div class="mb-3">
            <label class="form-label">Link tipi</label>
            <select name="type" class="form-select js-item-type">
              @foreach(\App\Models\MenuItem::TYPES as $k => $v)<option value="{{ $k }}" @selected(old('type', 'category') === $k)>{{ $v }}</option>@endforeach
            </select>
          </div>

          <div class="mb-3 js-field-linkable">
            <label class="form-label">Hədəf</label>
            <select name="linkable_id" class="form-select js-linkable" data-url="{{ route('admin.menus.linkables') }}"></select>
            <div class="form-text">Başlıq boş qalarsa hədəfin adı (bütün dillərdə) istifadə olunur.</div>
          </div>

          <div class="mb-3 js-field-url">
            <label class="form-label">URL</label>
            <input type="text" name="url" class="form-control js-url" placeholder="">
            <div class="form-text js-url-help"></div>
          </div>

          <x-admin.trans name="title" label="Başlıq" />

          <div class="row g-2">
            <div class="col-md-7">
              <label class="form-label">Görünüş</label>
              <select name="display" class="form-select js-display">
                @foreach(\App\Models\MenuItem::DISPLAYS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label">Sütun №</label>
              <input type="number" name="column" min="1" max="8" value="1" class="form-control js-column">
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">Valideyn element</label>
            <select name="parent_id" class="form-select js-parent">
              <option value="">— Üst səviyyə —</option>
              @foreach($parentOptions as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach
            </select>
          </div>

          <div class="mt-3 js-field-banner">
            <x-admin.image name="banner_image" label="Meqa menyu banneri (sağ sütun)" />
            <input type="text" name="banner_url" class="form-control form-control-sm js-banner-url" placeholder="Banner linki">
          </div>

          <div class="mt-3">
            <div class="form-check form-switch"><input class="form-check-input js-active" type="checkbox" name="is_active" value="1" id="mi-active" checked><label class="form-check-label" for="mi-active">Aktiv</label></div>
            <div class="form-check form-switch"><input class="form-check-input js-blank" type="checkbox" name="target_blank" value="1" id="mi-blank"><label class="form-check-label" for="mi-blank">Yeni pəncərədə aç</label></div>
            <div class="form-check form-switch js-field-auto"><input class="form-check-input js-auto" type="checkbox" name="auto_children" value="1" id="mi-auto"><label class="form-check-label" for="mi-auto">Alt kateqoriyaları avtomatik göstər</label></div>
          </div>
          <div class="mt-3">
            <label class="form-label small">CSS sinfi (istəyə bağlı)</label>
            <input type="text" name="css_class" class="form-control form-control-sm js-css">
          </div>

          <div class="mt-3 d-flex gap-2">
            <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button>
            <button type="button" class="btn btn-light js-new-item">Təmizlə</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@foreach($items as $item)
  <form id="del-item-{{ $item->id }}" method="post" action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}" data-confirm="Element (və alt elementləri) silinsin?">@csrf @method('DELETE')</form>
@endforeach
@endsection

@push('scripts')
<script>
  (function () {
    var $form = $('#item-form');
    var urlHelps = {
      route: 'Sayt daxili yol, məs: <code>specials</code>, <code>account/orders</code>, <code>blog</code>, <code>contact</code>, <code>cart</code>, <code>javascript:open_login_popup()</code>. Dil prefiksi (/az/) avtomatik əlavə olunur.',
      url: 'Tam ünvan, məs: https://www.instagram.com/bizimoda_'
    };
    var $linkable = $('.js-linkable');

    function initLinkable() {
      if ($linkable.data('select2')) { $linkable.select2('destroy'); }
      $linkable.select2({
        theme: 'bootstrap-5', width: '100%', placeholder: 'Seçin...', dropdownParent: $('#item-form-card'),
        ajax: { url: $linkable.data('url'), dataType: 'json', delay: 200, data: function (p) { return { q: p.term || '', type: $('.js-item-type').val() }; } }
      });
    }

    function applyType() {
      var t = $('.js-item-type').val();
      var hasLinkable = ['category', 'product', 'page', 'blog_category', 'blog_post'].indexOf(t) !== -1;
      $('.js-field-linkable').toggle(hasLinkable);
      $('.js-field-url').toggle(t === 'route' || t === 'url');
      $('.js-url-help').html(urlHelps[t] || '');
      $('.js-field-auto').toggle(t === 'category');
    }
    function applyDisplay() {
      $('.js-field-banner').toggle($('.js-display').val() === 'mega');
    }

    $('.js-item-type').on('change', function () { $linkable.val(null).trigger('change'); applyType(); });
    $('.js-display').on('change', applyDisplay);
    initLinkable(); applyType(); applyDisplay();

    function resetForm() {
      $form[0].reset();
      $form.attr('action', $form.data('store-url'));
      $('#item-method').val('POST');
      $('.js-form-title').text('Yeni element');
      $linkable.empty().val(null).trigger('change');
      $('.image-field .image-preview').html('<i class="bi bi-image text-muted fs-2"></i>');
      applyType(); applyDisplay();
    }
    $('.js-new-item').on('click', function () { resetForm(); $('html,body').animate({ scrollTop: $('#item-form-card').offset().top - 80 }, 200); });

    $(document).on('click', '.js-edit-item', function () {
      var d = $(this).data('item');
      resetForm();
      $form.attr('action', $form.data('update-url').replace('__ID__', d.id));
      $('#item-method').val('PUT');
      $('.js-form-title').text('Redaktə: ' + d.label);
      $('.js-item-type').val(d.type);
      applyType();
      if (d.linkable_id) { $linkable.append(new Option(d.linkable_label || ('#' + d.linkable_id), d.linkable_id, true, true)).trigger('change'); }
      $('.js-url').val(d.url || '');
      $.each(d.title || {}, function (code, val) { $form.find('[name="title[' + code + ']"]').val(val); });
      $('.js-display').val(d.display); applyDisplay();
      $('.js-column').val(d.column);
      $('.js-parent').val(d.parent_id || '');
      $('.js-parent option').prop('disabled', false).filter('[value="' + d.id + '"]').prop('disabled', true);
      $('.js-active').prop('checked', d.is_active);
      $('.js-blank').prop('checked', d.target_blank);
      $('.js-auto').prop('checked', d.auto_children);
      $('.js-css').val(d.css_class || '');
      $('.js-banner-url').val(d.banner_url || '');
      if (d.banner_thumb) { $('.image-field .image-preview').html('<img src="' + d.banner_thumb + '">'); }
      $('html,body').animate({ scrollTop: $('#item-form-card').offset().top - 80 }, 200);
    });

    // Drag-drop ağac
    var $root = $('.js-tree');
    function serialize($ul) {
      return $ul.children('li').map(function () { return { id: $(this).data('id'), children: serialize($(this).children('ul')) }; }).get();
    }
    function save() {
      $.post($root.data('reorder-url'), { tree: JSON.stringify(serialize($root)) }, function () {
        $('.js-tree-saved').removeClass('d-none').delay(1500).queue(function (n) { $(this).addClass('d-none'); n(); });
      });
    }
    $root.find('ul').addBack().each(function () {
      new Sortable(this, { group: 'menu', handle: '.drag-handle', animation: 150, fallbackOnBody: true, swapThreshold: 0.65, onEnd: save });
    });
  })();
</script>
@endpush
