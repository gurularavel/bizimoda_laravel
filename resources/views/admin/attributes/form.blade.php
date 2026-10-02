@extends('admin.layouts.app')

@section('title', $attribute->exists ? 'Xüsusiyyət: '.$attribute->name : 'Yeni xüsusiyyət')

@php $codes = \App\Support\Locales::codes(); @endphp

@section('content')
<form method="post" action="{{ $attribute->exists ? route('admin.attributes.update', $attribute) : route('admin.attributes.store') }}">
  @csrf
  @if($attribute->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card"><div class="card-body">
        <x-admin.trans name="name" label="Ad" :model="$attribute" required />
        <div class="mb-3">
          <label class="form-label">Qrup</label>
          <select name="attribute_group_id" class="form-select">
            <option value="">—</option>
            @foreach($groups as $g)<option value="{{ $g->id }}" @selected(old('attribute_group_id', $attribute->attribute_group_id) == $g->id)>{{ $g->name }}</option>@endforeach
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Sıra</label><input type="number" name="sort" value="{{ old('sort', $attribute->sort ?? 0) }}" class="form-control"></div>
      </div></div>
    </div>
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">Dəyərlər</div>
        <div class="card-body">
          <table class="table align-middle">
            <thead><tr><th style="width:30px"></th>@foreach($codes as $c)<th>{{ strtoupper($c) }}</th>@endforeach<th></th></tr></thead>
            <tbody class="js-values">
              @foreach($attribute->values ?? [] as $i => $value)
                <tr>
                  <td><i class="bi bi-grip-vertical drag-handle"></i><input type="hidden" name="values[{{ $i }}][id]" value="{{ $value->id }}"></td>
                  @foreach($codes as $c)<td><input type="text" name="values[{{ $i }}][value][{{ $c }}]" value="{{ $value->getTranslation('value', $c, false) }}" class="form-control form-control-sm"></td>@endforeach
                  <td><button type="button" class="btn btn-sm btn-light text-danger js-remove-value"><i class="bi bi-x-lg"></i></button></td>
                </tr>
              @endforeach
            </tbody>
          </table>
          <button type="button" class="btn btn-sm btn-outline-secondary js-add-value"><i class="bi bi-plus"></i> Dəyər əlavə et</button>
          <template id="value-template">
            <tr>
              <td><i class="bi bi-grip-vertical drag-handle"></i></td>
              @foreach($codes as $c)<td><input type="text" name="values[__I__][value][{{ $c }}]" class="form-control form-control-sm"></td>@endforeach
              <td><button type="button" class="btn btn-sm btn-light text-danger js-remove-value"><i class="bi bi-x-lg"></i></button></td>
            </tr>
          </template>
        </div>
      </div>
    </div>
  </div>
  <div class="sticky-actions d-flex gap-2 mt-3">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button>
    <a href="{{ route('admin.attributes.index') }}" class="btn btn-light">Siyahıya qayıt</a>
  </div>
</form>
@endsection

@push('scripts')
<script>
  var vIdx = 1000;
  $('.js-add-value').on('click', function () { $('.js-values').append($('#value-template').html().replace(/__I__/g, vIdx++)); });
  $(document).on('click', '.js-remove-value', function () { $(this).closest('tr').remove(); });
  new Sortable(document.querySelector('.js-values'), { handle: '.drag-handle', animation: 150 });
</script>
@endpush
