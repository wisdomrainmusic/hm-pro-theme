<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail early if WooCommerce isn't active.
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Reduce the default hover zoom strength.
 * Woo uses jquery.zoom; magnify > 1 increases the perceived zoom.
 */
add_filter( 'woocommerce_single_product_zoom_options', function ( $options ) {
	if ( ! is_array( $options ) ) {
		$options = [];
	}

	// Keep zoom subtle (default can feel aggressive on large portrait images).
	$options['magnify']  = isset( $options['magnify'] ) ? min( 1.25, (float) $options['magnify'] ) : 1.15;
	$options['duration'] = 120;
	$options['touch']    = false;
	$options['on']       = 'mouseover';

	return $options;
} );

/**
 * Ensure product gallery slider shows navigation arrows.
 * WooCommerce uses FlexSlider and respects these carousel options.
 */
add_filter( 'woocommerce_single_product_carousel_options', function ( $options ) {
	if ( ! is_array( $options ) ) {
		$options = [];
	}

	$options['directionNav'] = true;
	$options['controlNav']   = 'thumbnails';
	$options['smoothHeight'] = false;

	return $options;
} );
