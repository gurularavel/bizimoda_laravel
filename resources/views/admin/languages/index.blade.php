@extends('admin.layouts.app')

@section('title', 'Dillər')

@section('content')
<p class="text-muted">Saytın dilləri URL prefiksi ilə işləyir: <code>/az</code>, <code>/ru</code>, <code>/en</code>. Deaktiv dil saytda gizlənir və həmin dilin URL-ləri default dilə yönləndirilir. Mətnlərin tərcüməsi hər formada AZ/RU/EN tablarında, interfeys sözləri isə <a href="{{ route('admin.translations.index') }}">Tərcümələr</a> bölməsində.</p>
<div class="card">
  <table class="table mb-0">
    <thead><tr><th>Kod</th><th>Ad</th><th>Status</th><th>Default</th><th>Sıra</th><th></th></tr></thead>
    <tbody>
      @foreach($languages as $l)
        <tr>
          <td><img src="{{ config('locales.flags.'.$l->code) }}" width="16" height="11" alt=""> <code>{{ $l->code }}</code></td>
          <td>{{ $l->name }}</td>
          <td>{!! $l->is_active ? '<span class="badge bg-success">Aktiv</span>' : '<span class="badge bg-secondary">Deaktiv</span>' !!}</td>
          <td>@if($l->is_default)<i class="bi bi-star-fill text-warning"></i>@endif</td>
          <td>{{ $l->sort }}</td>
          <td class="text-end"><a href="{{ route('admin.languages.edit', $l) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
