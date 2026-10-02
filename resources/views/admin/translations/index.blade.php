@extends('admin.layouts.app')

@section('title', 'Tərcümələr (interfeys)')

@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <form class="d-flex gap-2" method="get">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Axtar...">
    <label class="d-flex align-items-center gap-1 text-nowrap small"><input type="checkbox" name="missing" value="1" @checked(request('missing'))> yalnız tərcüməsizlər</label>
    <button class="btn btn-light">Filtr</button>
  </form>
  <form method="post" action="{{ route('admin.translations.scan') }}">@csrf<button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Koddan yeni sözləri tap</button></form>
</div>
<p class="text-muted small">Saytın düymə, başlıq və mesajları. Açar — {{ strtoupper($default) }} mətnidir. Məhsul, kateqoriya, səhifə adları isə öz formalarında tərcümə olunur.</p>
<form method="post" action="{{ route('admin.translations.update') }}">
  @csrf
  <div class="card">
    <div class="table-responsive" style="max-height:70vh">
      <table class="table table-sm align-middle mb-0">
        <thead class="sticky-top bg-white"><tr>@foreach($locales as $l)<th>{{ strtoupper($l) }}</th>@endforeach</tr></thead>
        <tbody>
          @forelse($keys as $key)
            @php $enc = base64_encode($key); @endphp
            <tr>
              @foreach($locales as $l)
                <td style="min-width:240px">
                  <textarea name="t[{{ $enc }}][{{ $l }}]" rows="1" class="form-control form-control-sm {{ trim((string) ($data[$l][$key] ?? '')) === '' && $l !== $default ? 'border-warning' : '' }}" placeholder="{{ $l === $default ? $key : '' }}">{{ $data[$l][$key] ?? '' }}</textarea>
                </td>
              @endforeach
            </tr>
          @empty
            <tr><td colspan="{{ count($locales) }}" class="text-center text-muted py-4">Tərcümə yoxdur. "Koddan yeni sözləri tap" düyməsini basın.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="sticky-actions mt-3"><button class="btn btn-brand"><i class="bi bi-check-lg"></i> Yadda saxla</button> <small class="text-muted ms-2">{{ count($keys) }} sətir</small></div>
</form>
@endsection
