@extends('admin.layouts.app')

@section('title', $product->exists ? 'Məhsul: '.$product->name : 'Yeni məhsul')

@php
    $type = old('type', $product->type);
    $selectedCategories = old('categories', $product->exists ? $product->categories->pluck('id')->all() : []);
    $productOptions = $product->exists ? $product->productOptions->keyBy('option_id') : collect();
    $selectedAttrValues = old('attributes') ? collect(old('attributes'))->flatten()->all() : ($product->exists ? $product->attributeValues->pluck('id')->all() : []);
    $setRows = old('set_items', $product->exists ? $product->setItems->map(fn ($i) => [
        'component_id' => $i->component_id, 'component_text' => $i->component?->name.' — '.money($i->component?->price),
        'default_qty' => $i->default_qty, 'min_qty' => $i->min_qty, 'max_qty' => $i->max_qty, 'is_required' => $i->is_required,
        'price_override' => $i->price_override, 'old_price_override' => $i->old_price_override,
        'component_price' => $i->component?->price, 'component_old_price' => $i->component?->old_price,
    ])->all() : []);
    if (old('set_items')) {
        $names = \App\Models\Product::query()->whereIn('id', collect($setRows)->pluck('component_id')->filter())->get()->keyBy('id');
        $setRows = collect($setRows)->map(fn ($r) => $r + (isset($names[$r['component_id'] ?? 0]) ? [
            'component_text' => $names[$r['component_id']]->name.' — '.money($names[$r['component_id']]->price),
            'component_price' => $names[$r['component_id']]->price,
            'component_old_price' => $names[$r['component_id']]->old_price,
        ] : []))->all();
    }
