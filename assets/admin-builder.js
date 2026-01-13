(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	ready(function () {
		var wrap = document.querySelector('.hmpro-builder-wrap');
		if (!wrap) return;

		var sectionBtns = wrap.querySelectorAll('.hmpro-builder-section-btn');
		var elementBtns = wrap.querySelectorAll('.hmpro-builder-element-btn');
		var addFirst = wrap.querySelector('.hmpro-builder-add-first');

		function toast(msg) {
			// Lightweight placeholder feedback.
			if (window.console && console.log) console.log('[HMPro Builder]', msg);
		}

		sectionBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				sectionBtns.forEach(function (b) { b.classList.remove('button-primary'); });
				btn.classList.add('button-primary');
				toast('Section selected: ' + btn.getAttribute('data-section'));
			});
		});

		elementBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				toast('Element clicked: ' + btn.getAttribute('data-type') + ' (saving added in Commit 017)');
			});
		});

		if (addFirst) {
			addFirst.addEventListener('click', function () {
				toast('Add component clicked (placeholder).');
			});
		}
	});
})();
