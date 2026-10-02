@extends('admin.layouts.app')

@section('title', $slider->exists ? 'Slayder: '.$slider->name : 'Yeni slayder')

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $slider->exists ? route('admin.sliders.update', $slider) : route('admin.sliders.store') }}">
  @csrf
  @if($slider->exists) @method('PUT') @endif
  <div class="card mb-3"><div class="card-body row g-3">
    <div class="col-md-6"><label class="form-label">Ad</label><input name="name" value="{{ old('name', $slider->name) }}" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">En (px)</label><input type="number" name="width" value="{{ old('width', $slider->width) }}" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Hündürlük (px)</label><input type="number" name="height" value="{{ old('height', $slider->height) }}" class="form-control"></div>
  </div></div>

  <div class="card">
    <div class="card-header">Slaydlar <small class="text-muted fw-normal">— sürükləyib sıralayın</small></div>
    <div class="card-body">
      <div class="js-slides">
        @foreach($slider->slides ?? [] as $slide)
          <div class="d-flex gap-3 align-items-start border rounded p-2 mb-2 bg-white js-slide">
            <i class="bi bi-grip-vertical drag-handle mt-4"></i>
            <img src="{{ thumb($slide->image, 240, 70, 'cover') }}" style="width:240px;height:70px;object-fit:cover" class="rounded" alt="">
            <div class="flex-grow-1">
              <input type="hidden" name="slides[{{ $slide->id }}][sort]" value="{{ $slide->sort }}" class="js-slide-sort">
              <div class="row g-2">
                <div class="col-md-6"><input type="text" name="slides[{{ $slide->id }}][link]" value="{{ $slide->link }}" class="form-control form-control-sm" placeholder="Link (məs. /az/specials)"></div>
                @foreach(\App\Support\Locales::codes() as $code)
                  <div class="col-md-2"><input type="text" name="slides[{{ $slide->id }}][title][{{ $code }}]" value="{{ $slide->getTranslation('title', $code, false) }}" class="form-control form-control-sm" placeholder="Alt ({{ strtoupper($code) }})"></div>
                @endforeach
                <div class="col-md-6"><input type="file" name="slides[{{ $slide->id }}][image]" accept="image/*" class="form-control form-control-sm"></div>
                <div class="col-md-6 d-flex gap-3 align-items-center">
                  <label class="small"><input type="checkbox" name="slides[{{ $slide->id }}][is_active]" value="1" @checked($slide->is_active)> Aktiv</label>
                  <label class="small text-danger"><input type="checkbox" name="slides[{{ $slide->id }}][delete]" value="1"> Sil</label>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
      <label class="form-label mt-2">Yeni slaydlar</label>
      <input type="file" name="new_slides[]" multiple accept="image/*" class="form-control">
      <div class="form-text">Tövsiyə olunan ölçü: {{ $slider->width }}×{{ $slider->height }} px.</div>
    </div>
  </div>
  <div class="sticky-actions d-flex gap-2 mt-3">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button>
    <a href="{{ route('admin.sliders.index') }}" class="btn btn-light">Siyahıya qayıt</a>
  </div>
</form>
@endsection

@push('scripts')
<script>
  var el = document.querySelector('.js-slides');
  new Sortable(el, { handle: '.drag-handle', animation: 150, onEnd: function () { $('.js-slide').each(function (i) { $(this).find('.js-slide-sort').val(i); }); } });
</script>
@endpush