@endphp

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" id="product-form">
  @csrf
  @if($product->exists) @method('PUT') @endif
  <input type="hidden" name="_tab" value="" id="current-tab">

  <ul class="nav nav-tabs mb-3 js-hash-tabs">
    <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general">Ümumi</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-categories">Kateqoriyalar</button></li>
    <li class="nav-item js-set-only"><button type="button" class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-set"><i class="bi bi-boxes"></i> Dəst modulları</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-price">Qiymət və stok</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-images">Şəkillər</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-options">Opsiyonlar</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-attributes">Xüsusiyyətlər</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-related">Oxşar məhsullar</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo">SEO</button></li>
  </ul>

  <div class="tab-content">
    {{-- ÜMUMİ --}}
    <div class="tab-pane fade show active" id="tab-general">
      <div class="row g-3">
        <div class="col-lg-8">
          <div class="card"><div class="card-body">
            <x-admin.trans name="name" label="Məhsulun adı" :model="$product" required />
            <x-admin.trans name="short_description" label="Qısa təsvir (kartda görünür)" :model="$product" type="textarea" :rows="2" />
            <x-admin.trans name="dimensions" label="Ölçülər (tab)" :model="$product" type="editor" />
            <x-admin.trans name="description" label="Xüsusiyyətlər / təsvir (tab)" :model="$product" type="editor" />
          </div></div>
        </div>
        <div class="col-lg-4">
          <div class="card mb-3"><div class="card-body">
            <div class="mb-3">
              <label class="form-label">Məhsul tipi</label>
              <select name="type" class="form-select js-type">
                @foreach(\App\Models\Product::TYPES as $k => $v)<option value="{{ $k }}" @selected($type === $k)>{{ $v }}</option>@endforeach
              </select>
              <div class="form-text js-type-help"></div>
            </div>
            <div class="form-check form-switch mb-2 js-module-only">
              <input class="form-check-input" type="checkbox" name="sold_separately" value="1" id="sold_separately" @checked(old('sold_separately', $product->sold_separately))>
              <label class="form-check-label" for="sold_separately">Ayrıca da satılsın (siyahılarda görünsün)</label>
            </div>
            <div class="mb-3">
              <label class="form-label">SKU / Model</label>
              <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Brend</label>
              <select name="brand_id" class="form-select js-select2" data-placeholder="—">
                <option value=""></option>
                @foreach($brands as $id => $name)<option value="{{ $id }}" @selected(old('brand_id', $product->brand_id) == $id)>{{ $name }}</option>@endforeach
              </select>
            </div>
            <x-admin.trans name="label" label="Etiket (məs. Hot, Yeni)" :model="$product" />
            <div class="mb-3">
              <label class="form-label">Sıra</label>
              <input type="number" name="sort" value="{{ old('sort', $product->sort ?? 0) }}" class="form-control">
            </div>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->is_active))>
              <label class="form-check-label" for="is_active">Aktiv</label>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" @checked(old('is_featured', $product->is_featured))>
              <label class="form-check-label" for="is_featured">Seçilmiş (ana səhifə)</label>
            </div>
          </div></div>
          @if($partOfSets->isNotEmpty())
            <div class="card"><div class="card-body">
              <div class="fw-semibold mb-2"><i class="bi bi-boxes"></i> Bu modul aşağıdakı dəstlərdədir:</div>
              @foreach($partOfSets as $item)
                <div><a href="{{ route('admin.products.edit', $item->set_id) }}">{{ $item->set?->name }}</a> <small class="text-muted">(min {{ $item->min_qty }}{{ $item->max_qty ? ', max '.$item->max_qty : '' }})</small></div>
              @endforeach
            </div></div>
          @endif
        </div>
      </div>
    </div>

    {{-- KATEQORİYALAR --}}
    <div class="tab-pane fade" id="tab-categories">
      <div class="card"><div class="card-body">
        <div class="mb-3">
          <label class="form-label">Əsas kateqoriya <span class="text-danger">*</span></label>
          <select name="main_category_id" class="form-select js-select2 js-main-category" required data-placeholder="Seçin...">
            <option value=""></option>
            @foreach($categories as $id => $label)<option value="{{ $id }}" @selected(old('main_category_id', $product->main_category_id) == $id)>{{ $label }}</option>@endforeach
          </select>
          <div class="form-text">Məhsulun URL-i və breadcrumb-ı əsas kateqoriyaya görə qurulur. Əsas kateqoriya avtomatik olaraq aşağıdakı siyahıya da əlavə olunur.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Əlavə kateqoriyalar</label>
          <select name="categories[]" class="form-select js-select2" multiple data-placeholder="Məhsul bu kateqoriyalarda da görünəcək">
            @foreach($categories as $id => $label)<option value="{{ $id }}" @selected(in_array($id, $selectedCategories))>{{ $label }}</option>@endforeach
          </select>
        </div>
      </div></div>
    </div>

    {{-- DƏST MODULLARI --}}
    <div class="tab-pane fade" id="tab-set">
      <div class="card"><div class="card-body">
        <div class="alert alert-info small">
          <i class="bi bi-info-circle"></i> Dəstin qiyməti modulların qiyməti × sayı cəmindən <b>avtomatik</b> formalaşır.
          <b>Min say</b> — müştəri bu moduldan bundan az seçə bilməz (məs. tumba = 2; 1 ədəd almaq mümkün olmayacaq).
          Məcburi olmayan modulu müştəri tamamilə (0) çıxara bilər. <b>Standart say</b> — səhifə açılanda seçilmiş say (0 = seçilməyib).
          Modul qiyməti boş qalarsa modulun öz qiyməti istifadə olunur.
        </div>
        @error('set_items')<div class="alert alert-danger">{{ $message }}</div>@enderror
        <div class="table-responsive">
          <table class="table align-middle set-items-table">
            <thead>
              <tr>
                <th style="width:30px"></th>
                <th style="min-width:280px">Modul (məhsul)</th>
                <th>Standart say</th>
                <th>Min say</th>
                <th>Maks say</th>
                <th>Məcburi</th>
                <th>Dəstdə qiymət</th>
                <th>Köhnə qiymət</th>
                <th class="text-end">Cəm</th>
                <th></th>
              </tr>
            </thead>
            <tbody class="js-set-rows">
              @foreach($setRows as $i => $row)
                @include('admin.products._set-row', ['i' => $i, 'row' => $row])
              @endforeach
            </tbody>
            <tfoot>
              <tr><td colspan="8" class="text-end fw-semibold">Dəstin standart qiyməti:</td><td class="text-end fw-bold js-set-total">—</td><td></td></tr>
            </tfoot>
          </table>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm js-add-set-row"><i class="bi bi-plus"></i> Modul əlavə et</button>
        <a href="{{ route('admin.products.create', ['type' => 'module']) }}" target="_blank" class="btn btn-link btn-sm">Yeni modul yarat</a>
        <template id="set-row-template">@include('admin.products._set-row', ['i' => '__I__', 'row' => []])</template>
      </div></div>
    </div>

    {{-- QİYMƏT --}}
    <div class="tab-pane fade" id="tab-price">
      <div class="card"><div class="card-body">
        <div class="row g-3">
          <div class="col-md-4 js-not-set">
            <label class="form-label">Qiymət (₼)</label>
            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="form-control">
          </div>
          <div class="col-md-4 js-not-set">
            <label class="form-label">Köhnə qiymət (endirimdən əvvəl)</label>
            <input type="number" step="0.01" min="0" name="old_price" value="{{ old('old_price', $product->old_price) }}" class="form-control">
            <div class="form-text">Doldurulsa endirim faizi ("-5 %") avtomatik göstərilir.</div>
          </div>
          <div class="col-12 js-set-only">
            <div class="alert alert-secondary mb-0">Dəstin qiyməti modullardan hesablanır: <b>{{ money($product->computed_price) }}</b>@if($product->computed_old_price) (köhnə: {{ money($product->computed_old_price) }})@endif</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Stok sayı</label>
            <input type="number" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty ?? 0) }}" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Stok statusu</label>
            <select name="stock_status" class="form-select">
              @foreach(\App\Models\Product::STOCK_STATUSES as $k => $v)<option value="{{ $k }}" @selected(old('stock_status', $product->stock_status) === $k)>{{ $v }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-4 js-not-set">
            <label class="form-label">Minimum sifariş sayı</label>
            <input type="number" min="1" name="min_qty" value="{{ old('min_qty', $product->min_qty ?? 1) }}" class="form-control">
          </div>
        </div>
      </div></div>
    </div>

    {{-- ŞƏKİLLƏR --}}
    <div class="tab-pane fade" id="tab-images">
      <div class="card"><div class="card-body">
        <div class="row g-3 js-images">
          @foreach($product->images ?? [] as $image)
            <div class="col-6 col-md-3 col-xl-2 js-image-item">
              <div class="border rounded p-2 bg-white h-100">
                <div class="d-flex justify-content-between mb-1"><i class="bi bi-grip-vertical drag-handle"></i>@if($loop->first)<span class="badge bg-success">Əsas</span>@endif</div>
                <img src="{{ thumb($image->path, 200, 200) }}" class="img-fluid mb-2" alt="">
                <input type="hidden" name="images[{{ $image->id }}][sort]" value="{{ $image->sort }}" class="js-image-sort">
                <select name="images[{{ $image->id }}][option_value_id]" class="form-select form-select-sm mb-1" title="Bu şəkil hansı opsiyon dəyərinə aiddir (rəngə görə şəkil)">
                  <option value="">— hamısı —</option>
                  @foreach($options as $option)
                    <optgroup label="{{ $option->name }}">
                      @foreach($option->values as $value)<option value="{{ $value->id }}" @selected($image->option_value_id == $value->id)>{{ $value->name }}</option>@endforeach
                    </optgroup>
                  @endforeach
                </select>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="images[{{ $image->id }}][delete]" value="1" id="img-del-{{ $image->id }}"><label class="form-check-label small text-danger" for="img-del-{{ $image->id }}">Sil</label></div>
              </div>
            </div>
          @endforeach
        </div>
        <div class="mt-3">
          <label class="form-label">Yeni şəkillər əlavə et</label>
          <input type="file" name="new_images[]" multiple accept="image/*" class="form-control">
          <div class="form-text">İlk şəkil əsas şəkildir. Sıranı sürükləyərək dəyişin. Dəstlərdə modulların şəkilləri qalereyaya avtomatik əlavə olunur.</div>
        </div>
      </div></div>
    </div>

    {{-- OPSİYONLAR --}}
    <div class="tab-pane fade" id="tab-options">
      <div class="card"><div class="card-body">
        <p class="text-muted small">Məhsulun rəng, ölçü və s. variantları. Qiymət əlavəsi sabit (₼) və ya faiz (%) ola bilər — dəstlərdə faiz dəstin ümumi qiymətinə tətbiq olunur.</p>
        @forelse($options as $option)
          @php $po = $productOptions->get($option->id); $poValues = $po ? $po->values->keyBy('option_value_id') : collect(); @endphp
          <div class="border rounded p-3 mb-3 bg-white">
            <div class="d-flex flex-wrap gap-3 align-items-center mb-2">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input js-option-toggle" type="checkbox" name="options[{{ $option->id }}][enabled]" value="1" id="opt-{{ $option->id }}" @checked($po)>
                <label class="form-check-label fw-semibold" for="opt-{{ $option->id }}">{{ $option->name }} <small class="text-muted">({{ \App\Models\Option::TYPES[$option->type] ?? $option->type }})</small></label>
              </div>
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" name="options[{{ $option->id }}][required]" value="1" id="opt-req-{{ $option->id }}" @checked($po?->is_required ?? true)>
                <label class="form-check-label small" for="opt-req-{{ $option->id }}">Məcburi seçim</label>
              </div>
            </div>
            <div class="js-option-values" @unless($po) style="display:none" @endunless>
              <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Dəyər</th><th>Qiymət əlavəsi</th><th>Tip</th><th>Stok</th><th>Standart</th></tr></thead>
                <tbody>
                  @foreach($option->values as $value)
                    @php $pv = $poValues->get($value->id); @endphp
                    <tr>
                      <td>
                        <div class="form-check mb-0">
                          <input class="form-check-input" type="checkbox" name="options[{{ $option->id }}][values][{{ $value->id }}][enabled]" value="1" id="ov-{{ $value->id }}" @checked($pv)>
                          <label class="form-check-label" for="ov-{{ $value->id }}">@if($value->color)<span class="color-dot" style="background:{{ $value->color }}"></span>@endif {{ $value->name }}</label>
                        </div>
                        <input type="hidden" name="options[{{ $option->id }}][values][{{ $value->id }}][sort]" value="{{ $value->sort }}">
                      </td>
                      <td><input type="number" step="0.01" name="options[{{ $option->id }}][values][{{ $value->id }}][price_modifier]" value="{{ $pv?->price_modifier ?? 0 }}" class="form-control form-control-sm" style="width:110px"></td>
                      <td>
                        <select name="options[{{ $option->id }}][values][{{ $value->id }}][modifier_type]" class="form-select form-select-sm" style="width:90px">
                          <option value="fixed" @selected(($pv?->modifier_type ?? 'fixed') === 'fixed')>₼</option>
                          <option value="percent" @selected($pv?->modifier_type === 'percent')>%</option>
                        </select>
                      </td>
                      <td><input type="number" name="options[{{ $option->id }}][values][{{ $value->id }}][stock_qty]" value="{{ $pv?->stock_qty }}" class="form-control form-control-sm" style="width:90px"></td>
                      <td><input class="form-check-input" type="radio" name="options[{{ $option->id }}][default]" value="{{ $value->id }}" @checked($pv?->is_default)></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @empty
          <p class="text-muted">Hələ opsiyon yaradılmayıb. <a href="{{ route('admin.options.create') }}">Opsiyon yarat</a></p>
        @endforelse
      </div></div>
    </div>

    {{-- XÜSUSİYYƏTLƏR --}}
    <div class="tab-pane fade" id="tab-attributes">
      <div class="card"><div class="card-body">
        <p class="text-muted small">Xüsusiyyətlər "Xüsusiyyətlər" tabında cədvəl kimi görünür və kateqoriya filtrlərində istifadə olunur.</p>
        <div class="row g-3">
          @forelse($attributes as $attribute)
            <div class="col-md-6">
              <label class="form-label">{{ $attribute->name }} @if($attribute->group)<small class="text-muted">({{ $attribute->group->name }})</small>@endif</label>
              <select name="attributes[{{ $attribute->id }}][]" class="form-select js-select2" multiple data-placeholder="—">
                @foreach($attribute->values as $value)<option value="{{ $value->id }}" @selected(in_array($value->id, $selectedAttrValues))>{{ $value->value }}</option>@endforeach
              </select>
            </div>
          @empty
            <p class="text-muted">Hələ xüsusiyyət yaradılmayıb. <a href="{{ route('admin.attributes.create') }}">Xüsusiyyət yarat</a></p>
          @endforelse
        </div>
      </div></div>
    </div>

    {{-- OXŞAR --}}
    <div class="tab-pane fade" id="tab-related">
      <div class="card"><div class="card-body">
        <label class="form-label">Oxşar məhsullar</label>
        <select name="related[]" class="form-select js-product-search" multiple>
          @foreach($product->related ?? [] as $rel)<option value="{{ $rel->id }}" selected>{{ $rel->name }}</option>@endforeach
        </select>
        <div class="form-text">Boş qalarsa eyni əsas kateqoriyadan təsadüfi məhsullar göstərilir.</div>
      </div></div>
    </div>

    {{-- SEO --}}
    <div class="tab-pane fade" id="tab-seo">
      <div class="card"><div class="card-body">
        <x-admin.trans name="slug" label="URL (slug)" :model="$product" help="Boş qoysanız addan avtomatik yaradılacaq. URL: /{dil}/{əsas-kateqoriya}/{slug}" />
        <x-admin.trans name="meta_title" label="Meta başlıq" :model="$product" />
        <x-admin.trans name="meta_description" label="Meta təsvir" :model="$product" type="textarea" />
      </div></div>
    </div>
  </div>

  <div class="sticky-actions d-flex gap-2 mt-3">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-light">Siyahıya qayıt</a>
    @if($product->exists)
      <a href="{{ route('admin.discounts.create', ['scope' => 'products', 'product' => $product->id]) }}" class="btn btn-outline-danger ms-auto"><i class="bi bi-percent"></i> Endirim tətbiq et</a>
      <a href="{{ $product->url() }}" target="_blank" class="btn btn-light"><i class="bi bi-eye"></i> Saytda bax</a>
    @endif
  </div>
</form>
@endsection

@push('scripts')
<script>
  (function () {
    var helps = {
      simple: 'Adi məhsul: öz qiyməti var.',
      set: 'Dəst: qiyməti "Dəst modulları" tabındakı modulların qiyməti × sayından formalaşır.',
      module: 'Modul: dəstin hissəsidir (məs. çarpayı, tumba). İstəsəniz ayrıca da satıla bilər.'
    };
    function applyType() {
      var t = $('.js-type').val();
      $('.js-set-only').toggle(t === 'set');
      $('.js-not-set').toggle(t !== 'set');
      $('.js-module-only').toggle(t === 'module');
      $('.js-type-help').text(helps[t] || '');
    }
    $('.js-type').on('change', applyType);
    applyType();

    $('#product-form').on('submit', function () {
      var active = $('.js-hash-tabs .nav-link.active').data('bs-target');
      $('#current-tab').val(active ? active.replace('#', '') : '');
      $('.js-images .js-image-item').each(function (i) { $(this).find('.js-image-sort').val(i); });
    });

    $(document).on('change', '.js-option-toggle', function () {
      $(this).closest('.border').find('.js-option-values').toggle(this.checked);
    });

    var imgs = document.querySelector('.js-images');
    if (imgs) { new Sortable(imgs, { handle: '.drag-handle', animation: 150 }); }

    // Dəst modulları
    var idx = 1000;
    function rowTotal($row) {
      var qty = parseFloat($row.find('.js-default-qty').val()) || 0;
      var price = $row.find('.js-price-override').val();
      price = price !== '' ? parseFloat(price) : (parseFloat($row.data('component-price')) || 0);
      var total = qty * price;
      $row.find('.js-row-total').text(total ? total.toFixed(2).replace(/\.00$/, '') + ' ₼' : '—');
      return total;
    }
    function recalc() {
      var sum = 0;
      $('.js-set-rows tr').each(function () { sum += rowTotal($(this)); });
      $('.js-set-total').text(sum.toFixed(2).replace(/\.00$/, '') + ' ₼');
    }
    function initRow($row) {
      adminInitSelects($row);
      $row.find('.js-product-search').on('select2:select', function (e) {
        var m = (e.params.data.text || '').match(/—\s*([\d,.]+)\s*₼/);
        $row.data('component-price', m ? parseFloat(m[1].replace(/,/g, '')) : 0);
        recalc();
      });
    }
    $('.js-set-rows tr').each(function () { initRow($(this)); });
    $('.js-add-set-row').on('click', function () {
      var $row = $($('#set-row-template').html().replace(/__I__/g, idx++));
      $('.js-set-rows').append($row);
      initRow($row);
    });
    $(document).on('click', '.js-remove-set-row', function () { $(this).closest('tr').remove(); recalc(); });
    $(document).on('input change', '.js-set-rows input', recalc);
    var setRows = document.querySelector('.js-set-rows');
    if (setRows) { new Sortable(setRows, { handle: '.drag-handle', animation: 150 }); }
    recalc();
  })();
</script>
@endpush
