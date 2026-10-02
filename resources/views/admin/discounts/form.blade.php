@extends('admin.layouts.app')

@section('title', $discount->exists ? 'Endirim: '.$discount->name : 'Yeni endirim')

@php $scope = old('applies_to', $discount->applies_to); @endphp

@section('content')
<form method="post" action="{{ $discount->exists ? route('admin.discounts.update', $discount) : route('admin.discounts.store') }}" style="max-width:860px">
  @csrf
  @if($discount->exists) @method('PUT') @endif
  <div class="card mb-3"><div class="card-body">
    <div class="mb-3">
      <label class="form-label">Ad (admin və sifarişdə görünür) <span class="text-danger">*</span></label>
      <input name="name" value="{{ old('name', $discount->name) }}" class="form-control" placeholder="məs. Yataq otağı kampaniyası" required>
    </div>
    <div class="row g-3">
      <div class="col-md-5">
        <label class="form-label">Endirim növü</label>
        <div class="btn-group w-100" role="group">
          @foreach(\App\Models\Discount::TYPES as $k => $v)
            <input type="radio" class="btn-check js-type" name="type" id="type-{{ $k }}" value="{{ $k }}" @checked(old('type', $discount->type) === $k)>
            <label class="btn btn-outline-secondary" for="type-{{ $k }}">{{ $v }}</label>
          @endforeach
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Dəyər <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="number" step="0.01" min="0.01" name="value" value="{{ old('value', $discount->value) }}" class="form-control" required>
          <span class="input-group-text js-unit">{{ old('type', $discount->type) === 'fixed' ? '₼' : '%' }}</span>
        </div>
        <div class="form-text js-type-help"></div>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked(old('is_active', $discount->is_active))><label class="form-check-label" for="ia">Aktiv</label></div>
      </div>
    </div>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <label class="form-label">Hara tətbiq olunsun?</label>
    <div class="mb-3">
      @foreach(\App\Models\Discount::SCOPES as $k => $v)
        <div class="form-check form-check-inline">
          <input class="form-check-input js-scope" type="radio" name="applies_to" id="scope-{{ $k }}" value="{{ $k }}" @checked($scope === $k)>
          <label class="form-check-label" for="scope-{{ $k }}">{{ $v }}</label>
        </div>
      @endforeach
    </div>
    <div class="js-scope-categories mb-2">
      <label class="form-label">Kateqoriyalar <small class="text-muted">(alt kateqoriyalardakı məhsullar da daxildir)</small></label>
      <select name="categories[]" class="form-select js-select2" multiple data-placeholder="Kateqoriya seçin">
        @foreach($categories as $id => $label)<option value="{{ $id }}" @selected(in_array($id, old('categories', $selectedCategories)))>{{ $label }}</option>@endforeach
      </select>
    </div>
    <div class="js-scope-products mb-2">
      <label class="form-label">Məhsullar</label>
      <select name="products[]" class="form-select js-product-search" multiple data-placeholder="Məhsul axtar...">
        @foreach($selectedProducts as $p)<option value="{{ $p->id }}" selected>{{ $p->name }}</option>@endforeach
      </select>
    </div>
    <div class="alert alert-light border small mb-0">
      Dəstlərdə endirim dəstin yekun qiymətinə tətbiq olunur. Məhsulda artıq "köhnə qiymət" varsa, endirim cari qiymətdən çıxılır və müştəri köhnə qiyməti üstündən xətt çəkilmiş görür.
    </div>
  </div></div>

  <div class="card mb-3"><div class="card-body row g-3">
    <div class="col-md-6">
      <label class="form-label">Başlama tarixi</label>
      <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $discount->starts_at?->format('Y-m-d\TH:i')) }}" class="form-control">
    </div>
    <div class="col-md-6">
      <label class="form-label">Bitmə tarixi</label>
      <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $discount->ends_at?->format('Y-m-d\TH:i')) }}" class="form-control">
    </div>
    <div class="col-12 form-text mt-0">Boş qalarsa dərhal başlayır / müddətsizdir. Başlama və bitmə vaxtında qiymətlər avtomatik yenilənir (scheduler).</div>
  </div></div>

  <div class="d-flex gap-2"><button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button><a href="{{ route('admin.discounts.index') }}" class="btn btn-light">Geri</a></div>
</form>
@endsection

@push('scripts')
<script>
  function applyScope() {
    var s = $('.js-scope:checked').val();
    $('.js-scope-categories').toggle(s === 'categories');
    $('.js-scope-products').toggle(s === 'products');
  }
  function applyType() {
    var t = $('.js-type:checked').val();
    $('.js-unit').text(t === 'fixed' ? '₼' : '%');
    $('.js-type-help').text(t === 'fixed' ? 'Hər məhsulun qiymətindən bu məbləğ çıxılır.' : 'Qiymətdən bu faiz qədər endirim (1–100).');
  }
  $('.js-scope').on('change', applyScope);
  $('.js-type').on('change', applyType);
  applyScope(); applyType();
</script>
@endpush
