@extends('admin.layouts.app')

@section('title', 'Slayderlər')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <p class="text-muted mb-0">Slayderi ana səhifəyə <a href="{{ route('admin.home-sections.index') }}">Ana səhifə</a> bölməsindən əlavə edin.</p>
  <a href="{{ route('admin.sliders.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Yeni slayder</a>
</div>
<div class="card">
  <table class="table table-hover mb-0">
    <thead><tr><th>Ad</th><th>Ölçü</th><th>Slayd</th><th></th></tr></thead>
    <tbody>
      @forelse($sliders as $slider)
        <tr>
          <td><a href="{{ route('admin.sliders.edit', $slider) }}" class="fw-semibold">{{ $slider->name }}</a></td>
          <td>{{ $slider->width }}×{{ $slider->height }}</td>
          <td>{{ $slider->slides_count }}</td>
          <td class="text-end">
            <a href="{{ route('admin.sliders.edit', $slider) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            <form method="post" action="{{ route('admin.sliders.destroy', $slider) }}" class="d-inline" data-confirm="Slayder silinsin?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">Slayder yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
