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

	if ( ! is_array( $presets ) || empty( $presets ) ) {
		$presets = [ hmpro_get_default_preset() ];
		update_option( hmpro_presets_option_key(), $presets, false );
	}

	// Normalize: ensure each preset has an id.
	foreach ( $presets as $i => $p ) {
		if ( empty( $p['id'] ) ) {
			$presets[ $i ]['id'] = sanitize_key( $p['name'] ?? ( 'preset_' . $i ) );
		}
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
		if ( ! empty( $preset['id'] ) && $preset['id'] === $preset_id ) {
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
