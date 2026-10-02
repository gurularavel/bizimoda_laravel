@include('front.partials.products-carousel', [
    'products' => $section->payload,
    'title' => $section->title,
    'moduleClass' => $section->module_class ?: 'module-products-310',
])
