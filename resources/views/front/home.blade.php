@extends('front.layouts.app')

@push('styles')
<link href="catalog/view/theme/journal3/lib/masterslider/style/masterslider.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
<link href="catalog/view/theme/journal3/lib/masterslider/skins/minimal/style.css?v=14218c54" type="text/css" rel="stylesheet" media="all" />
@endpush
@push('libs')
<script src="catalog/view/theme/journal3/lib/masterslider/masterslider.js?v=14218c54"></script>
@endpush

@section('content')
<div id="top" class="top top-row">
  <div class="grid-rows">
    @foreach($sections as $section)
      <div class="grid-row grid-row-top-{{ $loop->iteration }}">
        <div class="grid-cols">
          <div class="grid-col grid-col-top-{{ $loop->iteration }}-1">
            <div class="grid-items">
              <div class="grid-item grid-item-top-{{ $loop->iteration }}-1-1">
                @include('front.sections.'.$section->type, ['section' => $section])
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endsection
