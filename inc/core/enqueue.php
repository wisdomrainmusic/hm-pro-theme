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

	// Frontend Mega Menu helpers (position + "Show more" behaviour).
	wp_enqueue_script(
		'hmpro-mega-menu',
		get_template_directory_uri() . '/assets/js/mega-menu.js',
		[],
		HMPRO_VERSION,
		true
	);
} );
