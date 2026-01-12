<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
