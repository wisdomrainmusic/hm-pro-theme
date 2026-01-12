<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

	echo '--hm-primary: ' . esc_html( $preset['primary'] ?? '#111111' ) . ';';
	echo '--hm-dark: ' . esc_html( $preset['dark'] ?? '#000000' ) . ';';
	echo '--hm-bg: ' . esc_html( $preset['bg'] ?? '#ffffff' ) . ';';
	echo '--hm-footer: ' . esc_html( $preset['footer'] ?? '#111111' ) . ';';
	echo '--hm-link: ' . esc_html( $preset['link'] ?? '#2271b1' ) . ';';
	echo '--hm-body-font: ' . esc_html( hmpro_font_token_to_stack( (string) ( $preset['body_font'] ?? 'system' ) ) ) . ';';
	echo '--hm-heading-font: ' . esc_html( hmpro_font_token_to_stack( (string) ( $preset['heading_font'] ?? 'system' ) ) ) . ';';

	echo '}';
	echo '</style>';
}
