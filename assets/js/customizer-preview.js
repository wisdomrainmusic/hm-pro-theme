/* global wp */
(function () {
	if (typeof wp === 'undefined' || !wp.customize) return;

	function getHeroes() {
		return document.querySelectorAll('.hmpro-th-hero');
	}

	function setVar(varName, value) {
		getHeroes().forEach(function (el) {
			el.style.setProperty(varName, value);
		});
	}

	function bindPx(settingId, cssVar) {
		wp.customize(settingId, function (setting) {
			setting.bind(function (val) {
				var n = parseInt(val, 10);
				if (isNaN(n)) n = 0;
				setVar(cssVar, n + 'px');
			});
		});
	}

	// Offsets for the hero content group.
	bindPx('hmpro_th_hero_group_x', '--hmpro-hero-group-x');
	bindPx('hmpro_th_hero_group_y', '--hmpro-hero-group-y');
})();
