@php
    $p = "blocks[{$i}]";
    $codes = \App\Support\Locales::codes();
    $t = fn ($key, $code) => is_array($data[$key] ?? null) ? ($data[$key][$code] ?? '') : '';
@endphp
<div class="page-block border rounded mb-3 bg-light">
  <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom bg-white rounded-top">
    <i class="bi bi-grip-vertical drag-handle"></i>
    <strong>{{ \App\Models\PageBlock::TYPES[$type] ?? $type }}</strong>
    <button type="button" class="btn btn-sm btn-link text-danger ms-auto js-remove-block"><i class="bi bi-trash"></i> Sil</button>
  </div>
  <div class="p-3">
    <input type="hidden" name="{{ $p }}[type]" value="{{ $type }}">

    @switch($type)
      @case('text')
        <x-admin.trans name="{{ $p }}[data][content]" label="Mətn" :model="$data['content'] ?? []" type="editor" />
        @break

      @case('html')
        <label class="form-label">HTML kod</label>
        <textarea name="{{ $p }}[data][html]" rows="6" class="form-control font-monospace small">{{ $data['html'] ?? '' }}</textarea>
        @break

      @case('image')
        <div class="row g-2">
          <div class="col-md-4">
            @if(! empty($data['image']))<img src="{{ thumb($data['image'], 200, 120) }}" class="img-fluid mb-2" alt="">@endif
            <input type="hidden" name="{{ $p }}[data][image]" value="{{ $data['image'] ?? '' }}">
            <input type="file" name="{{ $p }}[image_file]" accept="image/*" class="form-control form-control-sm">
          </div>
          <div class="col-md-8">
            <input type="text" name="{{ $p }}[data][link]" value="{{ $data['link'] ?? '' }}" class="form-control form-control-sm mb-2" placeholder="Link (istəyə bağlı)">
            @foreach($codes as $code)
              <input type="text" name="{{ $p }}[data][alt][{{ $code }}]" value="{{ $t('alt', $code) }}" class="form-control form-control-sm mb-1" placeholder="Alt mətn ({{ strtoupper($code) }})">
            @endforeach
          </div>
        </div>
        @break

      @case('banner')
        <div class="row g-2 mb-2">
          @foreach($codes as $code)
            <div class="col"><input type="text" name="{{ $p }}[data][title][{{ $code }}]" value="{{ $t('title', $code) }}" class="form-control form-control-sm" placeholder="Başlıq ({{ strtoupper($code) }})"></div>
          @endforeach
        </div>
        <div class="row g-2">
          @foreach($data['items'] ?? [] as $j => $item)
            <div class="col-md-4">
              <div class="border rounded p-2 bg-white">
                <img src="{{ thumb($item['image'], 240, 120) }}" class="img-fluid mb-1" alt="">
                <input type="hidden" name="{{ $p }}[data][items][{{ $j }}][image]" value="{{ $item['image'] }}">
                <input type="file" name="{{ $p }}[item_files][{{ $j }}]" accept="image/*" class="form-control form-control-sm mb-1">
                <input type="text" name="{{ $p }}[data][items][{{ $j }}][link]" value="{{ $item['link'] ?? '' }}" class="form-control form-control-sm mb-1" placeholder="Link">
                <label class="small text-danger"><input type="checkbox" name="{{ $p }}[data][items][{{ $j }}][remove]" value="1"> sil</label>
              </div>
            </div>
          @endforeach
        </div>
        <label class="form-label small mt-2">Yeni banner şəkilləri</label>
        <input type="file" name="{{ $p }}[new_files][]" multiple accept="image/*" class="form-control form-control-sm">
        @break

      @case('products')
        <div class="row g-2 mb-2">
          @foreach($codes as $code)
            <div class="col"><input type="text" name="{{ $p }}[data][title][{{ $code }}]" value="{{ $t('title', $code) }}" class="form-control form-control-sm" placeholder="Başlıq ({{ strtoupper($code) }})"></div>
          @endforeach
        </div>
        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label small">Mənbə</label>
            <select name="{{ $p }}[data][source]" class="form-select form-select-sm">
              @foreach(\App\Models\HomeSection::PRODUCT_SOURCES as $k => $v)<option value="{{ $k }}" @selected(($data['source'] ?? 'featured') === $k)>{{ $v }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label small">Kateqoriya (mənbə "Kateqoriyadan" olduqda)</label>
            <select name="{{ $p }}[data][category_id]" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($categories as $id => $label)<option value="{{ $id }}" @selected(($data['category_id'] ?? null) == $id)>{{ $label }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small">Say</label>
            <input type="number" name="{{ $p }}[data][limit]" value="{{ $data['limit'] ?? 10 }}" class="form-control form-control-sm">
          </div>
          <div class="col-12">
            <label class="form-label small">Əl ilə seçilmiş məhsullar (mənbə "Əl ilə seçilmiş")</label>
            <select name="{{ $p }}[data][ids][]" class="form-select js-product-search" multiple>
              @foreach(\App\Models\Product::query()->whereIn('id', $data['ids'] ?? [])->get() as $prod)<option value="{{ $prod->id }}" selected>{{ $prod->name }}</option>@endforeach
            </select>
          </div>
        </div>
        @break

      @case('faq')
        <div class="row g-2 mb-2">
          @foreach($codes as $code)
            <div class="col"><input type="text" name="{{ $p }}[data][title][{{ $code }}]" value="{{ $t('title', $code) }}" class="form-control form-control-sm" placeholder="Başlıq ({{ strtoupper($code) }})"></div>
          @endforeach
        </div>
        <div class="form-text mb-1">Hər sətirdə bir sual: <code>Sual :: Cavab</code></div>
        @foreach($codes as $code)
          @php $raw = collect($data['items'] ?? [])->map(fn ($it) => ($it['q'][$code] ?? '').' :: '.($it['a'][$code] ?? ''))->filter(fn ($l) => trim($l) !== '::')->implode("\n"); @endphp
          <label class="form-label small mb-0">{{ strtoupper($code) }}</label>
          <textarea name="{{ $p }}[data][raw][{{ $code }}]" rows="4" class="form-control form-control-sm mb-2">{{ $raw }}</textarea>
        @endforeach
        @break

      @case('form')
        <div class="row g-2">
          @foreach($codes as $code)
            <div class="col"><input type="text" name="{{ $p }}[data][title][{{ $code }}]" value="{{ $t('title', $code) }}" class="form-control form-control-sm" placeholder="Forma başlığı ({{ strtoupper($code) }})"></div>
          @endforeach
        </div>
        <div class="form-text">Müraciətlər admin → Müraciətlər bölməsinə düşür və bildiriş e-poçtlarına göndərilir.</div>
        @break
    @endswitch
  </div>
</div>
