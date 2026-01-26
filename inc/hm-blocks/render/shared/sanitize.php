<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared sanitization helpers for HM Pro blocks.
 */

/**
 * KSES sanitize for inline SVG (preset/custom).
 * Returns safe SVG markup or empty string.
 */
function hmpro_kses_svg( $svg ) {
	if ( ! is_string( $svg ) ) {
		return '';
	}

	$svg = trim( $svg );
	if ( $svg === '' ) {
		return '';
	}

	$allowed = array(
		'svg' => array(
			'xmlns' => true,
			'width' => true,
			'height' => true,
			'viewBox' => true,
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'role' => true,
			'aria-hidden' => true,
			'focusable' => true,
			'class' => true,
		),
		'g' => array(
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'transform' => true,
			'class' => true,
		),
		'path' => array(
			'd' => true,
			'fill' => true,
			'stroke' => true,
			'stroke-width' => true,
			'stroke-linecap' => true,
			'stroke-linejoin' => true,
			'transform' => true,
			'class' => true,
		),
		'circle' => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
		'rect'   => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
		'line'   => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true ),
		'polyline' => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
		'polygon'  => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
		'title' => array(),
		'desc'  => array(),
	);

	// wp_kses expects allowed tags array.
	return wp_kses( $svg, $allowed );
}
