@php $slider = $section->payload; @endphp
@if($slider && $slider->activeSlides->isNotEmpty())
  @php $w = $slider->width ?: 1590; $h = $slider->height ?: 458; @endphp
  <div class="module module-master_slider module-master_slider-26" style="background-image:url('')">
    <div class="journal-loading"><i class="fa fa-spinner fa-spin"></i></div>
    <img src="{{ thumb($slider->activeSlides->first()->image, $w, $h, 'cover') }}" alt="" width="{{ $w }}" height="{{ $h }}" />
    <div class="master-slider ms-skin-minimal" data-options='{"width":{{ $w }},"height":{{ $h }},"layout":"fillwidth","smoothHeight":false,"centerControls":false,"parallaxMode":"swipe","instantStartLayers":true,"loop":true,"dir":"h","autoHeight":true,"rtl":false,"startOnAppear":false,"autoplay":true,"overPause":true,"shuffle":false,"view":"fadeWave","speed":"15","swipe":false,"mouse":true}'>
      @foreach($slider->activeSlides as $slide)
        <div class="module-item module-item-{{ $loop->iteration }} ms-slide" data-delay="2.5">
          <img src="{{ thumb($slide->image, $w, $h, 'cover') }}" srcset="{{ thumb($slide->image, $w, $h, 'cover') }} 1x" alt="{{ $slide->title }}" width="{{ $w }}" height="{{ $h }}"/>
          @if($slide->link)
            <a href="{{ $slide->link }}" class="ms-layer" style="position:absolute;inset:0;display:block" data-type="text"></a>
          @endif
        </div>
      @endforeach
    </div>
  </div>
@endif
