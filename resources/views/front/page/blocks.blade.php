@foreach($blocks as $block)
  <div class="page-block page-block-{{ $block->type }}" style="margin-bottom:25px">
    @switch($block->type)
      @case('text')
        <div class="content">{!! $block->t('content') !!}</div>
        @break

      @case('html')
        {!! $block->data['html'] ?? '' !!}
        @break

      @case('image')
        @if(! empty($block->data['image']))
          <a @if(! empty($block->data['link'])) href="{{ $block->data['link'] }}" @endif>
            <img src="{{ image_url($block->data['image']) }}" alt="{{ $block->t('alt') }}" class="img-responsive"/>
          </a>
        @endif
        @break

      @case('banner')
        @include('front.sections.banners', ['section' => (object) ['title' => $block->t('title'), 'data' => $block->data]])
        @break

      @case('products')
        @include('front.partials.products-carousel', [
            'products' => $blockProducts[$block->id] ?? collect(),
            'title' => $block->t('title'),
            'moduleClass' => 'module-products-310',
        ])
        @break

      @case('faq')
        @php $faqId = 'faq-'.$block->id; @endphp
        @if($block->t('title'))<h3 class="title module-title">{{ $block->t('title') }}</h3>@endif
        <div class="panel-group" id="{{ $faqId }}">
          @foreach($block->data['items'] ?? [] as $i => $faq)
            <div class="panel panel-default">
              <div class="panel-heading">
                <h4 class="panel-title"><a href="#{{ $faqId }}-{{ $i }}" class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#{{ $faqId }}">{{ $block->t('q', $faq['q'] ?? '') }} <i class="fa fa-caret-down"></i></a></h4>
              </div>
              <div id="{{ $faqId }}-{{ $i }}" class="panel-collapse collapse">
                <div class="panel-body">{!! nl2br(e($block->t('a', $faq['a'] ?? ''))) !!}</div>
              </div>
            </div>
          @endforeach
        </div>
        @break

      @case('form')
        @include('front.partials.contact-form', ['title' => $block->t('title')])
        @break
    @endswitch
  </div>
@endforeach
