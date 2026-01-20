/* global wp, jQuery, HMPROCustomizerReset */
(function ($) {
	'use strict';

	function doReset(nonce) {
		return $.post(HMPROCustomizerReset.ajaxUrl, {
			action: HMPROCustomizerReset.action,
			nonce: nonce
		});
	}

	$(document).on('click', '#hmpro-reset-hf-colors', function (e) {
		e.preventDefault();

		if (!window.confirm('Reset Top Bar + Footer colors to defaults?')) {
			return;
		}

		var $btn = $(this);
		var nonce = $btn.data('nonce') || '';
		$btn.prop('disabled', true);

		doReset(nonce)
			.done(function () {
				// Clear Customizer settings in UI.
				if (wp && wp.customize) {
					['hmpro_topbar_bg_color', 'hmpro_topbar_text_color', 'hmpro_footer_bg_color', 'hmpro_footer_text_color'].forEach(function (key) {
						if (wp.customize.has(key)) {
							wp.customize(key).set('');
						}
					});
				}
				// Force a refresh so seeded preset defaults are applied immediately.
				if (wp && wp.customize && wp.customize.previewer) {
					wp.customize.previewer.refresh();
				}
			})
			.always(function () {
				$btn.prop('disabled', false);
			});
	});
})(jQuery);
