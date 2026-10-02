@extends('admin.layouts.app')

@section('title', 'Opsiyonlar')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <p class="text-muted mb-0">Rəng, ölçü, material kimi seçimlər. Məhsul formasında hər məhsula ayrıca qoşulur və qiymət əlavəsi təyin olunur.</p>
  <a href="{{ route('admin.options.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Yeni opsiyon</a>
</div>
<div class="card">
  <table class="table table-hover mb-0">
    <thead><tr><th>Ad</th><th>Tip</th><th>Dəyərlər</th><th></th></tr></thead>
    <tbody>
      @forelse($options as $option)
        <tr>
          <td><a href="{{ route('admin.options.edit', $option) }}" class="fw-semibold">{{ $option->name }}</a></td>
          <td>{{ \App\Models\Option::TYPES[$option->type] ?? $option->type }}</td>
          <td>@foreach($option->values as $v)<span class="badge badge-soft me-1">@if($v->color)<span class="color-dot" style="background:{{ $v->color }};width:10px;height:10px"></span> @endif{{ $v->name }}</span>@endforeach</td>
          <td class="text-end">
            <a href="{{ route('admin.options.edit', $option) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            <form method="post" action="{{ route('admin.options.destroy', $option) }}" class="d-inline" data-confirm="Opsiyon silinsin? Məhsullardakı bağlantılar da silinəcək.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">Opsiyon yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
