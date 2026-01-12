<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options keys
 */
function hmpro_presets_option_key() {
	return 'hmpro_presets';
}

function hmpro_active_preset_option_key() {
	return 'hmpro_active_preset';
}

/**
 * Default preset (used if storage is empty).
 */
function hmpro_get_default_preset() {
	return [
		'id'           => 'default',
		'name'         => 'Default',
		'primary'      => '#111111',
		'bg'           => '#ffffff',
		'surface'      => '#f7f7f7',
		'text'         => '#222222',
		'muted'        => '#777777',
		'link'         => '#111111',
		'border'       => '#e5e5e5',
		'body_font'    => 'system',
		'heading_font' => 'system',
		'created_at'   => gmdate( 'c' ),
		'updated_at'   => gmdate( 'c' ),
	];
}

/**
 * Read presets from wp_options.
 */
function hmpro_get_presets() {
	$presets = get_option( hmpro_presets_option_key(), [] );
	$changed = false;

	if ( ! is_array( $presets ) || empty( $presets ) ) {
		$presets = [ hmpro_get_default_preset() ];
		update_option( hmpro_presets_option_key(), $presets, false );
	}

	// Normalize: ensure each preset has a sanitized id (and persist if needed).
	$seen = [];
	foreach ( $presets as $i => $p ) {
		$name = isset( $p['name'] ) ? (string) $p['name'] : ( 'Preset ' . ( $i + 1 ) );

		$raw_id = isset( $p['id'] ) ? (string) $p['id'] : '';
		$id     = sanitize_key( $raw_id );

		if ( empty( $id ) ) {
			$id = sanitize_key( $name );
		}

		if ( empty( $id ) ) {
			$id = 'preset_' . $i;
		}

		// Ensure uniqueness.
		$base = $id;
		$n    = 2;
		while ( isset( $seen[ $id ] ) ) {
			$id = $base . '_' . $n;
			$n++;
		}
		$seen[ $id ] = true;

		if ( empty( $p['id'] ) || $p['id'] !== $id ) {
			$presets[ $i ]['id'] = $id;
			$changed = true;
		}

		if ( empty( $presets[ $i ]['name'] ) || $presets[ $i ]['name'] !== $name ) {
			$presets[ $i ]['name'] = $name;
			$changed = true;
		}
	}

	if ( $changed ) {
		update_option( hmpro_presets_option_key(), $presets, false );
	}

	return $presets;
}

/**
 * Find preset by id.
 */
function hmpro_get_preset_by_id( $preset_id ) {
	$preset_id = sanitize_key( (string) $preset_id );
	$presets   = hmpro_get_presets();

	foreach ( $presets as $preset ) {
		$pid = isset( $preset['id'] ) ? sanitize_key( (string) $preset['id'] ) : '';
		if ( $pid && $pid === $preset_id ) {
			return $preset;
		}
	}

	return null;
}

/**
 * Get active preset id (ensure it exists).
 */
function hmpro_get_active_preset_id() {
	$active = get_option( hmpro_active_preset_option_key(), 'default' );
	$active = sanitize_key( (string) $active );

	if ( hmpro_get_preset_by_id( $active ) ) {
		return $active;
	}

	// Fallback to default if active not found.
	update_option( hmpro_active_preset_option_key(), 'default', false );
	return 'default';
}

/**
 * Set active preset id (only if exists).
 */
function hmpro_set_active_preset_id( $preset_id ) {
	$preset_id = sanitize_key( (string) $preset_id );

	if ( ! hmpro_get_preset_by_id( $preset_id ) ) {
		return false;
	}

	update_option( hmpro_active_preset_option_key(), $preset_id, false );
	return true;
}

/**
 * Seed a few sample presets for testing.
 */
function hmpro_seed_sample_presets() {
	$presets = hmpro_get_presets();

	// If we already have more than 1 preset, don't spam.
	if ( count( $presets ) > 1 ) {
		return false;
	}

	$now = gmdate( 'c' );

	$samples = [
		[
			'id'           => 'midnight_pro',
			'name'         => 'Midnight Pro',
			'primary'      => '#0B1220',
			'bg'           => '#FFFFFF',
			'surface'      => '#F4F6FA',
			'text'         => '#101828',
			'muted'        => '#667085',
			'link'         => '#0B1220',
			'border'       => '#E4E7EC',
			'body_font'    => 'system',
			'heading_font' => 'system',
			'created_at'   => $now,
			'updated_at'   => $now,
		],
		[
			'id'           => 'rose_elegance',
			'name'         => 'Rose Elegance',
			'primary'      => '#D97C8A',
			'bg'           => '#FFF6F7',
			'surface'      => '#FFFFFF',
			'text'         => '#2B2B2B',
			'muted'        => '#7A6670',
			'link'         => '#D97C8A',
			'border'       => '#F2D7DB',
			'body_font'    => 'system',
			'heading_font' => 'system',
			'created_at'   => $now,
			'updated_at'   => $now,
		],
		[
			'id'           => 'sand_latte',
			'name'         => 'Sand Latte',
			'primary'      => '#8B6B4F',
			'bg'           => '#FBF5EF',
			'surface'      => '#FFFFFF',
			'text'         => '#2A241F',
			'muted'        => '#6B5E55',
			'link'         => '#8B6B4F',
			'border'       => '#E9DED6',
			'body_font'    => 'system',
			'heading_font' => 'system',
			'created_at'   => $now,
			'updated_at'   => $now,
		],
	];

	// Keep Default + add samples
	$new = array_merge( $presets, $samples );
	update_option( hmpro_presets_option_key(), $new, false );
	return true;
}
