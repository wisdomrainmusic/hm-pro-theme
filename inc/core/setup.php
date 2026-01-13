<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Header/Footer Builder Regions
 */
function hmpro_get_builder_regions() {
	return array(
		'header' => array(
			'header_top'    => __( 'Header Top', 'hm-pro-theme' ),
			'header_main'   => __( 'Header Main', 'hm-pro-theme' ),
			'header_bottom' => __( 'Header Bottom', 'hm-pro-theme' ),
		),
		'footer' => array(
			'footer_top'    => __( 'Footer Top', 'hm-pro-theme' ),
			'footer_main'   => __( 'Footer Main', 'hm-pro-theme' ),
			'footer_bottom' => __( 'Footer Bottom', 'hm-pro-theme' ),
		),
	);
}

/**
 * Check if builder layout exists (placeholder – real logic in Commit 017)
 */
function hmpro_has_builder_layout( $area ) {
	/**
	 * Filter: allow forcing builder on/off
	 */
	return apply_filters( 'hmpro/has_builder_layout', false, $area );
}

/**
 * Render a builder region
 * Renderer will be attached in Commit 018
 */
function hmpro_render_builder_region( $region_key, $area ) {
	do_action( 'hmpro/' . $area . '/render_region', $region_key );
}

add_action( 'after_setup_theme', function () {

	load_theme_textdomain( 'hmpro', HMPRO_PATH . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'woocommerce' );

	register_nav_menus( [
		'hm_primary' => __( 'Primary Menu', 'hmpro' ),
		'hm_footer'  => __( 'Footer Menu', 'hmpro' ),
	] );

} );
