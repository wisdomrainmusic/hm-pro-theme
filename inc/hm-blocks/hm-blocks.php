<?php
/**
 * HM Pro Blocks (theme module)
 *
 * Location: /inc/hm-blocks/
 *
 * This module registers custom Gutenberg blocks used for landing pages.
 * It is intentionally theme-embedded ("closed package").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'HMPRO_VERSION' ) ) {
	// Fallback in case constants are not loaded yet.
	define( 'HMPRO_VERSION', '0.1.0' );
}

// Paths.
if ( ! defined( 'HMPRO_BLOCKS_PATH' ) ) {
	define( 'HMPRO_BLOCKS_PATH', trailingslashit( get_template_directory() ) . 'inc/hm-blocks' );
}
if ( ! defined( 'HMPRO_BLOCKS_URL' ) ) {
	define( 'HMPRO_BLOCKS_URL', trailingslashit( get_template_directory_uri() ) . 'inc/hm-blocks' );
}

// Shared renderer utilities.
require_once HMPRO_BLOCKS_PATH . '/render/shared/helpers.php';
require_once HMPRO_BLOCKS_PATH . '/render/shared/sanitize.php';
require_once HMPRO_BLOCKS_PATH . '/render/shared/templates.php';

/**
 * Register "HM Pro" block category in the inserter.
 */
add_filter( 'block_categories_all', function ( $categories ) {
	$slug = 'hmpro';
	foreach ( $categories as $cat ) {
		if ( isset( $cat['slug'] ) && $cat['slug'] === $slug ) {
			return $categories;
		}
	}

	$categories[] = [
		'slug'  => $slug,
		'title' => __( 'HM Pro Blocks', 'hm-pro-theme' ),
		'icon'  => null,
	];

	return $categories;
}, 20 );

/**
 * Register shared assets for HM Pro blocks.
 */
function hmpro_blocks_register_shared_assets() {
	wp_register_style(
		'hmpro-blocks',
		HMPRO_BLOCKS_URL . '/assets/css/blocks.css',
		[],
		file_exists( HMPRO_BLOCKS_PATH . '/assets/css/blocks.css' ) ? filemtime( HMPRO_BLOCKS_PATH . '/assets/css/blocks.css' ) : HMPRO_VERSION
	);

	wp_register_style(
		'hmpro-blocks-editor',
		HMPRO_BLOCKS_URL . '/assets/css/editor.css',
		[ 'wp-edit-blocks' ],
		file_exists( HMPRO_BLOCKS_PATH . '/assets/css/editor.css' ) ? filemtime( HMPRO_BLOCKS_PATH . '/assets/css/editor.css' ) : HMPRO_VERSION
	);

	wp_register_script(
		'hmpro-blocks-editor',
		HMPRO_BLOCKS_URL . '/assets/js/editor.js',
		[ 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ],
		file_exists( HMPRO_BLOCKS_PATH . '/assets/js/editor.js' ) ? filemtime( HMPRO_BLOCKS_PATH . '/assets/js/editor.js' ) : HMPRO_VERSION,
		true
	);
}
add_action( 'init', 'hmpro_blocks_register_shared_assets', 5 );

/**
 * Enqueue shared frontend styles for any HM Pro block.
 */
function hmpro_blocks_enqueue_block_assets() {
	wp_enqueue_style( 'hmpro-blocks' );
}
add_action( 'enqueue_block_assets', 'hmpro_blocks_enqueue_block_assets' );

/**
 * Enqueue editor-only assets.
 */
function hmpro_blocks_enqueue_editor_assets() {
	wp_enqueue_style( 'hmpro-blocks-editor' );
	wp_enqueue_script( 'hmpro-blocks-editor' );
}
add_action( 'enqueue_block_editor_assets', 'hmpro_blocks_enqueue_editor_assets' );

/**
 * Register blocks located under /inc/hm-blocks/blocks/*
 *
 * Each block folder is expected to contain a block.json.
 */
function hmpro_blocks_register_blocks() {
	$blocks_dir = trailingslashit( HMPRO_BLOCKS_PATH ) . 'blocks';
	if ( ! is_dir( $blocks_dir ) ) {
		return;
	}

	$entries = glob( $blocks_dir . '/*', GLOB_ONLYDIR );
	if ( empty( $entries ) ) {
		return;
	}

	foreach ( $entries as $dir ) {
		$block_json = trailingslashit( $dir ) . 'block.json';
		if ( file_exists( $block_json ) ) {
			register_block_type( $dir );
		}
	}
}
add_action( 'init', 'hmpro_blocks_register_blocks', 20 );
