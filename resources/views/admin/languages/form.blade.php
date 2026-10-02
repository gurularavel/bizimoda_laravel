@extends('admin.layouts.app')

@section('title', 'Dil: '.$language->name)

@section('content')
<form method="post" action="{{ route('admin.languages.update', $language) }}" style="max-width:520px">
  @csrf @method('PUT')
  <div class="card"><div class="card-body">
    <div class="mb-3"><label class="form-label">Kod</label><input class="form-control" value="{{ $language->code }}" disabled></div>
    <div class="mb-3"><label class="form-label">Ad (dil seçicisində)</label><input name="name" value="{{ old('name', $language->name) }}" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Sıra</label><input type="number" name="sort" value="{{ old('sort', $language->sort) }}" class="form-control"></div>
    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked($language->is_active)><label class="form-check-label" for="ia">Aktiv</label></div>
    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_default" value="1" id="idf" @checked($language->is_default)><label class="form-check-label" for="idf">Default dil (/ ünvanı bu dilə yönləndirir)</label></div>
  </div></div>
  <div class="mt-3 d-flex gap-2"><button class="btn btn-brand">Yadda saxla</button><a href="{{ route('admin.languages.index') }}" class="btn btn-light">Geri</a></div>
</form>
@endsection
