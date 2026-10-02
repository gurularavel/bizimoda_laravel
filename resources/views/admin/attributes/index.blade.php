@extends('admin.layouts.app')

@section('title', 'Xüsusiyyətlər')

@section('content')
<div class="row g-3">
  <div class="col-lg-8">
    <div class="d-flex justify-content-between mb-3">
      <p class="text-muted mb-0">Material, tərz və s. Məhsulun "Xüsusiyyətlər" cədvəlində və kateqoriya filtrlərində istifadə olunur.</p>
      <a href="{{ route('admin.attributes.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Yeni xüsusiyyət</a>
    </div>
    <div class="card">
      <table class="table table-hover mb-0">
        <thead><tr><th>Ad</th><th>Qrup</th><th>Dəyərlər</th><th></th></tr></thead>
        <tbody>
          @forelse($attributes as $attribute)
            <tr>
              <td><a href="{{ route('admin.attributes.edit', $attribute) }}" class="fw-semibold">{{ $attribute->name }}</a></td>
              <td>{{ $attribute->group?->name }}</td>
              <td>@foreach($attribute->values as $v)<span class="badge badge-soft me-1">{{ $v->value }}</span>@endforeach</td>
              <td class="text-end">
                <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                <form method="post" action="{{ route('admin.attributes.destroy', $attribute) }}" class="d-inline" data-confirm="Silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Xüsusiyyət yoxdur</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Xüsusiyyət qrupları</div>
      <ul class="list-group list-group-flush">
        @foreach($groups as $group)
          <li class="list-group-item d-flex justify-content-between align-items-center">{{ $group->name }}
            <form method="post" action="{{ route('admin.attribute-groups.destroy', $group) }}" data-confirm="Qrup silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button></form>
          </li>
        @endforeach
      </ul>
      <div class="card-body border-top">
        <form method="post" action="{{ route('admin.attribute-groups.store') }}">
          @csrf
          <x-admin.trans name="name" label="Yeni qrup" required />
          <button class="btn btn-sm btn-brand">Əlavə et</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
