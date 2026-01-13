<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builder Storage (Commit 017)
 * Options:
 * - hmpro_header_layout
 * - hmpro_footer_layout
 *
 * Schema:
 * {
 *   schema_version: 1,
 *   regions: {
 *     header_top: [rows], header_main: [rows], header_bottom: [rows]
 *   }
 * }
 *
 * rows: [{ id, columns: [{ id, width, components: [{ id, type, settings }] }] }]
 */

function hmpro_builder_option_key( $area ) {
	$area = ( 'footer' === $area ) ? 'footer' : 'header';
	return ( 'footer' === $area ) ? 'hmpro_footer_layout' : 'hmpro_header_layout';
}

function hmpro_builder_default_schema( $area ) {
	$area = ( 'footer' === $area ) ? 'footer' : 'header';

	$regions = ( 'footer' === $area )
		? array( 'footer_top', 'footer_main', 'footer_bottom' )
		: array( 'header_top', 'header_main', 'header_bottom' );

	$out = array(
		'schema_version' => 1,
		'regions'        => array(),
	);

	foreach ( $regions as $key ) {
		$out['regions'][ $key ] = array();
	}

	return $out;
}

function hmpro_builder_get_layout( $area ) {
	$key = hmpro_builder_option_key( $area );
	$raw = get_option( $key, null );

	if ( empty( $raw ) || ! is_array( $raw ) ) {
		return hmpro_builder_default_schema( $area );
	}

	// Merge with defaults to ensure all regions exist.
	$default = hmpro_builder_default_schema( $area );
	$raw['schema_version'] = isset( $raw['schema_version'] ) ? absint( $raw['schema_version'] ) : 1;
	$raw['regions']        = isset( $raw['regions'] ) && is_array( $raw['regions'] ) ? $raw['regions'] : array();

	$raw['regions'] = array_merge( $default['regions'], $raw['regions'] );

	return $raw;
}

function hmpro_builder_update_layout( $area, array $layout ) {
	$key = hmpro_builder_option_key( $area );
	return update_option( $key, $layout, false );
}

/**
 * Sanitize layout payload coming from admin.
 */
