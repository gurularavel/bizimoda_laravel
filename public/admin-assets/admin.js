/* bizimoda admin panel */
(function ($) {
	'use strict';

	var token = $('meta[name="csrf-token"]').attr('content');
	$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token } });

	window.adminInitEditors = function (scope) {
		if (!window.tinymce) {
			return;
		}
		$(scope || document).find('textarea.js-editor').each(function () {
			if (this.id && tinymce.get(this.id)) {
				return;
			}
			if (!this.id) {
				this.id = 'ed-' + Math.random().toString(36).slice(2);
			}
			tinymce.init({
				target: this,
				height: 320,
				menubar: false,
				branding: false,
				promotion: false,
				convert_urls: false,
				plugins: 'link image lists table code media autoresize',
				toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media table | removeformat code',
				images_upload_handler: function (blobInfo) {
					return new Promise(function (resolve, reject) {
						var fd = new FormData();
						fd.append('file', blobInfo.blob(), blobInfo.filename());
						$.ajax({ url: window.ADMIN.uploadUrl, type: 'post', data: fd, processData: false, contentType: false })
							.done(function (r) { resolve(r.location); })
							.fail(function () { reject('Yükləmə alınmadı'); });
					});
				},
				setup: function (editor) {
					editor.on('change', function () { editor.save(); });
				}
			});
		});
	};

	window.adminInitSelects = function (scope) {
		$(scope || document).find('select.js-select2').each(function () {
			if ($(this).data('select2')) {
				return;
			}
			$(this).select2({ theme: 'bootstrap-5', width: '100%', placeholder: $(this).data('placeholder') || '', allowClear: !this.multiple && !$(this).prop('required') });
		});

		$(scope || document).find('select.js-product-search').each(function () {
			if ($(this).data('select2')) {
				return;
			}
			var $s = $(this);
			$s.select2({
				theme: 'bootstrap-5',
				width: '100%',
				placeholder: $s.data('placeholder') || 'Məhsul axtar...',
				minimumInputLength: 1,
				ajax: {
					url: window.ADMIN.productSearchUrl,
					dataType: 'json',
					delay: 250,
					data: function (p) { return { q: p.term, type: $s.data('type') || '' }; },
					processResults: function (data) { return { results: data }; }
				}
			});
		});
	};

	$(function () {
		adminInitEditors();
		adminInitSelects();

		// Şəkil önizləmə
		$(document).on('change', '.js-image-input', function () {
			var file = this.files && this.files[0];
			var $preview = $(this).closest('.image-field').find('.image-preview');
			if (file) {
				var reader = new FileReader();
				reader.onload = function (e) { $preview.html('<img src="' + e.target.result + '">'); };
				reader.readAsDataURL(file);
			}
		});

		// Silmə/kritik əməliyyat təsdiqi
		$(document).on('submit', 'form[data-confirm]', function (e) {
			if (!confirm($(this).data('confirm'))) {
				e.preventDefault();
			}
		});

		// Formadan əvvəl TinyMCE məzmununu textarea-ya yaz
		$(document).on('submit', 'form', function () {
			if (window.tinymce) {
				tinymce.triggerSave();
			}
		});

		// Bootstrap tab-ı URL hash-ə görə aç
		if (location.hash) {
			var $tab = $('[data-bs-target="' + location.hash + '"]');
			if ($tab.length) {
				bootstrap.Tab.getOrCreateInstance($tab[0]).show();
			}
		}
		$(document).on('shown.bs.tab', '.js-hash-tabs [data-bs-toggle="tab"]', function (e) {
			history.replaceState(null, '', $(e.target).data('bs-target'));
		});
	});
})(jQuery);

/* Sidebar-ı gizlət / göstər (yığılanda yalnız ikonlar) — vəziyyət localStorage-da saxlanılır */
(function ($) {
	'use strict';

	var tooltips = [];

	function syncTooltips() {
		tooltips.forEach(function (t) { t.dispose(); });
		tooltips = [];
		if (!document.body.classList.contains('sidebar-collapsed')) {
			return;
		}
		$('#adminSidebar .nav-link').each(function () {
			tooltips.push(new bootstrap.Tooltip(this, { title: $(this).data('title'), placement: 'right', container: 'body', trigger: 'hover' }));
		});
	}

	$(function () {
		syncTooltips();
		$('.js-sidebar-toggle').on('click', function () {
			var collapsed = document.body.classList.toggle('sidebar-collapsed');
			try { localStorage.setItem('admin.sidebar', collapsed ? 'collapsed' : 'expanded'); } catch (e) {}
			syncTooltips();
		});
	});
})(jQuery);

/* Ağac accordion: alt elementləri aç/yığ, vəziyyət localStorage-da (ağac üzrə) saxlanılır */
(function ($) {
	'use strict';

	function key($tree) {
		return 'admin.tree.' + ($tree.data('storage-key') || 'default');
	}

	function load($tree) {
		try { return JSON.parse(localStorage.getItem(key($tree)) || '{}'); } catch (e) { return {}; }
	}

	function save($tree) {
		var state = {};
		$tree.find('.tree-item').each(function () {
			if ($(this).children('ul').children('li').length) {
				state[$(this).data('id')] = $(this).hasClass('is-collapsed') ? 0 : 1;
			}
		});
		try { localStorage.setItem(key($tree), JSON.stringify(state)); } catch (e) {}
	}

	$(function () {
		$('.js-tree').each(function () {
			var $tree = $(this);
			var state = load($tree);
			$tree.find('.tree-item').each(function () {
				var id = $(this).data('id');
				if (state.hasOwnProperty(id)) {
					$(this).toggleClass('is-collapsed', state[id] === 0);
				}
			});
		});

		$(document).on('click', '.js-tree-toggle', function () {
			var $item = $(this).closest('.tree-item');
			$item.toggleClass('is-collapsed');
			save($item.closest('.js-tree'));
		});

		$(document).on('click', '.js-tree-expand-all, .js-tree-collapse-all', function () {
			var $tree = $($(this).data('target'));
			var collapse = $(this).hasClass('js-tree-collapse-all');
			$tree.find('.tree-item').each(function () {
				if ($(this).children('ul').children('li').length) {
					$(this).toggleClass('is-collapsed', collapse);
				}
			});
			save($tree);
		});
	});
})(jQuery);
