/* global window, document */
(function () {
	'use strict';

	function setMegaTopVar() {
		var header = document.getElementById('site-header');
		if (!header) {
			document.documentElement.style.setProperty('--hmpro-mega-top', '80px');
			return;
		}
		var rect = header.getBoundingClientRect();
		var top = Math.max(0, Math.round(rect.bottom));
		document.documentElement.style.setProperty('--hmpro-mega-top', top + 'px');
	}

	function initShowMore() {
		var menus = document.querySelectorAll('.hmpro-mega-panel .hmpro-mega-column-menu');
		if (!menus.length) return;

		menus.forEach(function (menu) {
			// We only collapse the first visible list under each "column menu"
			var ul = menu.querySelector(':scope > ul.hmpro-mega-col-list.hmpro-depth-1');
			if (!ul) return;

			var items = ul.querySelectorAll(':scope > li.hmpro-mega-col-item');
			if (!items || items.length <= 5) return;

			menu.classList.add('hmpro-has-more');
			menu.classList.add('hmpro-collapsed');

			// Avoid duplicating controls if re-initialized
			if (menu.querySelector(':scope > .hmpro-mega-more')) return;

			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'hmpro-mega-more';
			btn.setAttribute('aria-expanded', 'false');
			btn.textContent = 'Daha Fazla Gör';

			btn.addEventListener('click', function () {
				var expanded = menu.classList.toggle('hmpro-expanded');
				menu.classList.toggle('hmpro-collapsed', !expanded);
				btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
				btn.textContent = expanded ? 'Daha Az Göster' : 'Daha Fazla Gör';
			});

			menu.appendChild(btn);
		});
	}

	function init() {
		setMegaTopVar();
		initShowMore();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.addEventListener('resize', setMegaTopVar);
	window.addEventListener('scroll', setMegaTopVar, { passive: true });
})();

