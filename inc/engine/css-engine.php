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

/**
 * Elementor integration: map active preset colors/typography to Elementor Global variables.
 *
 * This restores the old behavior where Elementor widgets inherit preset styling by default,
 * while still allowing per-widget overrides via Elementor's style controls.
 */
add_action( 'wp_head', 'hmpro_output_elementor_global_vars', 21 );
add_action( 'elementor/frontend/after_enqueue_styles', 'hmpro_enqueue_elementor_global_vars', 5 );
add_action( 'elementor/editor/after_enqueue_styles', 'hmpro_enqueue_elementor_global_vars', 5 );

function hmpro_get_elementor_global_vars_css() {
	$preset = hmpro_get_active_preset();
	if ( ! $preset ) {
		return '';
	}

	$primary = $preset['primary'] ?? '#111111';
	$dark    = $preset['dark'] ?? '#0B1220';
	$bg      = $preset['bg'] ?? '#ffffff';
	$text    = $preset['text'] ?? ( $preset['dark'] ?? '#222222' );
	$link    = $preset['link'] ?? $primary;

	$body_font    = hmpro_sanitize_css_font_stack( hmpro_font_token_to_stack( (string) ( $preset['body_font'] ?? 'system' ) ) );
	$heading_font = hmpro_sanitize_css_font_stack( hmpro_font_token_to_stack( (string) ( $preset['heading_font'] ?? 'system' ) ) );

	$css  = ':root{';
	$css .= '--e-global-color-primary:' . esc_html( $primary ) . ';';
	$css .= '--e-global-color-secondary:' . esc_html( $dark ) . ';';
	$css .= '--e-global-color-text:' . esc_html( $text ) . ';';
	$css .= '--e-global-color-accent:' . esc_html( $link ) . ';';
	$css .= '--e-global-color-background:' . esc_html( $bg ) . ';';
	$css .= '--e-global-typography-text-font-family:' . esc_html( $body_font ) . ';';
	$css .= '--e-global-typography-primary-font-family:' . esc_html( $heading_font ) . ';';
	$css .= '--e-global-typography-secondary-font-family:' . esc_html( $heading_font ) . ';';
	$css .= '--e-global-typography-accent-font-family:' . esc_html( $body_font ) . ';';
	$css .= '}';

	return $css;
}

function hmpro_output_elementor_global_vars() {
	$css = hmpro_get_elementor_global_vars_css();
	if ( '' === $css ) {
		return;
	}

	echo '<style id="hmpro-elementor-globals">' . $css . '</style>';
}

function hmpro_enqueue_elementor_global_vars() {
	$css = hmpro_get_elementor_global_vars_css();
	if ( '' === $css ) {
		return;
	}

	// Frontend preview + editor iframe.
	if ( wp_style_is( 'elementor-frontend', 'enqueued' ) ) {
		wp_add_inline_style( 'elementor-frontend', $css );
	}

	// Elementor editor UI.
	if ( wp_style_is( 'elementor-editor', 'enqueued' ) ) {
		wp_add_inline_style( 'elementor-editor', $css );
	}
}
