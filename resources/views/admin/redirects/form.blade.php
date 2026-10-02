@extends('admin.layouts.app')

@section('title', $redirect->exists ? 'Yönləndirməni redaktə et' : 'Yeni yönləndirmə')

@section('content')
<form method="post" action="{{ $redirect->exists ? route('admin.redirects.update', $redirect) : route('admin.redirects.store') }}" style="max-width:640px">
  @csrf
  @if($redirect->exists) @method('PUT') @endif
  <div class="card"><div class="card-body">
    <div class="mb-3"><label class="form-label">Köhnə ünvan (yol)</label><input name="from_path" value="{{ old('from_path', $redirect->from_path) }}" class="form-control" placeholder="/kohne-unvan" required></div>
    <div class="mb-3"><label class="form-label">Yeni ünvan</label><input name="to_path" value="{{ old('to_path', $redirect->to_path) }}" class="form-control" placeholder="/az/yeni-unvan və ya tam URL" required></div>
    <div class="mb-3">
      <label class="form-label">Kod</label>
      <select name="code" class="form-select"><option value="301" @selected(old('code', $redirect->code) == 301)>301 — daimi</option><option value="302" @selected(old('code', $redirect->code) == 302)>302 — müvəqqəti</option></select>
    </div>
  </div></div>
  <div class="mt-3 d-flex gap-2"><button class="btn btn-brand">Yadda saxla</button><a href="{{ route('admin.redirects.index') }}" class="btn btn-light">Geri</a></div>
</form>
@endsection