function hmpro_builder_sanitize_layout( $area, $payload ) {
	$area = ( 'footer' === $area ) ? 'footer' : 'header';

	$allowed_types = array( 'logo', 'menu', 'search', 'social', 'cart', 'button', 'html', 'spacer' );

	// Settings allowlist per component type (can expand in Commit 019).
	$allowed_settings = array(
		'logo'   => array( 'alignment', 'visibility', 'spacing' ),
		'menu'   => array( 'alignment', 'visibility', 'spacing', 'location', 'menu_id', 'depth', 'source' ),
		'search' => array( 'alignment', 'visibility', 'spacing', 'placeholder' ),
		// Social stores URLs in a nested array: settings.urls[network] = url.
		// Keep allowlist tight but compatible with admin UI + renderer.
		'social' => array(
			'alignment',
			'visibility',
			'spacing',
			'size',
			'gap',
			'new_tab',
			'urls',
		),
		'cart'   => array( 'alignment', 'visibility', 'spacing' ),
		'button' => array( 'alignment', 'visibility', 'spacing', 'text', 'url', 'rel', 'target' ),
		'html'   => array( 'visibility', 'spacing', 'content' ),
		'spacer' => array( 'visibility', 'spacing', 'width', 'height' ),
	);

	$out = hmpro_builder_default_schema( $area );
	$out['schema_version'] = 1;

	if ( ! is_array( $payload ) ) {
		return $out;
	}

	$regions = isset( $payload['regions'] ) && is_array( $payload['regions'] ) ? $payload['regions'] : array();

	foreach ( $out['regions'] as $region_key => $_rows_default ) {
		$rows = isset( $regions[ $region_key ] ) && is_array( $regions[ $region_key ] ) ? $regions[ $region_key ] : array();
		$clean_rows = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$row_id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';
			if ( '' === $row_id ) {
				continue;
			}

			$columns = isset( $row['columns'] ) && is_array( $row['columns'] ) ? $row['columns'] : array();
			$clean_cols = array();

			foreach ( $columns as $col ) {
				if ( ! is_array( $col ) ) {
					continue;
				}

				$col_id = isset( $col['id'] ) ? sanitize_key( $col['id'] ) : '';
				if ( '' === $col_id ) {
					continue;
				}

				$width = isset( $col['width'] ) ? absint( $col['width'] ) : 12;
				if ( $width < 1 || $width > 12 ) {
					$width = 12;
				}

				$components = isset( $col['components'] ) && is_array( $col['components'] ) ? $col['components'] : array();
				$clean_components = array();

				foreach ( $components as $comp ) {
					if ( ! is_array( $comp ) ) {
						continue;
					}

					$comp_id = isset( $comp['id'] ) ? sanitize_key( $comp['id'] ) : '';
					$type    = isset( $comp['type'] ) ? sanitize_key( $comp['type'] ) : '';

					if ( '' === $comp_id || '' === $type || ! in_array( $type, $allowed_types, true ) ) {
						continue;
					}

					$settings = isset( $comp['settings'] ) && is_array( $comp['settings'] ) ? $comp['settings'] : array();
					$clean_settings = array();

					$type_allowed = isset( $allowed_settings[ $type ] ) ? $allowed_settings[ $type ] : array();
					foreach ( $type_allowed as $k ) {
						if ( ! array_key_exists( $k, $settings ) ) {
							continue;
						}

						$v = $settings[ $k ];

						// Basic sanitization by key.
						if ( in_array( $k, array( 'text', 'placeholder', 'alignment', 'visibility', 'spacing', 'rel', 'target', 'source', 'location', 'size', 'gap' ), true ) ) {
							$clean_settings[ $k ] = is_scalar( $v ) ? sanitize_text_field( (string) $v ) : '';
						} elseif ( 'new_tab' === $k ) {
							$clean_settings[ $k ] = ! empty( $v ) ? 1 : 0;
						} elseif ( 'url' === $k ) {
							$clean_settings[ $k ] = is_scalar( $v ) ? esc_url_raw( (string) $v ) : '';
						} elseif ( 'urls' === $k ) {
							$clean_urls = array();
							if ( is_array( $v ) ) {
								$allowed_social = array( 'facebook', 'instagram', 'x', 'youtube', 'tiktok', 'linkedin', 'whatsapp', 'telegram' );
								foreach ( $allowed_social as $nk ) {
									if ( empty( $v[ $nk ] ) ) {
										continue;
									}
									$raw_u = trim( (string) $v[ $nk ] );
									if ( '' !== $raw_u && ! preg_match( '~^https?://~i', $raw_u ) ) {
										$raw_u = 'https://' . ltrim( $raw_u, '/ ' );
									}
									$u = esc_url_raw( $raw_u );
									if ( '' === $u ) {
										$u = sanitize_text_field( $raw_u );
									}
									if ( '' !== $u ) {
										$clean_urls[ $nk ] = $u;
									}
								}
							}
							$clean_settings[ $k ] = $clean_urls;
						} elseif ( 'menu_id' === $k || 'depth' === $k || 'width' === $k || 'height' === $k ) {
							$clean_settings[ $k ] = absint( $v );
						} elseif ( 'content' === $k ) {
							// Stored raw; rendered with wp_kses_post in Commit 018.
							$clean_settings[ $k ] = is_scalar( $v ) ? (string) $v : '';
						}
					}

					$clean_components[] = array(
						'id'       => $comp_id,
						'type'     => $type,
						'settings' => $clean_settings,
					);
				}

				$clean_cols[] = array(
					'id'         => $col_id,
					'width'      => $width,
					'components' => $clean_components,
				);
			}

			$clean_rows[] = array(
				'id'      => $row_id,
				'columns' => $clean_cols,
			);
		}

		$out['regions'][ $region_key ] = $clean_rows;
	}

	return $out;
}
