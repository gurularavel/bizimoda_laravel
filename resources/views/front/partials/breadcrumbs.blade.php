@php
    $crumbs = array_merge([['title' => null, 'url' => lroute('front.home')]], $items ?? []);
@endphp
<ul class="breadcrumb">
  @foreach($crumbs as $crumb)
    <li><a href="{{ $crumb['url'] }}">@if($loop->first)<i class="fa fa-home"></i>@else{{ $crumb['title'] }}@endif</a></li>
  @endforeach
</ul>
@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@'.'context' => 'http://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => collect($crumbs)->values()->map(fn ($c, $i) => [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'item' => ['@id' => $c['url'], 'name' => $c['title'] ?? __('Baş səhifə')],
    ])->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
