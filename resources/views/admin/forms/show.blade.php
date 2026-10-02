@extends('admin.layouts.app')

@section('title', 'Müraciət #'.$item->id)

@section('content')
<div class="card" style="max-width:720px">
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">Tip</dt><dd class="col-sm-9">{{ \App\Models\FormSubmission::TYPES[$item->type] ?? $item->type }}</dd>
      <dt class="col-sm-3">Ad</dt><dd class="col-sm-9">{{ $item->name }}</dd>
      @if($item->phone)<dt class="col-sm-3">Telefon</dt><dd class="col-sm-9"><a href="tel:{{ $item->phone }}">{{ $item->phone }}</a></dd>@endif
      @if($item->email)<dt class="col-sm-3">E-poçt</dt><dd class="col-sm-9"><a href="mailto:{{ $item->email }}">{{ $item->email }}</a></dd>@endif
      @if($item->subject)<dt class="col-sm-3">{{ $item->type === 'one_click' ? 'Sifariş' : 'Mövzu' }}</dt><dd class="col-sm-9">
        @if($item->type === 'one_click' && ($order = \App\Models\Order::query()->where('number', $item->subject)->first()))<a href="{{ route('admin.orders.show', $order) }}">{{ $item->subject }}</a>@else{{ $item->subject }}@endif
      </dd>@endif
      @if($item->product)<dt class="col-sm-3">Məhsul</dt><dd class="col-sm-9"><a href="{{ route('admin.products.edit', $item->product) }}">{{ $item->product->name }}</a></dd>@endif
      @if($item->url)<dt class="col-sm-3">Səhifə</dt><dd class="col-sm-9"><a href="{{ $item->url }}" target="_blank">{{ $item->url }}</a></dd>@endif
      <dt class="col-sm-3">Tarix</dt><dd class="col-sm-9">{{ $item->created_at->format('d.m.Y H:i') }} ({{ strtoupper((string) $item->locale) }})</dd>
    </dl>
    @if($item->message)<hr><div style="white-space:pre-line">{{ $item->message }}</div>@endif
  </div>
  <div class="card-footer bg-white d-flex gap-2">
    <a href="{{ route('admin.forms.index') }}" class="btn btn-light">Geri</a>
    <form method="post" action="{{ route('admin.forms.destroy', $item) }}" class="ms-auto" data-confirm="Silinsin?">@csrf @method('DELETE')<button class="btn btn-outline-danger">Sil</button></form>
  </div>
</div>
@endsection
