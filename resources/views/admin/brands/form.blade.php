@extends('admin.layouts.app')

@section('title', $brand->exists ? 'Brend: '.$brand->name : 'Yeni brend')

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" style="max-width:640px">
  @csrf
  @if($brand->exists) @method('PUT') @endif
  <div class="card"><div class="card-body">
    <div class="mb-3"><label class="form-label">Ad *</label><input type="text" name="name" value="{{ old('name', $brand->name) }}" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" value="{{ old('slug', $brand->slug) }}" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Sıra</label><input type="number" name="sort" value="{{ old('sort', $brand->sort ?? 0) }}" class="form-control"></div>
    <x-admin.image name="logo" label="Loqo" :value="$brand->logo" />
    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $brand->is_active))><label class="form-check-label" for="is_active">Aktiv</label></div>
  </div></div>
  <div class="mt-3 d-flex gap-2"><button class="btn btn-brand">Yadda saxla</button><a href="{{ route('admin.brands.index') }}" class="btn btn-light">Geri</a></div>
</form>
@endsection
