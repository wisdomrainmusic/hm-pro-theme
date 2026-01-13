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

		var layoutField = document.getElementById('hmpro_builder_layout');

		// Minimal initial schema stays in hidden field.
		// Commit 019 will actually mutate this JSON based on UI actions.
		function getLayout() {
			try { return JSON.parse(layoutField.value || '{}'); } catch(e) { return {}; }
		}
		function setLayout(obj) {
			layoutField.value = JSON.stringify(obj || {});
		}

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
				toast('Element clicked: ' + btn.getAttribute('data-type') + ' (drag/drop in Commit 019)');
			});
		});

		if (addFirst) {
			addFirst.addEventListener('click', function () {
				toast('Add component clicked (placeholder).');
			});
		}

		// Ensure hidden layout field always has a valid base schema.
		var current = getLayout();
		if (!current || typeof current !== 'object' || !current.schema_version) {
			setLayout(current);
		}
	});
})();
