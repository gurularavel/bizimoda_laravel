/**
 * Journal3 (OpenCart) JS-in Laravel ilə işləməsi üçün körpü.
 * - Bütün jQuery AJAX sorğularına CSRF token əlavə edir.
 * - "ajax/{route}" sorğuları <base href> sayəsində "/ajax/..." ünvanına gedir və LegacyController-də emal olunur.
 */
(function ($) {
	var meta = document.querySelector('meta[name="csrf-token"]');

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': meta ? meta.getAttribute('content') : '',
			'X-Requested-With': 'XMLHttpRequest'
		}
	});

	// OpenCart common.js-dəki getURLVar('route') səbət səhifəsini tanısın
	window.bizimodaRoute = document.documentElement.className.match(/route-([a-z0-9-]+)/);
})(jQuery);
