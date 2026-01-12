<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build runtime CSS variables from active preset and inject as inline CSS.
 */
add_action( 'wp_enqueue_scripts', 'hmpro_output_runtime_css', 20 );

function hmpro_output_runtime_css() {
	// Ensure base stylesheet is enqueued before adding inline styles.
	$handle = 'hmpro-base';

	$preset_id = hmpro_get_active_preset_id();
	$preset    = hmpro_get_preset_by_id( $preset_id );

	if ( empty( $preset ) || ! is_array( $preset ) ) {
		return;
	}

	$vars = hmpro_preset_to_css_vars( $preset );
	if ( empty( $vars ) ) {
		return;
	}

	$css = ":root{\n";
	foreach ( $vars as $k => $v ) {
		$css .= "\t" . $k . ':' . $v . ";\n";
	}
	$css .= "}\n";

	// Also expose a body class for debugging/styling hooks.
	$css .= "body{--hm-active-preset:'" . esc_attr( $preset_id ) . "';}\n";

	wp_add_inline_style( $handle, $css );
}

/**
 * Map preset fields to HM Pro Theme CSS variables.
 */
function hmpro_preset_to_css_vars( array $preset ) {
	$map = [
		'primary' => '--hm-primary',
		'dark'    => '--hm-dark',
		'bg'      => '--hm-bg',
		'surface' => '--hm-surface',
		'text'    => '--hm-text',
		'muted'   => '--hm-muted',
		'link'    => '--hm-link',
		'border'  => '--hm-border',
		'footer'  => '--hm-footer',
	];

	$out = [];

	foreach ( $map as $field => $var ) {
		if ( empty( $preset[ $field ] ) ) {
			continue;
		}

		$val = (string) $preset[ $field ];
		$val = trim( $val );

		// Only allow safe CSS values (colors expected).
		// Accept hex/rgb/rgba/hsl/hsla/var(...) as future-proof.
		if ( preg_match( '/^(#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})|rgba?\([^\)]+\)|hsla?\([^\)]+\)|var\([^\)]+\))$/', $val ) ) {
			$out[ $var ] = $val;
		}
	}

	// Fonts (simple mapping for now; real font loading later)
	if ( ! empty( $preset['body_font'] ) ) {
		$out['--hm-body-font'] = hmpro_font_token_to_stack( (string) $preset['body_font'] );
	}
	if ( ! empty( $preset['heading_font'] ) ) {
		$out['--hm-heading-font'] = hmpro_font_token_to_stack( (string) $preset['heading_font'] );
	}

	return $out;
}

/**
 * Convert a stored font token into a safe font-family stack.
 * For now we only support "system". Later we will add Google/Local fonts.
 */
function hmpro_font_token_to_stack( $token ) {
	$token = strtolower( trim( (string) $token ) );

	if ( 'system' === $token || '' === $token ) {
		return "system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif";
	}

	// Fallback: treat as raw font name, wrap in quotes, append safe stack.
	$token = preg_replace( '/[^a-z0-9 \-_]/i', '', $token );
	return "'" . $token . "',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif";
}
