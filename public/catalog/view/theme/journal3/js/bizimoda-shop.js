/**
 * bizimoda: məhsul səhifəsi (dəst modulları, opsiyonlar, canlı qiymət, səbətə at),
 * rəy forması və səbət səhifəsinin say düymələri.
 */
(function ($) {
	'use strict';

	// Quickview iframe-dən çağırılanda bildiriş və səbət valideyn səhifədə yenilənir
	var P = (window.parent && window.parent !== window && window.parent.jQuery) ? window.parent : window;

	function handleCartResponse(json) {
		$('.alert-dismissible, .text-danger').remove();

		if (json.redirect) {
			P.location = json.redirect;
			return;
		}

		if (json.error) {
			var message = typeof json.error === 'string' ? json.error : $.map(json.error, function (v) { return v; }).join('<br>');
			$('.js-config-error').html(message).show();
			return;
		}

		if (json.success) {
			$('.js-config-error').hide();

			if (P !== window) {
				P.$('.popup-wrapper .popup-close').trigger('click');
			}

			if (json.notification && P.show_notification) {
				P.show_notification(json.notification);
			} else {
				P.$('header').after('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json.success + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}

			P.$('#cart-total').html(json.total);
			P.$('#cart-items,.cart-badge').html(json.items_count);
			P.$('#cart-items,.cart-badge').toggleClass('count-zero', !json.items_count);
			P.$('.cart-content ul').load('ajax/common/cart/info ul li');
		}
	}

	window.bizimodaHandleCartResponse = handleCartResponse;

	// Səbət bildirişi: əvvəlki eyni bildirişi bağla, avtomatik bağlanma üçün irəliləyiş zolağı göstər
	if (window.show_notification && !window.show_notification.__bz) {
		var originalShowNotification = window.show_notification;
		window.show_notification = function (opts) {
			if (opts && /bz-toast/.test(opts.className || '')) {
				$('.notification.bz-toast').remove();
			}
			var $n = originalShowNotification.apply(this, arguments);
			var timeout = (opts && opts.timeout) || (window.Journal && Journal.notificationHideAfter);
			if ($n && $n.hasClass('bz-toast') && timeout) {
				$n.append('<span class="bz-toast__progress" style="animation-duration:' + parseInt(timeout, 10) + 'ms"></span>');
			}
			return $n;
		};
		window.show_notification.__bz = true;
	}

	// "Bir kliklə al" popup-ı (iframe) hansı məhsul üçün açıldığını bilsin
	$(document).on('click', '.btn-extra', function () {
		var $thumb = $(this).closest('.bz-card, .product-thumb');
		window.__popup_product = {
			id: $(this).data('product_id'),
			name: $.trim($thumb.length ? $thumb.find('.bz-card__name, .name a').first().text() : $('#product .page-title').first().text())
		};
	});

	// Axtarış təklifləri (typeahead) — journal.js şablonu bu funksiyanı çağırır
	var AZ_MAP = { 'İ': 'i', 'I': 'i', 'ı': 'i', 'ə': 'e', 'Ə': 'e', 'ö': 'o', 'Ö': 'o', 'ü': 'u', 'Ü': 'u', 'ş': 's', 'Ş': 's', 'ç': 'c', 'Ç': 'c', 'ğ': 'g', 'Ğ': 'g' };

	function normalizeAz(text) {
		// Hər simvol bir simvola çevrilir — indekslər orijinal mətnlə üst-üstə düşür
		return String(text).replace(/[İIıəƏöÖüÜşŞçÇğĞ]/g, function (ch) { return AZ_MAP[ch]; }).replace(/[A-Z]/g, function (ch) { return ch.toLowerCase(); });
	}

	function highlight(html, query) {
		var words = normalizeAz(query || '').split(/\s+/).filter(function (w) { return w.length > 0; });
		if (!words.length) {
			return html;
		}
		var norm = normalizeAz(html);
		var marks = new Array(html.length + 1).join('0').split('');
		words.forEach(function (w) {
			var from = 0, at;
			while ((at = norm.indexOf(w, from)) !== -1) {
				for (var i = at; i < at + w.length; i++) { marks[i] = '1'; }
				from = at + w.length;
			}
		});
		var out = '', open = false;
		for (var i = 0; i < html.length; i++) {
			if (marks[i] === '1' && !open) { out += '<mark>'; open = true; }
			if (marks[i] !== '1' && open) { out += '</mark>'; open = false; }
			out += html.charAt(i);
		}
		return out + (open ? '</mark>' : '');
	}

	window.bzSearchSuggestion = function (data) {
		var active = document.activeElement;
		var query = active && /search-input/.test(active.className) ? active.value : $('.search-input.tt-input').filter(function () { return this.value; }).first().val();

		if (data.view_more) {
			return '<div class="search-result bz-sr-more"><a href="' + data.href + '">' + data.name + '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a></div>';
		}

		if (data.no_results) {
			return '<div class="search-result bz-sr-empty"><a><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>' + data.name + '</a></div>';
		}

		if (data.category) {
			return '<div class="search-result bz-sr-cat"><a href="' + data.href + '">' +
				'<span class="bz-sr-cat__icon"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v6H4zM14 15h6v6h-6z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></span>' +
				'<span class="bz-sr-cat__name"><span class="bz-sr-cat__title">' + highlight(data.name, query) + '</span>' + (data.parent ? ' <small>' + data.parent + '</small>' : '') + '</span>' +
				'<span class="bz-sr-cat__count">' + data.count + '</span></a></div>';
		}

		var html = '<div class="search-result bz-sr-product' + (data.quantity <= 0 ? ' is-out' : '') + '"><a href="' + data.href + '">';
		if (data.thumb) {
			html += '<img src="' + data.thumb + '" srcset="' + data.thumb + ' 1x, ' + data.thumb2 + ' 2x" alt="" width="48" height="48"/>';
		}
		html += '<span class="bz-sr-product__info"><span class="bz-sr-product__name">' + highlight(data.name, query) + '</span>';
		if (data.category_name) {
			html += '<small>' + data.category_name + '</small>';
		}
		html += '</span><span class="bz-sr-product__price">';
		if (data.special) {
			html += '<span class="bz-price">' + data.special + '</span><span class="bz-price-old">' + data.price + '</span>';
		} else if (data.price_value) {
			html += '<span class="bz-price">' + data.price + '</span>';
		}
		html += '</span></a></div>';

		return html;
	};

	// Mobil filtr: Journal bunu yalnız User-Agent "phone" olanda edir; biz mobil header aktiv olanda edirik
	function moveFilterToMobilePanel() {
		var $filter = $('.side-column .module-filter, #column-left .module-filter').first();
		if (!$filter.length || !$('html').hasClass('mobile-header-active')) {
			return;
		}
		var $header = $('.mobile-filter-container .mobile-wrapper-header');
		if (!$header.children().length) {
			$filter.find('> h3 > *').prependTo($header);
		}
		$filter.appendTo('.mobile-filter-wrapper');

		// Paneli bağlayıb nəticələrə qayıtmaq üçün düymə (filtr AJAX ilə dərhal tətbiq olunur)
		if (!$('.mobile-filter-container .bz-filter-footer').length) {
			$('<div class="bz-filter-footer"><button type="button" class="bz-btn bz-btn--primary bz-btn--block close-filter"></button></div>')
				.find('button').text($('.js-open-filter').data('apply-text') || 'OK').end()
				.appendTo('.mobile-filter-container');
		}
	}

	$(document).on('click', '.js-open-filter', function (e) {
		e.stopPropagation();
		moveFilterToMobilePanel();
		var $container = $('.mobile-filter-container');
		$('html').addClass('mobile-overlay mobile-filter-container-open');
		$container.outerWidth();
		$container.addClass('animating');
		return false;
	});

	$(function () {
		moveFilterToMobilePanel();
	});

	// Ümumi say inputu (.bz-qty): kartlar və məhsul səhifəsi. Dəst modulları və səbət öz handler-ləri ilə işləyir.
	function clampQty($input, value) {
		var $box = $input.closest('.bz-qty');
		var min = parseInt($box.data('min'), 10) || 1;
		var qty = parseInt(value, 10);
		qty = isNaN(qty) ? min : Math.max(min, Math.min(999, qty));
		if (String(qty) !== $input.val()) {
			$input.val(qty).trigger('change');
		}
		$box.find('[data-step="-1"]').prop('disabled', qty <= min);
	}

	$(document).on('click', '.bz-qty:not(.js-custom-qty) .bz-qty__btn', function () {
		var $input = $(this).closest('.bz-qty').find('.bz-qty__input');
		clampQty($input, (parseInt($input.val(), 10) || 0) + parseInt($(this).data('step'), 10));
	});

	$(document).on('blur', '.bz-qty:not(.js-custom-qty) .bz-qty__input', function () {
		clampQty($(this), $(this).val());
	});

	$(document).on('keydown', '.bz-qty:not(.js-custom-qty) .bz-qty__input', function (e) {
		if (e.which === 38 || e.which === 40) {
			e.preventDefault();
			clampQty($(this), (parseInt($(this).val(), 10) || 0) + (e.which === 38 ? 1 : -1));
		}
	});

	$(function () {
		$('.bz-qty:not(.js-custom-qty) .bz-qty__input').each(function () {
			var min = parseInt($(this).closest('.bz-qty').data('min'), 10) || 1;
			$(this).closest('.bz-qty').find('[data-step="-1"]').prop('disabled', (parseInt($(this).val(), 10) || 0) <= min);
		});
	});

	$(function () {
		var $product = $('#product[data-price-url]');

		if ($product.length) {
			var priceUrl = $product.data('price-url');
			var cartUrl = $product.data('cart-url');
			var timer = null;

			var collect = function () {
				var data = { quantity: parseInt($('#product-quantity').val(), 10) || 1 };

				$product.find('.js-set-modules .count-input').each(function () {
					data['components[' + $(this).data('product_id') + ']'] = parseInt($(this).val(), 10) || 0;
				});

				$product.find('.js-product-options select, .js-product-options input[type="radio"]:checked').each(function () {
					data[$(this).attr('name')] = $(this).val();
				});

				return data;
			};

			var seq = 0;

			var refreshPrice = function () {
				clearTimeout(timer);
				timer = setTimeout(function () {
					var current = ++seq;

					$.ajax({
						url: priceUrl,
						type: 'post',
						data: collect(),
						dataType: 'json',
						success: function (json) {
							if (current !== seq) {
								return; // daha yeni sorğu göndərilib
							}

							$product.find('.js-unit-price').html(json.total);

							var $old = $product.find('.js-unit-old-price');
							if (json.old_total) {
								$old.html(json.old_total).closest('.product-price-old').show();
							} else {
								$old.closest('.product-price-old').hide();
							}

							if (json.error) {
								$('.js-config-error').html(json.error).show();
							} else {
								$('.js-config-error').hide();
							}
						}
					});
				}, 150);
			};

			// Dəst modulları: say düymələri (min/max/məcburi qaydaları ilə)
			$product.on('click', '.js-set-modules .count_up, .js-set-modules .count_down', function () {
				var $input = $(this).closest('.count-input-wrapper').find('.count-input');
				var qty = parseInt($input.val(), 10) || 0;
				var min = parseInt($input.data('min_qty'), 10) || 1;
				var max = parseInt($input.data('max_qty'), 10) || 0;
				var required = String($input.data('required')) === '1';

				if ($(this).hasClass('count_up')) {
					qty = qty < min ? min : qty + 1;
					if (max && qty > max) {
						qty = max;
					}
				} else {
					qty = qty - 1;
					if (qty < min) {
						// minimum saydan az seçmək olmaz: ya minimum qalır, ya da modul tam çıxarılır
						qty = required ? min : 0;
					}
				}

				$input.val(qty);
				$input.closest('.js-set-row').attr('data-state', qty > 0 ? 1 : 0).toggleClass('module-disabled', qty === 0);
				refreshPrice();
			});

			$product.on('change', '.js-product-options select, .js-product-options input', refreshPrice);
			$product.on('change', '#product-quantity', refreshPrice);
			$product.on('click', '.stepper .fa-angle-up, .stepper .fa-angle-down', function () {
				setTimeout(refreshPrice, 50);
			});

			$('#button-cart').on('click', function () {
				var $btn = $(this);

				$.ajax({
					url: cartUrl,
					type: 'post',
					data: collect(),
					dataType: 'json',
					beforeSend: function () {
						$btn.button('loading');
					},
					complete: function () {
						$btn.button('reset');
					},
					success: handleCartResponse,
					error: function (xhr) {
						var json = xhr.responseJSON || {};
						if (json.errors) {
							handleCartResponse({ error: $.map(json.errors, function (v) { return v[0]; }) });
						} else if (json.message) {
							handleCartResponse({ error: json.message });
						}
					}
				});
			});
		}

		// Rəy forması
		$('#button-review').on('click', function () {
			var $btn = $(this);
			var $form = $('#form-review');

			$.ajax({
				url: $form.data('action'),
				type: 'post',
				data: $form.serialize(),
				dataType: 'json',
				beforeSend: function () { $btn.button('loading'); },
				complete: function () { $btn.button('reset'); },
				success: function (json) {
					$('.alert-dismissible').remove();
					$('#review').after('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json.success + '</div>');
					$form.find('input[name="name"], textarea').val('');
					$form.find('input[name="rating"]:checked').prop('checked', false);
				},
				error: function (xhr) {
					$('.alert-dismissible').remove();
					var errors = (xhr.responseJSON && xhr.responseJSON.errors) || {};
					var message = $.map(errors, function (v) { return v[0]; }).join('<br>') || 'Error';
					$('#review').after('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + message + '</div>');
				}
			});
		});

		// Səbət səhifəsi: say düymələri
		$('.cart-page').on('click', '.pr-count .count_up, .pr-count .count_down', function () {
			var $input = $(this).closest('.count-input-wrapper').find('.count-input');
			var qty = parseInt($input.val(), 10) || 1;
			var min = parseInt($input.data('min'), 10) || 1;
			var next = $(this).hasClass('count_up') ? qty + 1 : Math.max(min, qty - 1);
			if (next === qty) {
				return;
			}
			qty = next;
			$input.val(qty);
			$('.cart-page').addClass('is-updating');

			$.ajax({
				url: $('.cart-page').data('update-url'),
				type: 'post',
				data: { key: $input.data('key'), quantity: qty },
				dataType: 'json',
				success: function () {
					location.reload();
				}
			});
		});
	});
})(jQuery);
