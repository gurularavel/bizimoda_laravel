/* ==========================================================================
   bizimOda — telefon maskası: +994XXXXXXXXX (yalnız rəqəm, 9 rəqəm prefiksdən sonra)
   Bütün input[type=tel] və name="phone" sahələrinə tətbiq olunur (iframe popup-lar daxil).
   ========================================================================== */
(function () {
	var PREFIX = '+994';
	var CODE = '994';
	var LENGTH = 9;
	var SELECTOR = 'input[type="tel"], input[name="phone"]';

	// İstənilən daxiletməni (yapışdırma, 0XX..., +994 XX ... və s.) yerli 9 rəqəmə çevir
	function localDigits(value) {
		var digits = String(value || '').replace(/\D/g, '');
		if (digits.indexOf(CODE) === 0) {
			digits = digits.slice(CODE.length);
		} else if (digits.length < CODE.length && CODE.indexOf(digits) === 0) {
			// İstifadəçi prefiksin bir hissəsini silib — prefiks bərpa olunur
			digits = '';
		}
		digits = digits.replace(/^0+/, '');
		return digits.slice(0, LENGTH);
	}

	function format(value) {
		return PREFIX + localDigits(value);
	}

	function prepare(input) {
		if (input.__bzPhone) {
			return;
		}
		input.__bzPhone = true;
		input.setAttribute('inputmode', 'tel');
		input.setAttribute('maxlength', String(PREFIX.length + LENGTH));
		input.setAttribute('pattern', '\\+994[0-9]{' + LENGTH + '}');
		input.setAttribute('placeholder', '+994XXXXXXXXX');
		if (input.value) {
			input.value = format(input.value);
		}
	}

	function prepareAll(root) {
		(root || document).querySelectorAll(SELECTOR).forEach(prepare);
	}

	document.addEventListener('focusin', function (e) {
		var input = e.target;
		if (!input.matches || !input.matches(SELECTOR)) {
			return;
		}
		prepare(input);
		if (!input.value) {
			input.value = PREFIX;
		}
		setTimeout(function () {
			if (input.selectionStart < PREFIX.length) {
				input.setSelectionRange(input.value.length, input.value.length);
			}
		}, 0);
	});

	// Hərf və digər simvolların yazılmasının qarşısı
	document.addEventListener('keydown', function (e) {
		var input = e.target;
		if (!input.matches || !input.matches(SELECTOR) || e.ctrlKey || e.metaKey || e.altKey || e.key.length !== 1) {
			return;
		}
		if (!/[0-9]/.test(e.key)) {
			e.preventDefault();
		}
	});

	document.addEventListener('input', function (e) {
		var input = e.target;
		if (!input.matches || !input.matches(SELECTOR)) {
			return;
		}
		prepare(input);
		// Kursorun yerini saxla: kursordan əvvəlki yerli rəqəmlərin sayına görə
		var caret = input.selectionStart;
		var before = localDigits(input.value.slice(0, caret)).length;
		input.value = format(input.value);
		var pos = PREFIX.length + before;
		try { input.setSelectionRange(pos, pos); } catch (err) { /* bəzi brauzerlər */ }
	});

	document.addEventListener('focusout', function (e) {
		var input = e.target;
		if (input.matches && input.matches(SELECTOR) && input.value === PREFIX) {
			input.value = '';
		}
	});

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { prepareAll(); });
	} else {
		prepareAll();
	}
})();
