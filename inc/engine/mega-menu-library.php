<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mega Menu Library (Commit 3A)
 * - CPT: hm_mega_menu
 * - Layout JSON stored in post meta: _hmpro_mega_layout
 * - Settings stored in post meta: _hmpro_mega_settings
 * - Shortcode: [hm_mega_menu id="123"]
 */

add_action( 'init', function () {
	$labels = [
		'name'               => __( 'Mega Menus', 'hmpro' ),
		'singular_name'      => __( 'Mega Menu', 'hmpro' ),
		'add_new'            => __( 'Add Mega Menu', 'hmpro' ),
		'add_new_item'       => __( 'Add New Mega Menu', 'hmpro' ),
		'edit_item'          => __( 'Edit Mega Menu', 'hmpro' ),
		'new_item'           => __( 'New Mega Menu', 'hmpro' ),
		'view_item'          => __( 'View Mega Menu', 'hmpro' ),
		'search_items'       => __( 'Search Mega Menus', 'hmpro' ),
		'not_found'          => __( 'No mega menus found.', 'hmpro' ),
		'not_found_in_trash' => __( 'No mega menus found in Trash.', 'hmpro' ),
	];

	register_post_type( 'hm_mega_menu', [
		'labels'              => $labels,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false, // we surface via HM Pro Theme menu
		'supports'            => [ 'title' ],
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'menu_position'       => 60,
		'menu_icon'           => 'dashicons-menu',
		'exclude_from_search' => true,
	] );
}, 10 );

function hmpro_mega_default_layout_schema() {
	return [
		'schema_version' => 1,
		'regions'        => [
			'mega_content' => [],
		],
	];
}

function hmpro_mega_default_settings() {
	return [
		'height_mode' => 'auto', // auto|compact|showcase
	];
}

function hmpro_mega_menu_get_layout( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return hmpro_mega_default_layout_schema();
	}

	$raw = get_post_meta( $post_id, '_hmpro_mega_layout', true );
	if ( empty( $raw ) || ! is_array( $raw ) ) {
		return hmpro_mega_default_layout_schema();
	}

	$raw['schema_version'] = isset( $raw['schema_version'] ) ? absint( $raw['schema_version'] ) : 1;
	$raw['regions']        = isset( $raw['regions'] ) && is_array( $raw['regions'] ) ? $raw['regions'] : [];

	$default       = hmpro_mega_default_layout_schema();
	$raw['regions'] = array_merge( $default['regions'], $raw['regions'] );

	return $raw;
}

function hmpro_mega_menu_update_layout( $post_id, array $layout ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return false;
	}
	if ( empty( $layout['regions'] ) || ! is_array( $layout['regions'] ) ) {
		return false;
	}
	return update_post_meta( $post_id, '_hmpro_mega_layout', $layout );
}

function hmpro_mega_menu_get_settings( $post_id ) {
	$post_id = absint( $post_id );
	$raw     = ( $post_id > 0 ) ? get_post_meta( $post_id, '_hmpro_mega_settings', true ) : [];
	if ( empty( $raw ) || ! is_array( $raw ) ) {
		$raw = [];
	}
	$default = hmpro_mega_default_settings();
	$out     = array_merge( $default, $raw );

	$mode = isset( $out['height_mode'] ) ? sanitize_key( (string) $out['height_mode'] ) : 'auto';
	if ( ! in_array( $mode, [ 'auto', 'compact', 'showcase' ], true ) ) {
		$mode = 'auto';
	}
	$out['height_mode'] = $mode;
	return $out;
}

function hmpro_mega_menu_update_settings( $post_id, array $settings ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return false;
	}
	$mode = isset( $settings['height_mode'] ) ? sanitize_key( (string) $settings['height_mode'] ) : 'auto';
	if ( ! in_array( $mode, [ 'auto', 'compact', 'showcase' ], true ) ) {
		$mode = 'auto';
	}
	$clean = [ 'height_mode' => $mode ];
	return update_post_meta( $post_id, '_hmpro_mega_settings', $clean );
}

