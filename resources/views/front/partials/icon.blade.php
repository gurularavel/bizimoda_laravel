{{-- Header və digər yerlər üçün xətti SVG ikonlar: @include('front.partials.icon', ['name' => 'user']) --}}
@php
    $paths = [
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 20.5c1.4-3.8 4.4-5.5 8-5.5s6.6 1.7 8 5.5"/>',
        'user-plus' => '<circle cx="10" cy="8" r="4"/><path d="M3 20.5c1.3-3.8 4-5.5 7-5.5 1.3 0 2.5.3 3.6.9M19 14v6M16 17h6"/>',
        'logout' => '<path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 16l-4-4 4-4M6 12h10"/>',
        'cart' => '<path d="M3 4h2l2.2 10.2a1.5 1.5 0 0 0 1.5 1.2h8.6a1.5 1.5 0 0 0 1.5-1.1L20.5 8H6.2"/><circle cx="9.5" cy="19.5" r="1.2"/><circle cx="17" cy="19.5" r="1.2"/>',
        'heart' => '<path d="M12 20.5s-7.5-4.6-7.5-10.1A4.4 4.4 0 0 1 12 7.6a4.4 4.4 0 0 1 7.5 2.8c0 5.5-7.5 10.1-7.5 10.1z"/>',
    ];
@endphp
<svg class="bz-ico {{ $class ?? '' }}" viewBox="0 0 24 24" width="{{ $size ?? 24 }}" height="{{ $size ?? 24 }}" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
