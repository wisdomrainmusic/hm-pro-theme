(function($){
	'use strict';

	function hasGallery(variation){
		return variation && variation.hmpro_gallery && Array.isArray(variation.hmpro_gallery) && variation.hmpro_gallery.length > 0;
	}

	function buildSlideHtml(img){
		var alt = img.alt ? String(img.alt) : '';
		var srcset = img.srcset ? String(img.srcset) : '';
		var sizes = img.sizes ? String(img.sizes) : '';

		// Keep Woo gallery markup conventions.
		return '' +
		'<div class="woocommerce-product-gallery__image">' +
			'<a href="'+ img.full +'">' +
				'<img src="'+ img.src +'"' +
					(srcset ? ' srcset="'+srcset+'"' : '') +
					(sizes ? ' sizes="'+sizes+'"' : '') +
					' alt="'+ alt.replace(/"/g,'&quot;') +'" />' +
			'</a>' +
		'</div>';
	}

	function reinitGallery($gallery){
		// Destroy flexslider if present (Woo uses it under the hood).
		try {
			if ($gallery.data('flexslider')) {
				$gallery.flexslider('destroy');
			}
		} catch(e){}

		// Woo exposes jQuery plugin wc_product_gallery() on .woocommerce-product-gallery.
		try {
			if ($.fn.wc_product_gallery) {
				$gallery.wc_product_gallery();
			} else if (typeof $gallery.wc_product_gallery === 'function') {
				$gallery.wc_product_gallery();
			}
		} catch(e){}

		// Ensure arrows still show (theme already forces via PHP + inline JS).
	}

	$(function(){
		var $form = $('form.variations_form');
		if (!$form.length) return;

		var $gallery = $('.woocommerce-product-gallery');
		if (!$gallery.length) return;

		var $wrapper = $gallery.find('.woocommerce-product-gallery__wrapper');
		if (!$wrapper.length) return;

		// Cache original gallery to restore on reset / when no variation gallery exists.
		var originalHtml = $wrapper.html();

		function swapToVariation(variation){
			if (!hasGallery(variation)) {
				$wrapper.html(originalHtml);
				reinitGallery($gallery);
				return;
			}

			var html = '';
			variation.hmpro_gallery.forEach(function(img){
				if (img && img.src && img.full) {
					html += buildSlideHtml(img);
				}
			});

			if (!html) {
				$wrapper.html(originalHtml);
				reinitGallery($gallery);
				return;
			}

			$wrapper.html(html);
			reinitGallery($gallery);
		}

		$form.on('found_variation', function(e, variation){
			swapToVariation(variation);
		});

		$form.on('reset_data', function(){
			$wrapper.html(originalHtml);
			reinitGallery($gallery);
		});

	});

})(jQuery);
