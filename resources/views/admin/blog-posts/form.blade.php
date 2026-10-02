@extends('admin.layouts.app')

@section('title', $post->exists ? 'Yazı: '.$post->title : 'Yeni bloq yazısı')

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $post->exists ? route('admin.blog-posts.update', $post) : route('admin.blog-posts.store') }}">
  @csrf
  @if($post->exists) @method('PUT') @endif
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card"><div class="card-body">
        <x-admin.trans name="title" label="Başlıq" :model="$post" required />
        <x-admin.trans name="excerpt" label="Qısa məzmun" :model="$post" type="textarea" :rows="2" />
        <x-admin.trans name="content" label="Məzmun" :model="$post" type="editor" />
        <x-admin.trans name="tags" label="Teqlər (vergüllə)" :model="$post" />
      </div></div>
    </div>
    <div class="col-lg-4">
      <div class="card mb-3"><div class="card-body">
        <div class="mb-3">
          <label class="form-label">Kateqoriya</label>
          <select name="blog_category_id" class="form-select">
            <option value="">—</option>
            @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('blog_category_id', $post->blog_category_id) == $c->id)>{{ $c->name }}</option>@endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Dərc tarixi</label>
          <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="form-control">
          <div class="form-text">Gələcək tarix seçilsə, yazı həmin vaxt avtomatik görünəcək.</div>
        </div>
        <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked(old('is_active', $post->is_active))><label class="form-check-label" for="ia">Aktiv (qaralama deyil)</label></div>
        <x-admin.image name="cover" label="Örtük şəkli" :value="$post->cover" />
      </div></div>
      <div class="card"><div class="card-body">
        <x-admin.trans name="slug" label="URL (slug)" :model="$post" />
        <x-admin.trans name="meta_title" label="Meta başlıq" :model="$post" />
        <x-admin.trans name="meta_description" label="Meta təsvir" :model="$post" type="textarea" />
      </div></div>
    </div>
  </div>
  <div class="sticky-actions d-flex gap-2 mt-3">
    <button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button>
    <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-light">Siyahıya qayıt</a>
  </div>
</form>
@endsection
