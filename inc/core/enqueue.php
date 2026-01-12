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

} );
