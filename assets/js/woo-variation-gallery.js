(function($){
	'use strict';

	function syncSingleGalleryHeights($gallery){
		if (!$gallery || !$gallery.length) return;
		var h = $gallery.outerHeight();
		if (!h || h < 50) return;

		// Hard cap to prevent "giant" galleries on wide layouts
		// Adjust if needed, but keep it under ~900px for a premium, stable look.
		var cap = 900;
		if (h > cap) h = cap;

		// FlexSlider viewport + wrapper must follow the canvas height
		var $vp = $gallery.find('.flex-viewport');
		if ($vp.length) {
			$vp.css('height', h + 'px');
		}
		var $wrap = $gallery.find('.woocommerce-product-gallery__wrapper');
		if ($wrap.length) {
			$wrap.css('height', h + 'px');
		}
	}

	function lockHeight($gallery){
		var h = $gallery.outerHeight();
		if (h && h > 0) {
			$gallery
				.css({ 'height': h + 'px', 'min-height': h + 'px' })
				.addClass('hmpro-vg-lock');
		}

		// ALSO lock wrapper height (Woo/Flex may mutate wrapper during init)
		var $wrap = $gallery.find('.woocommerce-product-gallery__wrapper');
		if ($wrap.length) {
			var wh = $wrap.outerHeight();
			if (wh && wh > 0) {
				$wrap.css({ 'height': wh + 'px', 'min-height': wh + 'px' });
			}
		}
	}
	function unlockHeight($gallery){
		$gallery.css({ 'height': '', 'min-height': '' }).removeClass('hmpro-vg-lock');
		var $wrap = $gallery.find('.woocommerce-product-gallery__wrapper');
		if ($wrap.length) {
			$wrap.css({ 'height': '', 'min-height': '' });
		}
		syncSingleGalleryHeights($gallery);
	}
	function waitImages($gallery, cb){
		var $imgs = $gallery.find('.woocommerce-product-gallery__wrapper img');
		if (!$imgs.length) { cb(); return; }
		var total = $imgs.length, done = 0, tick = function(){ done++; if (done >= total) cb(); };
		$imgs.each(function(){ if (this.complete) { tick(); } else { $(this).one('load error', tick); } });
	}

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

	function hardResetGallery($gallery){
		// 1) Attempt to destroy flexslider wherever it might be attached.
		try { if ($gallery.data('flexslider')) { $gallery.flexslider('destroy'); } } catch(e){}
		try { if ($gallery.find('.woocommerce-product-gallery__wrapper').data('flexslider')) { $gallery.find('.woocommerce-product-gallery__wrapper').flexslider('destroy'); } } catch(e){}
		try { $gallery.find('.flexslider').each(function(){ try { $(this).flexslider('destroy'); } catch(e){} }); } catch(e){}

		// 2) Remove leftover wrappers/nav created by flexslider (prevents duplicate slides & weird crop states).
		$gallery.find('.flex-control-nav, .flex-direction-nav').remove();
		$gallery.find('.flex-viewport').each(function(){
			var $vp = $(this);
			var $children = $vp.children().detach();
			$vp.replaceWith($children);
		});
		$gallery.removeData('flexslider');
		$gallery.find('.woocommerce-product-gallery__wrapper').removeData('flexslider');

		// Woo exposes jQuery plugin wc_product_gallery() on .woocommerce-product-gallery.
		try {
			if ($.fn.wc_product_gallery) {
				$gallery.wc_product_gallery();
			} else if (typeof $gallery.wc_product_gallery === 'function') {
				$gallery.wc_product_gallery();
			}
		} catch(e){}
	}

	$(function(){
		var $form = $('form.variations_form');
		if (!$form.length) return;

		var $gallery = $('.woocommerce-product-gallery');
		if (!$gallery.length) return;

		var $wrapper = $gallery.find('.woocommerce-product-gallery__wrapper');
		if (!$wrapper.length) return;

		syncSingleGalleryHeights($gallery);
		$(window).on('resize', function(){ syncSingleGalleryHeights($gallery); });

		// After Woo/Flex initializes, heights may change again
		setTimeout(function(){ syncSingleGalleryHeights($gallery); }, 250);

		/**
		 * Cache a PRISTINE default gallery:
		 * Only real slides (no flex clones/wrappers). This prevents zoom/crop weirdness on restore.
		 */
		var originalSlidesHtml = (function(){
			var html = '';
			$wrapper.children('.woocommerce-product-gallery__image').each(function(){
				html += $(this).prop('outerHTML');
			});
			// Fallback if theme structure differs.
			if (!html) {
				html = $wrapper.html();
			}
			return html;
		})();

		var customGalleryActive = false;

		function swapToVariation(variation){
			var hasCustomGallery = hasGallery(variation);

			// If this variation has no custom gallery -> leave Woo default behavior.
			// Only restore the parent gallery if we previously applied a custom gallery.
			if (!variation || !hasCustomGallery) {
				if (customGalleryActive) {
					lockHeight($gallery);
					$wrapper.html(originalSlidesHtml);
					hardResetGallery($gallery);
					waitImages($gallery, function(){ unlockHeight($gallery); });
					customGalleryActive = false;
				}
				return;
			}

			lockHeight($gallery);

			var html = '';
			variation.hmpro_gallery.forEach(function(img){
				if (img && img.src && img.full) {
					html += buildSlideHtml(img);
				}
			});

			if (!html) {
				if (customGalleryActive) {
					$wrapper.html(originalSlidesHtml);
					hardResetGallery($gallery);
					waitImages($gallery, function(){ unlockHeight($gallery); });
					customGalleryActive = false;
				}
				return;
			}

			$wrapper.html(html);
			hardResetGallery($gallery);
			waitImages($gallery, function(){ unlockHeight($gallery); });
			customGalleryActive = true;
		}

		/**
		 * IMPORTANT:
		 * Use show_variation and defer execution
		 * so Woo does NOT override our gallery after swap.
		 */
		$form.on('show_variation', function(e, variation){
			window.requestAnimationFrame(function(){
				setTimeout(function(){
					swapToVariation(variation);
				}, 0);
			});
		});

		$form.on('reset_data', function(){
			window.requestAnimationFrame(function(){
				setTimeout(function(){
					$wrapper.html(originalSlidesHtml);
					hardResetGallery($gallery);
					customGalleryActive = false;
				}, 0);
			});
		});

	});

})(jQuery);
