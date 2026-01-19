<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {

	wp_enqueue_style(
		'hmpro-style',
		get_stylesheet_uri(),
		[],
		HMPRO_VERSION
	);

	if ( ! is_front_page() ) {
		return;
	}

	if ( ! get_theme_mod( 'hmpro_transparent_header_home', 0 ) ) {
		return;
	}

	// CSS is already in header.css; JS only when enabled.
	wp_enqueue_script(
		'hmpro-transparent-header',
		get_template_directory_uri() . '/assets/js/transparent-header.js',
		[],
		'1.0.0',
		true
	);

	$data = [
		'enableSolidOnScroll' => (int) get_theme_mod( 'hmpro_transparent_header_scroll_solid', 1 ),
		'threshold'           => (int) get_theme_mod( 'hmpro_transparent_header_threshold', 60 ),
	];
	wp_add_inline_script( 'hmpro-transparent-header', 'window.HMPRO_TRANSPARENT_HEADER=' . wp_json_encode( $data ) . ';', 'before' );
} );

/**
 * Transparent Header (Homepage only).
 * - Adds body classes
 * - Enqueues small JS when enabled
 */
add_filter( 'body_class', function ( $classes ) {
	if ( ! is_front_page() ) {
		return $classes;
	}
	if ( ! get_theme_mod( 'hmpro_transparent_header_home', 0 ) ) {
		return $classes;
	}

	$classes[] = 'hmpro-transparent-header-home';

	if ( get_theme_mod( 'hmpro_transparent_header_offset', 1 ) ) {
		$classes[] = 'hmpro-transparent-header-offset';
	}

	return $classes;
}, 20 );
