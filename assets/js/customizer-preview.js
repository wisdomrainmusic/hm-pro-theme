/* global wp */
(function () {
	if (typeof wp === 'undefined' || !wp.customize) return;

	function getHeroes() {
		return document.querySelectorAll('.hmpro-th-hero');
	}

	function getHeaderBanners() {
		return document.querySelectorAll('.hmpro-hb-banner');
	}

	function setVar(varName, value) {
		getHeroes().forEach(function (el) {
			el.style.setProperty(varName, value);
		});
	}

	function setHBVar(varName, value) {
		getHeaderBanners().forEach(function (el) {
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

	function bindHBPx(settingId, cssVar) {
		wp.customize(settingId, function (setting) {
			setting.bind(function (val) {
				var n = parseInt(val, 10);
				if (isNaN(n)) n = 0;
				setHBVar(cssVar, n + 'px');
			});
		});
	}

	// Offsets for the hero content group.
	bindPx('hmpro_th_hero_group_x', '--hmpro-hero-group-x');
	bindPx('hmpro_th_hero_group_y', '--hmpro-hero-group-y');

	// Header banner offsets (same behavior as hero).
	bindHBPx('hmpro_hb_group_x', '--hmpro-hb-group-x');
	bindHBPx('hmpro_hb_group_y', '--hmpro-hb-group-y');
})();
