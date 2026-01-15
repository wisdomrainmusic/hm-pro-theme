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

	wp_enqueue_style(
		'hmpro-header',
		HMPRO_URL . '/assets/css/header.css',
		[ 'hmpro-base' ],
		HMPRO_VERSION
	);

	wp_enqueue_style(
		'hmpro-footer',
		HMPRO_URL . '/assets/css/footer.css',
		[ 'hmpro-base' ],
		HMPRO_VERSION
	);

	wp_enqueue_style(
		'hmpro-mega-menu',
		HMPRO_URL . '/assets/css/mega-menu.css',
		[ 'hmpro-base' ],
		HMPRO_VERSION
	);

	wp_enqueue_script(
		'hmpro-mega-menu',
		get_template_directory_uri() . '/assets/js/mega-menu.js',
		array(),
		HMPRO_VERSION,
		true
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

add_action( 'customize_register', function ( $wp_customize ) {
	$section_id = 'hmpro_header_settings';

	if ( ! $wp_customize->get_section( $section_id ) ) {
		$wp_customize->add_section( $section_id, [
			'title'    => __( 'Header & Navigation', 'hm-pro-theme' ),
			'priority' => 30,
		] );
	}

	$wp_customize->add_setting( 'hmpro_mega_menu_interaction', [
		'default'           => 'hover',
		'sanitize_callback' => function ( $value ) {
			return in_array( $value, [ 'hover', 'click' ], true ) ? $value : 'hover';
		},
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'hmpro_mega_menu_interaction', [
		'label'   => __( 'Mega Menu Interaction Mode', 'hm-pro-theme' ),
		'section' => $section_id,
		'type'    => 'radio',
		'choices' => [
			'hover' => __( 'Hover (Default)', 'hm-pro-theme' ),
			'click' => __( 'Click to Open', 'hm-pro-theme' ),
		],
	] );
} );

add_filter( 'body_class', function ( $classes ) {
	$mode = get_theme_mod( 'hmpro_mega_menu_interaction', 'hover' );

	if ( 'click' === $mode ) {
		$classes[] = 'hmpro-mega-click';
	}

	return $classes;
} );
