<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HMPRO_VERSION', '0.1.0' );
define( 'HMPRO_PATH', get_template_directory() );
define( 'HMPRO_URL', get_template_directory_uri() );

require_once HMPRO_PATH . '/inc/core/setup.php';
require_once HMPRO_PATH . '/inc/core/enqueue.php';
require_once HMPRO_PATH . '/inc/core/customizer.php';

require_once HMPRO_PATH . '/inc/engine/presets.php';
require_once HMPRO_PATH . '/inc/engine/css-engine.php';
require_once HMPRO_PATH . '/inc/engine/import-export.php';
require_once HMPRO_PATH . '/inc/engine/typography.php';
require_once HMPRO_PATH . '/inc/engine/builder-storage.php';
require_once HMPRO_PATH . '/inc/engine/builder-renderer.php';
require_once HMPRO_PATH . '/inc/engine/mega-menu-library.php';
require_once HMPRO_PATH . '/inc/engine/mega-menu-canvas.php';
require_once HMPRO_PATH . '/inc/engine/mega-menu-menuitem-meta.php';

require_once HMPRO_PATH . '/inc/tools/tools-loader.php';

require_once HMPRO_PATH . '/inc/admin/admin-menu.php';
require_once HMPRO_PATH . '/inc/admin/actions.php';
require_once HMPRO_PATH . '/inc/admin/mega-menu-ajax.php';
require_once HMPRO_PATH . '/inc/admin/presets-page.php';
require_once HMPRO_PATH . '/inc/admin/preset-edit.php';
require_once HMPRO_PATH . '/inc/admin/builder-pages.php';

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

	// Builder-specific assets (only on builder screens).
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( in_array( $page, [ 'hmpro-header-builder', 'hmpro-footer-builder', 'hmpro-mega-menu-builder' ], true ) ) {
		wp_enqueue_style(
			'hmpro-admin-builder',
			HMPRO_URL . '/assets/admin-builder.css',
			[ 'hmpro-admin' ],
			HMPRO_VERSION
		);

		wp_enqueue_script(
			'hmpro-admin-builder',
			HMPRO_URL . '/assets/admin-builder.js',
			[ 'hmpro-admin' ],
			HMPRO_VERSION,
			true
		);
	}
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
