@extends('admin.layouts.app')

@section('title', 'Kateqoriyalar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0"><i class="bi bi-arrows-move"></i> Sıralamaq və iç-içə yerləşdirmək üçün sürükləyin. Dəyişikliklər avtomatik yadda saxlanılır.</p>
  <div class="d-flex gap-1">
    <button type="button" class="btn btn-light js-tree-expand-all" data-target="#category-tree" title="Hamısını aç"><i class="bi bi-arrows-expand"></i></button>
    <button type="button" class="btn btn-light js-tree-collapse-all" data-target="#category-tree" title="Hamısını yığ"><i class="bi bi-arrows-collapse"></i></button>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Yeni kateqoriya</a>
  </div>
</div>
<div class="card">
  <div class="card-body">
    <ul class="tree-list js-tree" id="category-tree" data-storage-key="categories" data-reorder-url="{{ route('admin.categories.reorder') }}">
      @include('admin.categories._branch', ['nodes' => $tree])
    </ul>
    <div class="text-success small mt-2 d-none js-tree-saved"><i class="bi bi-check2"></i> Sıralama yadda saxlanıldı</div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    var $root = $('.js-tree');
    function serialize($ul) {
      return $ul.children('li').map(function () {
        return { id: $(this).data('id'), children: serialize($(this).children('ul')) };
      }).get();
    }
    function save() {
      $.post($root.data('reorder-url'), { tree: JSON.stringify(serialize($root)) }, function () {
        $('.js-tree-saved').removeClass('d-none').delay(1500).queue(function (n) { $(this).addClass('d-none'); n(); });
      });
    }
    $root.find('ul').addBack().each(function () {
      new Sortable(this, { group: 'tree', handle: '.drag-handle', animation: 150, fallbackOnBody: true, swapThreshold: 0.65, onEnd: save });
    });
  })();
</script>
@endpush
