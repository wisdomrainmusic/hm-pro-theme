/* global window, document */
(function () {
	'use strict';
	/* Click-to-toggle mega menu (no hover close) */

	function initClickToggle() {
		var nav = document.querySelector('.hmpro-primary-nav');
		if (!nav) return;

		function findDirectAnchor(li) {
			if (!li) return null;
			var kids = li.children;
			for (var i = 0; i < kids.length; i++) {
				if (kids[i] && kids[i].tagName && kids[i].tagName.toLowerCase() === 'a') {
					return kids[i];
				}
			}
			return li.querySelector('a');
		}

		function closeAll(exceptLi) {
			var openLis = nav.querySelectorAll('li.hmpro-li-has-mega.hmpro-mega-open');
			openLis.forEach(function (li) {
				if (exceptLi && li === exceptLi) return;
				li.classList.remove('hmpro-mega-open');
				var a = findDirectAnchor(li);
				if (a) a.setAttribute('aria-expanded', 'false');
			});
		}

		function toggle(li) {
			if (!li) return;
			var isOpen = li.classList.contains('hmpro-mega-open');
			closeAll(li);
			if (isOpen) {
				li.classList.remove('hmpro-mega-open');
				var aClose = findDirectAnchor(li);
				if (aClose) aClose.setAttribute('aria-expanded', 'false');
				return;
			}
			li.classList.add('hmpro-mega-open');
			var aOpen = findDirectAnchor(li);
			if (aOpen) aOpen.setAttribute('aria-expanded', 'true');
			setMegaTopVar();
		}

		nav.addEventListener('click', function (e) {
			var link = e.target && e.target.closest ? e.target.closest('li.hmpro-li-has-mega > a') : null;
			if (!link) return;
			var li = link.parentElement;
			if (!li || !li.classList || !li.classList.contains('hmpro-li-has-mega')) return;
			e.preventDefault();
			e.stopPropagation();
			toggle(li);
		});

		// Close when clicking outside the open mega panels / triggers
		document.addEventListener('click', function (e) {
			if (!nav.contains(e.target)) {
				closeAll(null);
				return;
			}
			// If click is inside the mega panel content, keep open.
			var insidePanel = e.target && e.target.closest ? e.target.closest('.hmpro-mega-panel') : null;
			if (insidePanel) return;
			// If clicked another mega trigger, let the nav handler toggle that one.
			var clickedMegaTrigger = e.target && e.target.closest ? e.target.closest('li.hmpro-li-has-mega > a') : null;
			if (clickedMegaTrigger) return;
			// Any other click inside the nav closes.
			closeAll(null);
		});

		document.addEventListener('keydown', function (e) {
			if (!e) return;
			var key = e.key || e.keyCode;
			if (key === 'Escape' || key === 'Esc' || key === 27) {
				closeAll(null);
			}
		});
	}

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
			// (avoid :scope for maximum compatibility)
			var ul = null;
			var directUls = menu.getElementsByTagName('ul');
			for (var i = 0; i < directUls.length; i++) {
				var candidate = directUls[i];
				if (candidate.parentElement === menu && candidate.classList.contains('hmpro-mega-col-list') && candidate.classList.contains('hmpro-depth-1')) {
					ul = candidate;
					break;
				}
			}
			if (!ul) return;

			var items = ul.children;
			var count = 0;
			for (var j = 0; j < items.length; j++) {
				if (items[j].classList && items[j].classList.contains('hmpro-mega-col-item')) count++;
			}
			if (!count || count <= 5) return;

			menu.classList.add('hmpro-has-more');
			menu.classList.add('hmpro-collapsed');

			// Avoid duplicating controls if re-initialized
			if (menu.querySelector('.hmpro-mega-more')) return;

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
		initClickToggle();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.addEventListener('resize', setMegaTopVar);
	window.addEventListener('scroll', setMegaTopVar, { passive: true });
})();
