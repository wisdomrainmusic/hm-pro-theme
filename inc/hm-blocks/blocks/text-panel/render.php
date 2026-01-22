<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HM Text Panel render (dynamic).
 */

if ( ! function_exists( 'hmpro_tp_hex_to_rgb' ) ) {
	function hmpro_tp_hex_to_rgb( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) ) {
			return null;
		}
		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}
}

$count  = isset( $attributes['columnsCount'] ) ? max( 1, min( 4, (int) $attributes['columnsCount'] ) ) : 2;
$gap    = isset( $attributes['columnGap'] ) ? max( 0, (int) $attributes['columnGap'] ) : 24;
$stack  = ! empty( $attributes['stackOnMobile'] );
$align  = isset( $attributes['textAlign'] ) ? (string) $attributes['textAlign'] : 'left';

$title_size   = isset( $attributes['titleSize'] ) ? max( 12, (int) $attributes['titleSize'] ) : 22;
$title_weight = isset( $attributes['titleWeight'] ) ? max( 300, min( 900, (int) $attributes['titleWeight'] ) ) : 700;
$body_size    = isset( $attributes['bodySize'] ) ? max( 12, (int) $attributes['bodySize'] ) : 16;
$line_height  = isset( $attributes['lineHeight'] ) ? (float) $attributes['lineHeight'] : 1.6;

$text_color      = isset( $attributes['textColor'] ) ? (string) $attributes['textColor'] : '';
$link_color      = isset( $attributes['linkColor'] ) ? (string) $attributes['linkColor'] : '';
$link_hover      = isset( $attributes['linkHoverColor'] ) ? (string) $attributes['linkHoverColor'] : '';

$panel_enabled   = ! empty( $attributes['panelEnabled'] );
$panel_color     = isset( $attributes['panelColor'] ) ? (string) $attributes['panelColor'] : '';
$panel_opacity   = isset( $attributes['panelOpacity'] ) ? (int) $attributes['panelOpacity'] : 85;
$panel_blur      = isset( $attributes['panelBlur'] ) ? (int) $attributes['panelBlur'] : 0;
$panel_radius    = isset( $attributes['panelRadius'] ) ? (int) $attributes['panelRadius'] : 16;
$panel_padding   = isset( $attributes['panelPadding'] ) ? (int) $attributes['panelPadding'] : 22;
$panel_border    = ! empty( $attributes['panelBorder'] );
$panel_border_cl = isset( $attributes['panelBorderColor'] ) ? (string) $attributes['panelBorderColor'] : 'rgba(0,0,0,0.10)';

$columns = isset( $attributes['columns'] ) && is_array( $attributes['columns'] ) ? $attributes['columns'] : array();
$columns = array_slice( $columns, 0, $count );
while ( count( $columns ) < $count ) {
	$columns[] = array( 'heading' => '', 'body' => '' );
}

$widths = isset( $attributes['columnWidths'] ) && is_array( $attributes['columnWidths'] ) ? $attributes['columnWidths'] : array();
$widths = array_slice( $widths, 0, $count );
while ( count( $widths ) < $count ) {
	$widths[] = (int) floor( 100 / $count );
}
$widths = array_map( function( $n ) {
	$n = (int) $n;
	return max( 5, min( 95, $n ) );
}, $widths );
$sum = array_sum( $widths );
if ( $sum > 0 && 100 !== $sum ) {
	$widths = array_map( function( $n ) use ( $sum ) {
		return (int) round( ( $n / $sum ) * 100 );
	}, $widths );
	$diff = 100 - array_sum( $widths );
	if ( 0 !== $diff ) {
		$idx = array_keys( $widths, max( $widths ), true );
		$idx = ! empty( $idx ) ? (int) $idx[0] : 0;
		$widths[ $idx ] = max( 5, min( 95, (int) $widths[ $idx ] + $diff ) );
	}
}

$classes = array(
	'hmpro-block',
	'hmpro-text-panel',
	'is-align-' . sanitize_html_class( $align ),
);
if ( $panel_enabled ) { $classes[] = 'is-panel'; }
if ( $stack ) { $classes[] = 'is-stack-mobile'; }

$style = array();
$style[] = '--hm-tp-gap:' . $gap . 'px';
$style[] = '--hm-tp-title-size:' . $title_size . 'px';
$style[] = '--hm-tp-title-weight:' . $title_weight;
$style[] = '--hm-tp-body-size:' . $body_size . 'px';
$style[] = '--hm-tp-line:' . $line_height;
if ( $text_color ) { $style[] = '--hm-tp-text:' . esc_attr( $text_color ); }
if ( $link_color ) { $style[] = '--hm-tp-link:' . esc_attr( $link_color ); }
if ( $link_hover ) { $style[] = '--hm-tp-link-hover:' . esc_attr( $link_hover ); }

if ( $panel_enabled ) {
	$op = max( 0, min( 100, (int) $panel_opacity ) ) / 100;
	$rgba = '';
	if ( $panel_color && false !== strpos( $panel_color, 'rgba' ) ) {
		// Allow rgba passthrough.
		$rgba = $panel_color;
	} elseif ( $panel_color && '#' === substr( $panel_color, 0, 1 ) ) {
		$rgb = hmpro_tp_hex_to_rgb( $panel_color );
		if ( $rgb ) {
			$rgba = 'rgba(' . (int) $rgb[0] . ',' . (int) $rgb[1] . ',' . (int) $rgb[2] . ',' . $op . ')';
		}
	}
	if ( $rgba ) { $style[] = '--hm-tp-panel-bg:' . $rgba; }
	$style[] = '--hm-tp-panel-blur:' . max( 0, min( 20, (int) $panel_blur ) ) . 'px';
	$style[] = '--hm-tp-panel-radius:' . max( 0, (int) $panel_radius ) . 'px';
	$style[] = '--hm-tp-panel-pad:' . max( 0, (int) $panel_padding ) . 'px';
	$style[] = '--hm-tp-panel-border:' . ( $panel_border ? '1px solid ' . $panel_border_cl : '0' );
}

$grid_cols = array();
foreach ( $widths as $w ) {
	$grid_cols[] = (int) $w . 'fr';
}

$wrapper_attributes = get_block_wrapper_attributes( array(
	'class' => implode( ' ', $classes ),
	'style' => implode( ';', $style ),
) );

echo '<section ' . $wrapper_attributes . '>';
echo '<div class="hmpro-tp__inner' . ( $panel_enabled ? ' hmpro-tp__panel' : '' ) . '">';
echo '<div class="hmpro-tp__grid" style="grid-template-columns:' . esc_attr( implode( ' ', $grid_cols ) ) . ';">';

for ( $i = 0; $i < $count; $i++ ) {
	$col = is_array( $columns[ $i ] ) ? $columns[ $i ] : array();
	$heading = isset( $col['heading'] ) ? (string) $col['heading'] : '';
	$body    = isset( $col['body'] ) ? (string) $col['body'] : '';

	echo '<div class="hmpro-tp__col">';
	if ( '' !== trim( wp_strip_all_tags( $heading ) ) ) {
		echo '<h3 class="hmpro-tp__title">' . esc_html( wp_strip_all_tags( $heading ) ) . '</h3>';
	}
	if ( '' !== trim( wp_strip_all_tags( $body ) ) ) {
		echo '<div class="hmpro-tp__body">' . wp_kses_post( $body ) . '</div>';
	}
	echo '</div>';
}

echo '</div>';
echo '</div>';
echo '</section>';
