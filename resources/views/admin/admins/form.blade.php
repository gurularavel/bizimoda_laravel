@extends('admin.layouts.app')

@section('title', $admin->exists ? 'Admin: '.$admin->name : 'Yeni admin')

@section('content')
<form method="post" action="{{ $admin->exists ? route('admin.admins.update', $admin) : route('admin.admins.store') }}" style="max-width:560px">
  @csrf
  @if($admin->exists) @method('PUT') @endif
  <div class="card"><div class="card-body">
    <div class="mb-3"><label class="form-label">Ad</label><input name="name" value="{{ old('name', $admin->name) }}" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">E-poçt</label><input type="email" name="email" value="{{ old('email', $admin->email) }}" class="form-control" required></div>
    <div class="mb-3">
      <label class="form-label">Rol</label>
      <select name="role" class="form-select">@foreach(\App\Models\Admin::ROLES as $k => $v)<option value="{{ $k }}" @selected(old('role', $admin->role) === $k)>{{ $v }}</option>@endforeach</select>
    </div>
    <div class="mb-3"><label class="form-label">Şifrə</label><input type="password" name="password" class="form-control" autocomplete="new-password" @unless($admin->exists) required @endunless placeholder="{{ $admin->exists ? 'Dəyişmək istəmirsinizsə boş qoyun' : 'Minimum 8 simvol' }}"></div>
    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked(old('is_active', $admin->is_active))><label class="form-check-label" for="ia">Aktiv</label></div>
  </div></div>
  <div class="mt-3 d-flex gap-2"><button class="btn btn-brand">Yadda saxla</button><a href="{{ route('admin.admins.index') }}" class="btn btn-light">Geri</a></div>
</form>
@endsection
