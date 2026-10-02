@extends('admin.layouts.app')

@section('title', $category->exists ? 'Kateqoriya: '.$category->name : 'Yeni kateqoriya')

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
  @csrf
  @if($category->exists) @method('PUT') @endif

  <ul class="nav nav-tabs mb-3 js-hash-tabs">
    <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general">Ümumi</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-filters">Filtrlər</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo">SEO</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tab-general">
      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card"><div class="card-body">
            <x-admin.trans name="name" label="Ad" :model="$category" required />
            <x-admin.trans name="description" label="Təsvir" :model="$category" type="editor" />
          </div></div>
        </div>
        <div class="col-lg-4">
          <div class="card"><div class="card-body">
            <div class="mb-3">
              <label class="form-label">Valideyn kateqoriya</label>
              <select name="parent_id" class="form-select js-select2" data-placeholder="— Əsas səviyyə —">
                <option value="">— Əsas səviyyə —</option>
                @foreach($parents as $id => $label)
                  <option value="{{ $id }}" @selected(old('parent_id', $category->parent_id) == $id)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Sıra</label>
              <input type="number" name="sort" value="{{ old('sort', $category->sort ?? 0) }}" class="form-control">
            </div>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $category->is_active))>
              <label class="form-check-label" for="is_active">Aktiv</label>
            </div>
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" name="show_in_filter" value="1" id="show_in_filter" @checked(old('show_in_filter', $category->show_in_filter))>
              <label class="form-check-label" for="show_in_filter">Valideynin filtr panelində ("Altbaşlıqlar") göstər</label>
            </div>
            <x-admin.image name="image" label="Şəkil (filtr / menyu ikonu)" :value="$category->image" />
            <x-admin.image name="banner" label="Banner (kateqoriya səhifəsinin yuxarısı)" :value="$category->banner" />
          </div></div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="tab-filters">
      <div class="card"><div class="card-body">
        <p class="text-muted">Bu kateqoriya səhifəsinin sol panelində hansı filtrlərin, hansı ardıcıllıqla görünəcəyini seçin. Siyahı boşdursa və "miras al" aktivdirsə, ən yaxın valideyn kateqoriyanın ayarları istifadə olunur (heç biri yoxdursa: qiymət, altbaşlıqlar, mövcudluq).</p>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" name="filter_inherit" value="1" id="filter_inherit" @checked(old('filter_inherit', $category->filter_inherit))>
          <label class="form-check-label" for="filter_inherit">Öz ayarı yoxdursa valideyndən miras al</label>
        </div>
        <table class="table align-middle">
          <thead><tr><th style="width:30px"></th><th>Filtr tipi</th><th>Xüsusiyyət / Opsiyon</th><th style="width:50px"></th></tr></thead>
          <tbody class="js-filter-rows">
            @foreach(old('filters', $category->filters?->map(fn ($f) => ['type' => $f->type, 'ref_id' => $f->ref_id])->all() ?? []) as $i => $row)
              @include('admin.categories._filter-row', ['i' => $i, 'row' => $row])
            @endforeach
          </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-outline-secondary js-add-filter"><i class="bi bi-plus"></i> Filtr əlavə et</button>
        <template id="filter-row-template">@include('admin.categories._filter-row', ['i' => '__I__', 'row' => []])</template>
      </div></div>
    </div>

    <div class="tab-pane fade" id="tab-seo">
      <div class="card"><div class="card-body">
        <x-admin.trans name="slug" label="URL (slug)" :model="$category" help="Boş qoysanız addan avtomatik yaradılacaq." />
        <x-admin.trans name="meta_title" label="Meta başlıq" :model="$category" />
        <x-admin.trans name="meta_description" label="Meta təsvir" :model="$category" type="textarea" />
      </div></div>
    </div>
  </div>

  <div class="sticky-actions d-flex gap-2 mt-3">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Siyahıya qayıt</a>
    @if($category->exists)
      <a href="{{ route('admin.discounts.create', ['scope' => 'categories', 'category' => $category->id]) }}" class="btn btn-outline-danger ms-auto"><i class="bi bi-percent"></i> Bu kateqoriyaya endirim</a>
      <a href="{{ $category->url() }}" target="_blank" class="btn btn-light"><i class="bi bi-eye"></i> Saytda bax</a>
    @endif
  </div>
</form>
@endsection

@push('scripts')
<script>
  (function () {
    var idx = 1000;
    function toggleRef($row) {
      var type = $row.find('.js-filter-type').val();
      $row.find('.js-ref-attribute').toggle(type === 'attribute').prop('disabled', type !== 'attribute');
      $row.find('.js-ref-option').toggle(type === 'option').prop('disabled', type !== 'option');
      $row.find('.js-ref-none').toggle(type !== 'attribute' && type !== 'option');
    }
    $('.js-filter-rows tr').each(function () { toggleRef($(this)); });
    $(document).on('change', '.js-filter-type', function () { toggleRef($(this).closest('tr')); });
    $('.js-add-filter').on('click', function () {
      var html = $('#filter-row-template').html().replace(/__I__/g, idx++);
      var $row = $(html);
      $('.js-filter-rows').append($row);
      toggleRef($row);
    });
    $(document).on('click', '.js-remove-row', function () { $(this).closest('tr').remove(); });
    new Sortable(document.querySelector('.js-filter-rows'), { handle: '.drag-handle', animation: 150 });
  })();
</script>
@endpush
