<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$layout     = isset( $attributes['layout'] ) ? sanitize_key( $attributes['layout'] ) : 'top';
$align      = isset( $attributes['align'] ) ? sanitize_key( $attributes['align'] ) : 'left';
$icon_mode  = isset( $attributes['iconMode'] ) ? sanitize_key( $attributes['iconMode'] ) : 'preset';
$preset     = isset( $attributes['iconPreset'] ) ? sanitize_key( $attributes['iconPreset'] ) : 'check';
$custom_svg = isset( $attributes['customSvg'] ) ? (string) $attributes['customSvg'] : '';
$icon_size  = isset( $attributes['iconSize'] ) ? (int) $attributes['iconSize'] : 28;
$title      = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$text       = isset( $attributes['text'] ) ? (string) $attributes['text'] : '';
$link_url   = isset( $attributes['linkUrl'] ) ? esc_url_raw( (string) $attributes['linkUrl'] ) : '';
$link_label = isset( $attributes['linkLabel'] ) ? (string) $attributes['linkLabel'] : '';

$layout = in_array( $layout, array( 'top', 'left' ), true ) ? $layout : 'top';
$align  = in_array( $align, array( 'left', 'center' ), true ) ? $align : 'left';
$icon_size = max( 14, min( 128, $icon_size ) );

$classes = array(
	'hmpro-feature-item',
	'is-layout-' . $layout,
	'is-align-' . $align,
);

$icon_html = '';
if ( $icon_mode === 'custom' && trim( $custom_svg ) !== '' ) {
	$icon_html = hmpro_kses_svg( $custom_svg );
} elseif ( $icon_mode === 'preset' ) {
	$icon_html = hmpro_kses_svg( hmpro_get_svg_preset( $preset ) );
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $classes ),
		'style' => '--hmpro-fi-icon:' . $icon_size . 'px;',
	)
);

echo '<div ' . $wrapper_attributes . '>';
	if ( $icon_html ) {
		echo '<div class="hmpro-feature-item__icon">' . $icon_html . '</div>';
	}

	echo '<div class="hmpro-feature-item__content">';
		if ( $title !== '' ) {
			echo '<h3 class="hmpro-feature-item__title">' . esc_html( $title ) . '</h3>';
		}
		if ( $text !== '' ) {
			echo '<p class="hmpro-feature-item__text">' . esc_html( $text ) . '</p>';
		}
		if ( $link_url && $link_label ) {
			echo '<a class="hmpro-feature-item__link" href="' . esc_url( $link_url ) . '">' . esc_html( $link_label ) . '</a>';
		}
	echo '</div>';
echo '</div>';
