@extends('admin.layouts.app')

@section('title', 'Endirimlər')

@php
    $statusBadges = [
        'running' => ['success', 'Aktiv'],
        'scheduled' => ['info text-dark', 'Planlaşdırılıb'],
        'expired' => ['secondary', 'Bitib'],
        'disabled' => ['light text-muted border', 'Dayandırılıb'],
    ];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Məhsullara, kateqoriyalara (alt kateqoriyalar daxil) və ya bütün kataloqa faiz və ya məbləğ endirimi. Bir məhsula bir neçə endirim düşərsə ən sərfəlisi tətbiq olunur.</p>
  <a href="{{ route('admin.discounts.create') }}" class="btn btn-brand text-nowrap"><i class="bi bi-plus-lg"></i> Yeni endirim</a>
</div>
<div class="card">
  <table class="table table-hover mb-0">
    <thead><tr><th>Ad</th><th>Endirim</th><th>Tətbiq</th><th>Müddət</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse($discounts as $d)
        @php [$color, $label] = $statusBadges[$d->status()]; @endphp
        <tr>
          <td><a href="{{ route('admin.discounts.edit', $d) }}" class="fw-semibold">{{ $d->name }}</a></td>
          <td><span class="badge bg-danger">-{{ $d->label() }}</span></td>
          <td>
            {{ \App\Models\Discount::SCOPES[$d->applies_to] }}
            @if($d->applies_to === 'products')<small class="text-muted">({{ $d->products_count }})</small>@endif
            @if($d->applies_to === 'categories')<small class="text-muted">({{ $d->categories_count }})</small>@endif
          </td>
          <td><small>{{ $d->starts_at?->format('d.m.Y H:i') ?? '—' }} → {{ $d->ends_at?->format('d.m.Y H:i') ?? 'müddətsiz' }}</small></td>
          <td><span class="badge bg-{{ $color }}">{{ $label }}</span></td>
          <td class="text-end text-nowrap">
            <form method="post" action="{{ route('admin.discounts.toggle', $d) }}" class="d-inline">@csrf @method('PATCH')
              <button class="btn btn-sm btn-light" title="{{ $d->is_active ? 'Dayandır' : 'Aktivləşdir' }}"><i class="bi bi-{{ $d->is_active ? 'pause' : 'play' }}"></i></button>
            </form>
            <a href="{{ route('admin.discounts.edit', $d) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
            <form method="post" action="{{ route('admin.discounts.destroy', $d) }}" class="d-inline" data-confirm="Endirim silinsin? Qiymətlər əvvəlki vəziyyətə qayıdacaq.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Endirim yoxdur</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
