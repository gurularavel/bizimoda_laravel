@extends('admin.layouts.app')

@section('title', $category->exists ? 'Bloq kateqoriyası: '.$category->name : 'Yeni bloq kateqoriyası')

@section('content')
<form method="post" action="{{ $category->exists ? route('admin.blog-categories.update', $category) : route('admin.blog-categories.store') }}" style="max-width:900px">
  @csrf
  @if($category->exists) @method('PUT') @endif
  <div class="card"><div class="card-body">
    <x-admin.trans name="name" label="Ad" :model="$category" required />
    <x-admin.trans name="description" label="Təsvir" :model="$category" type="editor" />
    <x-admin.trans name="slug" label="URL (slug)" :model="$category" />
    <x-admin.trans name="meta_title" label="Meta başlıq" :model="$category" />
    <x-admin.trans name="meta_description" label="Meta təsvir" :model="$category" type="textarea" />
    <div class="row">
      <div class="col-md-3"><label class="form-label">Sıra</label><input type="number" name="sort" value="{{ old('sort', $category->sort ?? 0) }}" class="form-control"></div>
      <div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked(old('is_active', $category->is_active))><label class="form-check-label" for="ia">Aktiv</label></div></div>
    </div>
  </div></div>
  <div class="mt-3 d-flex gap-2"><button class="btn btn-brand">Yadda saxla</button><a href="{{ route('admin.blog-categories.index') }}" class="btn btn-light">Geri</a></div>
</form>
@endsection
