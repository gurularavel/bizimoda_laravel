@extends('admin.layouts.app')

@section('title', 'Adminlər')

@section('content')
<div class="d-flex justify-content-end mb-3"><a href="{{ route('admin.admins.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Yeni admin</a></div>
<div class="card">
  <table class="table mb-0">
    <thead><tr><th>Ad</th><th>E-poçt</th><th>Rol</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @foreach($admins as $a)
        <tr>
          <td>{{ $a->name }}</td>
          <td>{{ $a->email }}</td>
          <td>{{ \App\Models\Admin::ROLES[$a->role] ?? $a->role }}</td>
          <td>{!! $a->is_active ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-secondary">Deaktiv</span>' !!}</td>
          <td class="text-end">
            <a href="{{ route('admin.admins.edit', $a) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            @if($a->id !== auth('admin')->id())
              <form method="post" action="{{ route('admin.admins.destroy', $a) }}" class="d-inline" data-confirm="Admin silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
