@extends('admin.layouts.app')

@section('title', $option->exists ? 'Opsiyon: '.$option->name : 'Yeni opsiyon')

@php $locales = \App\Support\Locales::all(); @endphp

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $option->exists ? route('admin.options.update', $option) : route('admin.options.store') }}">
  @csrf
  @if($option->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card"><div class="card-body">
        <x-admin.trans name="name" label="Ad" :model="$option" required />
        <div class="mb-3">
          <label class="form-label">Görünüş tipi</label>
          <select name="type" class="form-select">
            @foreach(\App\Models\Option::TYPES as $k => $v)<option value="{{ $k }}" @selected(old('type', $option->type) === $k)>{{ $v }}</option>@endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Sıra</label>
          <input type="number" name="sort" value="{{ old('sort', $option->sort ?? 0) }}" class="form-control">
        </div>
      </div></div>
    </div>
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">Dəyərlər</div>
        <div class="card-body">
          <table class="table align-middle">
            <thead><tr><th style="width:30px"></th>@foreach($locales as $code => $l)<th>{{ strtoupper($code) }}</th>@endforeach<th>Rəng</th><th>Şəkil</th><th></th></tr></thead>
            <tbody class="js-values">
              @foreach($option->values ?? [] as $i => $value)
                @include('admin.options._value-row', ['i' => $i, 'value' => $value])
              @endforeach
            </tbody>
          </table>
          <button type="button" class="btn btn-sm btn-outline-secondary js-add-value"><i class="bi bi-plus"></i> Dəyər əlavə et</button>
          <template id="value-template">@include('admin.options._value-row', ['i' => '__I__', 'value' => null])</template>
        </div>
      </div>
    </div>
  </div>
  <div class="sticky-actions d-flex gap-2 mt-3">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button>
    <a href="{{ route('admin.options.index') }}" class="btn btn-light">Siyahıya qayıt</a>
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
