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

} );

/**
 * Transparent Header (Homepage only).
 * - Adds body classes
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
