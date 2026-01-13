<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize a CSS font-family stack for inline <style> output.
 * Allows only safe characters and preserves quotes (required for families with spaces).
 */
function hmpro_sanitize_css_font_stack( $value ) {
	$value = (string) $value;
	$value = trim( $value );

	// Allow letters, numbers, spaces, commas, quotes, hyphen, underscore, parentheses.
	$value = preg_replace( '/[^a-zA-Z0-9,\s\'"\-\_\(\)\/]/', '', $value );

	// Collapse whitespace
	$value = preg_replace( '/\s+/', ' ', $value );

	return $value;
}

/**
 * Convert a hex color to rgba string.
 */
function hmpro_hex_to_rgba( $hex, $alpha = 1 ) {
	$hex = trim( (string) $hex );
	$hex = ltrim( $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return 'rgba(0,0,0,' . $alpha . ')';
	}

	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );

	$alpha = max( 0, min( 1, (float) $alpha ) );

	return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
}

/**
 * Output CSS variables for the active preset.
 */
add_action( 'wp_head', 'hmpro_output_css_variables', 20 );

function hmpro_output_css_variables() {
	$preset = hmpro_get_active_preset();
	if ( ! $preset ) {
		return;
	}

	echo '<style id="hmpro-css-vars">';
	echo ':root {';

	$accent = $preset['primary'] ?? '#111111';

	echo '--hm-primary: ' . esc_html( $accent ) . ';';
	echo '--hm-dark: ' . esc_html( $preset['dark'] ?? '#000000' ) . ';';
	echo '--hm-bg: ' . esc_html( $preset['bg'] ?? '#ffffff' ) . ';';
	echo '--hm-footer: ' . esc_html( $preset['footer'] ?? '#111111' ) . ';';
	echo '--hm-link: ' . esc_html( $preset['link'] ?? '#2271b1' ) . ';';
	echo '--hmpro-accent: ' . esc_html( $accent ) . ';';
	echo '--hmpro-icon-bg: ' . esc_html( hmpro_hex_to_rgba( $accent, 0.1 ) ) . ';';
	echo '--hmpro-icon-border: ' . esc_html( hmpro_hex_to_rgba( $accent, 0.25 ) ) . ';';
	echo '--hm-body-font: ' . hmpro_sanitize_css_font_stack( hmpro_font_token_to_stack( (string) ( $preset['body_font'] ?? 'system' ) ) ) . ';';
	echo '--hm-heading-font: ' . hmpro_sanitize_css_font_stack( hmpro_font_token_to_stack( (string) ( $preset['heading_font'] ?? 'system' ) ) ) . ';';

	echo '}';
	echo '</style>';
}
