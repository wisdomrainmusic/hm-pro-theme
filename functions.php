<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HMPRO_VERSION', '0.1.0' );
define( 'HMPRO_PATH', get_template_directory() );
define( 'HMPRO_URL', get_template_directory_uri() );

require_once HMPRO_PATH . '/inc/core/setup.php';
require_once HMPRO_PATH . '/inc/core/enqueue.php';

require_once HMPRO_PATH . '/inc/engine/presets.php';
require_once HMPRO_PATH . '/inc/engine/css-engine.php';
require_once HMPRO_PATH . '/inc/engine/import-export.php';
require_once HMPRO_PATH . '/inc/engine/typography.php';

require_once HMPRO_PATH . '/inc/admin/admin-menu.php';
require_once HMPRO_PATH . '/inc/admin/actions.php';
require_once HMPRO_PATH . '/inc/admin/presets-page.php';
require_once HMPRO_PATH . '/inc/admin/preset-edit.php';

add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style(
		'hmpro-admin',
		HMPRO_URL . '/assets/admin.css',
		[],
		HMPRO_VERSION
	);

	wp_enqueue_script(
		'hmpro-admin',
		HMPRO_URL . '/assets/admin.js',
		[],
		HMPRO_VERSION,
		true
	);
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'hmpro-base',
		HMPRO_URL . '/assets/css/base.css',
		[],
		HMPRO_VERSION
	);

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style(
			'hmpro-woo',
			HMPRO_URL . '/assets/css/woocommerce.css',
			[ 'hmpro-base' ],
			HMPRO_VERSION
		);
	}
} );
