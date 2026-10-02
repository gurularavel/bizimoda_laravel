@extends('admin.layouts.app')

@section('title', ($section->exists ? 'Bölmə' : 'Yeni bölmə').': '.(\App\Models\HomeSection::TYPES[$section->type] ?? $section->type))

@php $data = $section->data ?? []; @endphp

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $section->exists ? route('admin.home-sections.update', $section) : route('admin.home-sections.store') }}" style="max-width:900px">
  @csrf
  @if($section->exists) @method('PUT') @endif
  <input type="hidden" name="type" value="{{ $section->type }}">
  <div class="card"><div class="card-body">
    <x-admin.trans name="title" label="Başlıq" :model="$section" />

    @if($section->type === 'slider')
      <label class="form-label">Slayder</label>
      <select name="data[slider_id]" class="form-select">
        @foreach($sliders as $id => $name)<option value="{{ $id }}" @selected(($data['slider_id'] ?? null) == $id)>{{ $name }}</option>@endforeach
      </select>
    @elseif($section->type === 'products')
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Məhsul mənbəyi</label>
          <select name="data[source]" class="form-select">
            @foreach(\App\Models\HomeSection::PRODUCT_SOURCES as $k => $v)<option value="{{ $k }}" @selected(($data['source'] ?? 'featured') === $k)>{{ $v }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label">Kateqoriya</label>
          <select name="data[category_id]" class="form-select js-select2" data-placeholder="—">
            <option value=""></option>
            @foreach($categories as $id => $label)<option value="{{ $id }}" @selected(($data['category_id'] ?? null) == $id)>{{ $label }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Say</label>
          <input type="number" name="data[limit]" value="{{ $data['limit'] ?? 12 }}" class="form-control">
        </div>
        <div class="col-12">
          <label class="form-label">Əl ilə seçilmiş məhsullar</label>
          <select name="data[ids][]" class="form-select js-product-search" multiple>
            @foreach($selectedProducts as $p)<option value="{{ $p->id }}" selected>{{ $p->name }}</option>@endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Görünüş stili</label>
          <select name="module_class" class="form-select">
            @foreach(\App\Http\Controllers\Admin\HomeSectionController::MODULE_CLASSES as $k => $v)<option value="{{ $k }}" @selected($section->module_class === $k)>{{ $v }}</option>@endforeach
          </select>
        </div>
      </div>
    @elseif($section->type === 'banners')
      <div class="row g-2">
        @foreach($data['items'] ?? [] as $j => $item)
          <div class="col-md-4">
            <div class="border rounded p-2">
              <img src="{{ thumb($item['image'], 260, 130) }}" class="img-fluid mb-1" alt="">
              <input type="hidden" name="data[items][{{ $j }}][image]" value="{{ $item['image'] }}">
              <input type="file" name="item_files[{{ $j }}]" accept="image/*" class="form-control form-control-sm mb-1">
              <input type="text" name="data[items][{{ $j }}][link]" value="{{ $item['link'] ?? '' }}" class="form-control form-control-sm mb-1" placeholder="Link">
              <label class="small text-danger"><input type="checkbox" name="data[items][{{ $j }}][remove]" value="1"> sil</label>
            </div>
          </div>
        @endforeach
      </div>
      <label class="form-label mt-2">Yeni bannerlər</label>
      <input type="file" name="new_files[]" multiple accept="image/*" class="form-control">
    @elseif($section->type === 'html')
      <x-admin.trans name="data[html]" label="Məzmun" :model="$data['html'] ?? []" type="editor" />
    @elseif($section->type === 'blog')
      <label class="form-label">Yazı sayı</label>
      <input type="number" name="data[limit]" value="{{ $data['limit'] ?? 4 }}" class="form-control" style="max-width:150px">
    @endif

    <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked($section->is_active)><label class="form-check-label" for="ia">Aktiv</label></div>
  </div></div>
  <div class="mt-3 d-flex gap-2"><button class="btn btn-brand">Yadda saxla</button><a href="{{ route('admin.home-sections.index') }}" class="btn btn-light">Geri</a></div>
</form>
@endsection
