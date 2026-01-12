<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font registry (tokens -> Google family + safe fallback stack).
 */
function hmpro_font_registry() {
	return [
		'system'            => [
			'google'   => null,
			'fallback' => "system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif",
		],
		'inter'             => [
			'google'   => 'Inter:wght@300;400;500;600;700',
			'fallback' => "'Inter',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif",
		],
		'poppins'           => [
			'google'   => 'Poppins:wght@300;400;500;600;700',
			'fallback' => "'Poppins',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif",
		],
		'lato'              => [
			'google'   => 'Lato:wght@300;400;700;900',
			'fallback' => "'Lato',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif",
		],
		'open_sans'         => [
			'google'   => 'Open+Sans:wght@300;400;600;700',
			'fallback' => "'Open Sans',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif",
		],
		'playfair_display'  => [
			'google'   => 'Playfair+Display:wght@400;500;600;700',
			'fallback' => "'Playfair Display',Georgia,serif",
		],

		// Handwritten / signature vibes 😄
		'caveat'            => [
			'google'   => 'Caveat:wght@400;500;600;700',
			'fallback' => "'Caveat','Comic Sans MS',cursive",
		],
		'dancing_script'    => [
			'google'   => 'Dancing+Script:wght@400;500;600;700',
			'fallback' => "'Dancing Script','Comic Sans MS',cursive",
		],
		'pacifico'          => [
			'google'   => 'Pacifico',
			'fallback' => "'Pacifico','Comic Sans MS',cursive",
		],
	];
}

/**
 * Normalize incoming token to a registry key.
 */
function hmpro_normalize_font_token( $token ) {
	$token = strtolower( trim( (string) $token ) );
	$token = str_replace( [ ' ', '-' ], '_', $token );
	$token = preg_replace( '/[^a-z0-9_]/', '', $token );
	return $token ?: 'system';
}

/**
 * Enqueue Google Fonts for active preset (only if needed).
 */
add_action( 'wp_enqueue_scripts', 'hmpro_enqueue_active_preset_fonts', 5 );

function hmpro_enqueue_active_preset_fonts() {
	$preset_id = hmpro_get_active_preset_id();
	$preset    = hmpro_get_preset_by_id( $preset_id );
	if ( empty( $preset ) || ! is_array( $preset ) ) {
		return;
	}

	$reg = hmpro_font_registry();

	$body_token    = hmpro_normalize_font_token( $preset['body_font'] ?? 'system' );
	$heading_token = hmpro_normalize_font_token( $preset['heading_font'] ?? 'system' );

	$families = [];
	foreach ( [ $body_token, $heading_token ] as $token ) {
		if ( empty( $reg[ $token ] ) || empty( $reg[ $token ]['google'] ) ) {
			continue;
		}
		$families[] = $reg[ $token ]['google'];
	}

	$families = array_values( array_unique( $families ) );
	if ( empty( $families ) ) {
		return;
	}

	// Build Google Fonts URL (v2).
	$query = [
		'family'  => implode( '&family=', $families ),
		'display' => 'swap',
	];

	$url = 'https://fonts.googleapis.com/css2?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );

	wp_enqueue_style( 'hmpro-fonts', $url, [], HMPRO_VERSION );
}

/**
 * Convert a token to a safe font-family stack.
 */
function hmpro_font_token_to_stack( $token ) {
	$reg   = hmpro_font_registry();
	$token = hmpro_normalize_font_token( $token );

	if ( ! empty( $reg[ $token ]['fallback'] ) ) {
		return $reg[ $token ]['fallback'];
	}

	return $reg['system']['fallback'];
}
