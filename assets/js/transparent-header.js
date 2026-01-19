(function () {
	'use strict';

	var cfg = window.HMPRO_TRANSPARENT_HEADER || {};
	var enableSolid = !!cfg.enableSolidOnScroll;
	var threshold = parseInt(cfg.threshold, 10);
	if (isNaN(threshold)) threshold = 60;

	var body = document.body;
	if (!body || !body.classList.contains('hmpro-transparent-header-home')) return;

	if (!enableSolid) return;

	var ticking = false;
	function onScroll() {
		if (ticking) return;
		ticking = true;
		window.requestAnimationFrame(function () {
			var y = window.scrollY || window.pageYOffset || 0;
			if (y > threshold) body.classList.add('hmpro-transparent-header-scrolled');
			else body.classList.remove('hmpro-transparent-header-scrolled');
			ticking = false;
		});
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();
})();