function hmpro_mega_sanitize_layout( $payload ) {
	// For 3A: minimal, safe base. 3B/3C will expand types/settings allowlist.
	$out = hmpro_mega_default_layout_schema();
	$out['schema_version'] = 1;

	if ( ! is_array( $payload ) ) {
		return $out;
	}

	$regions = isset( $payload['regions'] ) && is_array( $payload['regions'] ) ? $payload['regions'] : [];
	$rows    = isset( $regions['mega_content'] ) && is_array( $regions['mega_content'] ) ? $regions['mega_content'] : [];

	$clean_rows = [];
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$row_id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';
		if ( '' === $row_id ) {
			continue;
		}

		$columns    = isset( $row['columns'] ) && is_array( $row['columns'] ) ? $row['columns'] : [];
		$clean_cols = [];
		foreach ( $columns as $col ) {
			if ( ! is_array( $col ) ) {
				continue;
			}
			$col_id = isset( $col['id'] ) ? sanitize_key( $col['id'] ) : '';
			if ( '' === $col_id ) {
				continue;
			}
			$width = isset( $col['width'] ) ? absint( $col['width'] ) : 3;
			if ( $width < 1 || $width > 12 ) {
				$width = 3;
			}

			$components       = isset( $col['components'] ) && is_array( $col['components'] ) ? $col['components'] : [];
			$clean_components = [];
			foreach ( $components as $comp ) {
				if ( ! is_array( $comp ) ) {
					continue;
				}
				$comp_id = isset( $comp['id'] ) ? sanitize_key( $comp['id'] ) : '';
				$type    = isset( $comp['type'] ) ? sanitize_key( $comp['type'] ) : '';
				if ( '' === $comp_id || '' === $type ) {
					continue;
				}
				// allowed types will be expanded in 3C
				if ( ! in_array( $type, [ 'html', 'button', 'spacer' ], true ) ) {
					continue;
				}

				$settings       = isset( $comp['settings'] ) && is_array( $comp['settings'] ) ? $comp['settings'] : [];
				$clean_settings = [];

				if ( 'button' === $type ) {
					$clean_settings['text'] = isset( $settings['text'] ) ? sanitize_text_field( (string) $settings['text'] ) : '';
					$clean_settings['url']  = isset( $settings['url'] ) ? esc_url_raw( (string) $settings['url'] ) : '';
				} elseif ( 'html' === $type ) {
					$clean_settings['content'] = isset( $settings['content'] ) ? (string) $settings['content'] : '';
				} elseif ( 'spacer' === $type ) {
					$clean_settings['width']  = isset( $settings['width'] ) ? absint( $settings['width'] ) : 0;
					$clean_settings['height'] = isset( $settings['height'] ) ? absint( $settings['height'] ) : 0;
				}

				$clean_components[] = [
					'id'       => $comp_id,
					'type'     => $type,
					'settings' => $clean_settings,
				];
			}

			$clean_cols[] = [
				'id'         => $col_id,
				'width'      => $width,
				'components' => $clean_components,
			];
		}

		$clean_rows[] = [
			'id'      => $row_id,
			'columns' => $clean_cols,
		];
	}

	$out['regions']['mega_content'] = $clean_rows;
	return $out;
}

add_shortcode( 'hm_mega_menu', function ( $atts ) {
	$atts    = shortcode_atts( [ 'id' => 0 ], $atts, 'hm_mega_menu' );
	$post_id = absint( $atts['id'] );
	if ( $post_id < 1 ) {
		return '';
	}

	$layout   = hmpro_mega_menu_get_layout( $post_id );
	$settings = hmpro_mega_menu_get_settings( $post_id );

	if ( ! function_exists( 'hmpro_builder_render_layout_rows' ) ) {
		return '';
	}

	ob_start();
	echo '<div class="hmpro-mega-layout hmpro-mega-height-' . esc_attr( $settings['height_mode'] ) . '" data-mega-id="' . esc_attr( (string) $post_id ) . '">';
	hmpro_builder_render_layout_rows( $layout['regions']['mega_content'] ?? [], 'mega' );
	echo '</div>';
	return (string) ob_get_clean();
} );
