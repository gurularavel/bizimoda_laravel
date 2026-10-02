@php
    $admin = auth('admin')->user();
    $nav = [
        ['dashboard', 'admin.dashboard', 'speedometer2', 'İdarə paneli', []],
        ['sales', 'admin.orders.index', 'bag-check', 'Sifarişlər', ['admin.orders.*']],
        ['customers', 'admin.customers.index', 'people', 'Müştərilər', ['admin.customers.*']],
        ['forms', 'admin.forms.index', 'envelope', 'Müraciətlər', ['admin.forms.*']],
        ['heading', null, null, 'Kataloq', []],
        ['catalog', 'admin.products.index', 'box-seam', 'Məhsullar', ['admin.products.*']],
        ['catalog', 'admin.categories.index', 'diagram-3', 'Kateqoriyalar', ['admin.categories.*']],
        ['catalog', 'admin.options.index', 'palette', 'Opsiyonlar (rəng, ölçü)', ['admin.options.*']],
        ['catalog', 'admin.attributes.index', 'list-check', 'Xüsusiyyətlər', ['admin.attributes.*']],
        ['catalog', 'admin.discounts.index', 'percent', 'Endirimlər', ['admin.discounts.*']],
        ['catalog', 'admin.brands.index', 'award', 'Brendlər', ['admin.brands.*']],
        ['catalog', 'admin.reviews.index', 'chat-square-text', 'Rəylər', ['admin.reviews.*']],
        ['heading', null, null, 'Kontent', []],
        ['content', 'admin.menus.index', 'menu-button-wide', 'Menyular', ['admin.menus.*']],
        ['content', 'admin.pages.index', 'file-earmark-text', 'Səhifələr', ['admin.pages.*']],
        ['content', 'admin.blog-posts.index', 'journal-richtext', 'Bloq yazıları', ['admin.blog-posts.*']],
        ['content', 'admin.blog-categories.index', 'folder2', 'Bloq kateqoriyaları', ['admin.blog-categories.*']],
        ['content', 'admin.home-sections.index', 'house', 'Ana səhifə', ['admin.home-sections.*']],
        ['content', 'admin.sliders.index', 'images', 'Slayderlər', ['admin.sliders.*']],
        ['heading', null, null, 'Sistem', []],
        ['system', 'admin.settings.edit', 'gear', 'Parametrlər', ['admin.settings.*']],
        ['system', 'admin.languages.index', 'translate', 'Dillər', ['admin.languages.*']],
        ['system', 'admin.translations.index', 'type', 'Tərcümələr', ['admin.translations.*']],
        ['system', 'admin.redirects.index', 'signpost-split', 'Redirect-lər', ['admin.redirects.*']],
        ['system', 'admin.admins.index', 'shield-lock', 'Adminlər', ['admin.admins.*']],
    ];
    $newOrders = $admin->canAccess('sales') ? \App\Models\Order::query()->where('status', 'new')->count() : 0;
    $newForms = $admin->canAccess('forms') ? \App\Models\FormSubmission::query()->where('is_read', false)->count() : 0;
@endphp
<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') — {{ setting('site.name', 'bizimoda') }}</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
  <link href="{{ asset('admin-assets/admin.css') }}?v={{ filemtime(public_path('admin-assets/admin.css')) }}" rel="stylesheet">
  @stack('styles')
</head>
<body>
{{-- Sidebar vəziyyəti (yığılmış/açıq) yadda saxlanılır; səhifə yüklənəndə titrəmə olmasın deyə body-dən dərhal sonra tətbiq olunur --}}
<script>try { if (localStorage.getItem('admin.sidebar') === 'collapsed') { document.body.classList.add('sidebar-collapsed'); } } catch (e) {}</script>
<div class="admin-wrap">
  <aside class="admin-sidebar" id="adminSidebar">
    <a href="{{ route('admin.dashboard') }}" class="brand"><img src="{{ asset('images/logo.png') }}" alt="bizimoda" class="brand-logo"><img src="{{ asset('images/favicon.png') }}" alt="bizimoda" class="brand-icon"><span>admin</span></a>
    <nav>
      @foreach($nav as [$perm, $route, $icon, $label, $patterns])
        @if($perm === 'heading')
          <div class="nav-heading"><span>{{ $label }}</span></div>
        @elseif($admin->canAccess($perm))
          @php $active = request()->routeIs($route) || ($patterns && request()->routeIs(...$patterns)); @endphp
          <a href="{{ route($route) }}" class="nav-link {{ $active ? 'active' : '' }}" data-title="{{ $label }}">
            <i class="bi bi-{{ $icon }}"></i><span class="nav-text">{{ $label }}</span>
            @if($route === 'admin.orders.index' && $newOrders)<span class="badge bg-danger ms-auto nav-badge">{{ $newOrders }}</span>@endif
            @if($route === 'admin.forms.index' && $newForms)<span class="badge bg-warning text-dark ms-auto nav-badge">{{ $newForms }}</span>@endif
          </a>
        @endif
      @endforeach
    </nav>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="btn btn-sm btn-light d-lg-none" type="button" onclick="document.getElementById('adminSidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
      <button class="btn btn-sm btn-light d-none d-lg-inline-block js-sidebar-toggle" type="button" title="Menyunu gizlət / göstər"><i class="bi bi-layout-sidebar-inset"></i></button>
      <h1 class="h5 mb-0">@yield('title')</h1>
      <div class="ms-auto d-flex align-items-center gap-2">
        <a href="{{ url('/') }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-up-right"></i> Sayt</a>
        <div class="dropdown">
          <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> {{ $admin->name }}</button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text small text-muted">{{ \App\Models\Admin::ROLES[$admin->role] ?? $admin->role }}</span></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Çıxış</button></form>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <main class="admin-content">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">
          <strong>Formada xətalar var:</strong>
          <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
      @endif
      @yield('content')
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
  window.ADMIN = { uploadUrl: @json(route('admin.upload')), productSearchUrl: @json(route('admin.products.search')) };
</script>
<script src="{{ asset('admin-assets/admin.js') }}?v={{ filemtime(public_path('admin-assets/admin.js')) }}"></script>
@stack('scripts')
</body>
</html>
