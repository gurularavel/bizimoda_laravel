@extends('admin.layouts.app')

@section('title', $page->exists ? 'Səhifə: '.$page->title : 'Yeni səhifə')

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
  @csrf
  @if($page->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card mb-3"><div class="card-body">
        <x-admin.trans name="title" label="Başlıq" :model="$page" required />
        <x-admin.trans name="content" label="Əsas məzmun" :model="$page" type="editor" />
      </div></div>

      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="bi bi-grid-1x2"></i> Səhifə blokları <small class="text-muted fw-normal">— sürükləyib sıralayın</small></span>
          <div class="dropdown">
            <button type="button" class="btn btn-sm btn-brand dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-plus"></i> Blok əlavə et</button>
            <ul class="dropdown-menu dropdown-menu-end">
              @foreach(\App\Models\PageBlock::TYPES as $type => $label)
                <li><a class="dropdown-item js-add-block" href="#" data-type="{{ $type }}">{{ $label }}</a></li>
              @endforeach
            </ul>
          </div>
        </div>
        <div class="card-body js-blocks">
          @foreach($page->blocks ?? [] as $i => $block)
            @include('admin.pages._block', ['i' => $i, 'type' => $block->type, 'data' => $block->data ?? []])
          @endforeach
          <p class="text-muted small js-blocks-empty mb-0" @if(($page->blocks ?? collect())->isNotEmpty()) style="display:none" @endif>Blok yoxdur. "Blok əlavə et" ilə banner, məhsul karuseli, FAQ və s. əlavə edin.</p>
        </div>
      </div>
      @foreach(array_keys(\App\Models\PageBlock::TYPES) as $type)
        <template id="block-tpl-{{ $type }}">@include('admin.pages._block', ['i' => '__I__', 'type' => $type, 'data' => []])</template>
      @endforeach
    </div>
    <div class="col-lg-4">
      <div class="card mb-3"><div class="card-body">
        <div class="mb-3">
          <label class="form-label">Şablon</label>
          <select name="template" class="form-select">
            @foreach(\App\Models\Page::TEMPLATES as $k => $v)<option value="{{ $k }}" @selected(old('template', $page->template) === $k)>{{ $v }}</option>@endforeach
          </select>
          <div class="form-text">"Əlaqə səhifəsi" şablonu ünvan/telefon bloklarını (Parametrlər → Əlaqə) və əlaqə formasını göstərir.</div>
        </div>
        <div class="mb-3"><label class="form-label">Sıra</label><input type="number" name="sort" value="{{ old('sort', $page->sort ?? 0) }}" class="form-control"></div>
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked(old('is_active', $page->is_active))><label class="form-check-label" for="ia">Aktiv</label></div>
      </div></div>
      <div class="card"><div class="card-body">
        <x-admin.trans name="slug" label="URL (slug)" :model="$page" help="Boş qoysanız başlıqdan yaradılır." />
        <x-admin.trans name="meta_title" label="Meta başlıq" :model="$page" />
        <x-admin.trans name="meta_description" label="Meta təsvir" :model="$page" type="textarea" />
      </div></div>
    </div>
  </div>
  <div class="sticky-actions d-flex gap-2 mt-3">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-light">Siyahıya qayıt</a>
    @if($page->exists)<a href="{{ $page->url() }}" target="_blank" class="btn btn-light ms-auto"><i class="bi bi-eye"></i> Saytda bax</a>@endif
  </div>
</form>
@endsection

@push('scripts')
<script>
  (function () {
    var idx = 1000;
    $('.js-add-block').on('click', function (e) {
      e.preventDefault();
      var html = $('#block-tpl-' + $(this).data('type')).html().replace(/__I__/g, idx++);
      var $block = $(html);
      $('.js-blocks').append($block);
      $('.js-blocks-empty').hide();
      adminInitEditors($block);
      adminInitSelects($block);
    });
    $(document).on('click', '.js-remove-block', function () {
      var $b = $(this).closest('.page-block');
      $b.find('textarea.js-editor').each(function () { if (tinymce.get(this.id)) { tinymce.get(this.id).remove(); } });
      $b.remove();
    });
    new Sortable(document.querySelector('.js-blocks'), {
      handle: '.drag-handle', animation: 150,
      onStart: function () { tinymce.triggerSave(); },
      onEnd: function (e) {
        $(e.item).find('textarea.js-editor').each(function () { if (tinymce.get(this.id)) { tinymce.get(this.id).remove(); } });
        adminInitEditors(e.item);
      }
    });
  })();
</script>
@endpush
