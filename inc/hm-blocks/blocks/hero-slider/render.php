<?php
/**
 * Render callback for HM Hero Slider block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hmpro_hero_sanitize_color' ) ) {
	function hmpro_hero_sanitize_color( $c ) {
		$c = is_string( $c ) ? trim( $c ) : '';
		if ( $c === '' ) {
			return '';
		}
		// #RGB, #RRGGBB, #RRGGBBAA
		if ( preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $c ) ) {
			return $c;
		}
		// rgb()/rgba()
		if ( preg_match( '/^rgba?\(([^)]+)\)$/i', $c ) ) {
			return $c;
		}
		return '';
	}
}

$attrs = isset( $attributes ) && is_array( $attributes ) ? $attributes : [];

$only_homepage = ! empty( $attrs['onlyHomepage'] );
if ( $only_homepage && ! is_front_page() ) {
	return '';
}

$full_width = isset( $attrs['fullWidth'] ) ? (bool) $attrs['fullWidth'] : true;
$max_width  = isset( $attrs['maxWidth'] ) ? absint( $attrs['maxWidth'] ) : 1200;
$height     = isset( $attrs['height'] ) ? absint( $attrs['height'] ) : 520;
$hide_mobile = ! empty( $attrs['hideOnMobile'] );

// Background fit: "cover" (default) or "contain" (show full image).
$image_fit = isset( $attrs['imageFit'] ) ? (string) $attrs['imageFit'] : 'cover';
$image_fit = ( $image_fit === 'contain' ) ? 'contain' : 'cover';

if ( $max_width < 720 ) {
	$max_width = 720;
}
if ( $max_width > 1800 ) {
	$max_width = 1800;
}

if ( $height < 200 ) {
	$height = 200;
}
if ( $height > 1200 ) {
	$height = 1200;
}

$overlay = isset( $attrs['overlayOpacity'] ) ? (float) $attrs['overlayOpacity'] : 0.35;
if ( $overlay < 0 ) {
	$overlay = 0;
}
if ( $overlay > 0.8 ) {
	$overlay = 0.8;
}

$autoplay   = isset( $attrs['autoplay'] ) ? (bool) $attrs['autoplay'] : true;
$interval   = isset( $attrs['interval'] ) ? absint( $attrs['interval'] ) : 4500;
$show_arrows = isset( $attrs['showArrows'] ) ? (bool) $attrs['showArrows'] : true;
$show_dots   = isset( $attrs['showDots'] ) ? (bool) $attrs['showDots'] : true;

if ( $interval < 1500 ) {
	$interval = 1500;
}
if ( $interval > 20000 ) {
	$interval = 20000;
}

$group_x = isset( $attrs['groupX'] ) ? (int) $attrs['groupX'] : 0;
$group_y = isset( $attrs['groupY'] ) ? (int) $attrs['groupY'] : 0;
$m_group_x = isset( $attrs['mobileGroupX'] ) ? (int) $attrs['mobileGroupX'] : 0;
$m_group_y = isset( $attrs['mobileGroupY'] ) ? (int) $attrs['mobileGroupY'] : 0;
$m_title_scale = isset( $attrs['mobileTitleScale'] ) ? (float) $attrs['mobileTitleScale'] : 0.88; // backward-compat.
$content_scale = isset( $attrs['contentScale'] ) ? (float) $attrs['contentScale'] : 1.0;
$m_content_scale = isset( $attrs['mobileContentScale'] ) ? (float) $attrs['mobileContentScale'] : 0.92;

$group_x = max( -300, min( 300, $group_x ) );
$group_y = max( -300, min( 300, $group_y ) );
$m_group_x = max( -300, min( 300, $m_group_x ) );
$m_group_y = max( -300, min( 300, $m_group_y ) );
if ( $m_title_scale < 0.6 ) {
	$m_title_scale = 0.6;
}
if ( $m_title_scale > 1.2 ) {
	$m_title_scale = 1.2;
}

// Clamp scales.
if ( $content_scale < 0.7 ) {
	$content_scale = 0.7;
}
if ( $content_scale > 1.3 ) {
	$content_scale = 1.3;
}
if ( $m_content_scale < 0.6 ) {
	$m_content_scale = 0.6;
}
if ( $m_content_scale > 1.3 ) {
	$m_content_scale = 1.3;
}

// If new mobile content scale wasn't set (still default), fall back to old title scale for backwards compatibility.
// This keeps existing pages looking similar after update.
if ( ! isset( $attrs['mobileContentScale'] ) && isset( $attrs['mobileTitleScale'] ) ) {
	$m_content_scale = $m_title_scale;
}

$slides = [];
if ( ! empty( $attrs['slides'] ) && is_array( $attrs['slides'] ) ) {
	$slides = $attrs['slides'];
}

// Normalize slides to 1..6.
$slides = array_values( array_filter( $slides, 'is_array' ) );
while ( count( $slides ) < 1 ) {
	$slides[] = [];
}
if ( count( $slides ) > 6 ) {
	$slides = array_slice( $slides, 0, 6 );
}

$classes = [
	'hmpro-block',
	'hmpro-hero-slider',
	$full_width ? 'is-fullwidth' : 'is-boxed',
	$hide_mobile ? 'is-hide-mobile' : '',
];

$wrapper_attrs = get_block_wrapper_attributes( [
	'class' => implode( ' ', array_filter( $classes ) ),
	'style' => sprintf(
		'--hmpro-hero-h:%dpx;--hmpro-hero-bg-fit:%s;--hmpro-hero-overlay:%s;--hmpro-hero-maxw:%dpx;--hmpro-hero-group-x:%dpx;--hmpro-hero-group-y:%dpx;--hmpro-hero-group-x-m:%dpx;--hmpro-hero-group-y-m:%dpx;--hmpro-hero-scale:%s;--hmpro-hero-scale-m:%s;--hmpro-hero-title-scale-m:%s;',
		$height,
		esc_attr( $image_fit ),
		rtrim( rtrim( (string) $overlay, '0' ), '.' ),
		$max_width,
		$group_x,
		$group_y,
		$m_group_x,
		$m_group_y,
		rtrim( rtrim( (string) $content_scale, '0' ), '.' ),
		rtrim( rtrim( (string) $m_content_scale, '0' ), '.' ),
		rtrim( rtrim( (string) $m_title_scale, '0' ), '.' )
	),
] );

$has_multiple = count( $slides ) > 1;

echo '<div ' . $wrapper_attrs . '>';
echo '<div class="hmpro-hero__frame">';

echo '<div class="hmpro-hero__slides" data-autoplay="' . esc_attr( $autoplay ? '1' : '0' ) . '" data-interval="' . esc_attr( (string) $interval ) . '">';

foreach ( $slides as $i => $s ) {
	$s = is_array( $s ) ? $s : [];

	$media_id   = isset( $s['mediaId'] ) ? absint( $s['mediaId'] ) : 0;
	$media_url  = isset( $s['mediaUrl'] ) ? esc_url_raw( (string) $s['mediaUrl'] ) : '';
	
	$title      = isset( $s['title'] ) ? sanitize_text_field( (string) $s['title'] ) : '';
	$subtitle   = isset( $s['subtitle'] ) ? sanitize_text_field( (string) $s['subtitle'] ) : '';
	$btn_text   = isset( $s['buttonText'] ) ? sanitize_text_field( (string) $s['buttonText'] ) : '';
	$btn_url    = isset( $s['buttonUrl'] ) ? esc_url_raw( (string) $s['buttonUrl'] ) : '';

	$t_col = hmpro_hero_sanitize_color( $s['titleColor'] ?? '' );
	$st_col = hmpro_hero_sanitize_color( $s['subtitleColor'] ?? '' );
	$b_col = hmpro_hero_sanitize_color( $s['buttonTextColor'] ?? '' );
	$bbg_col = hmpro_hero_sanitize_color( $s['buttonBgColor'] ?? '' );

	$resolved_url = '';
	if ( $media_id ) {
		$resolved_url = wp_get_attachment_image_url( $media_id, 'full' );
		if ( ! $resolved_url ) {
			$resolved_url = wp_get_attachment_url( $media_id );
		}
	}
	if ( ! $resolved_url && $media_url ) {
		$resolved_url = $media_url;
	}


	$is_active = ( $i === 0 );
	$slide_classes = 'hmpro-hero-slide' . ( $is_active ? ' is-active' : '' );

	$style_bits = [];
	if ( $t_col ) {
		$style_bits[] = '--hmpro-hero-title-color:' . $t_col;
	}
	if ( $st_col ) {
		$style_bits[] = '--hmpro-hero-subtitle-color:' . $st_col;
	}
	if ( $b_col ) {
		$style_bits[] = '--hmpro-hero-btn-color:' . $b_col;
	}
	if ( $bbg_col ) {
		$style_bits[] = '--hmpro-hero-btn-bg:' . $bbg_col;
	}
	$slide_style = $style_bits ? ' style="' . esc_attr( implode( ';', $style_bits ) ) . ';"' : '';

	echo '<div class="' . esc_attr( $slide_classes ) . '" data-index="' . esc_attr( (string) $i ) . '" aria-hidden="' . esc_attr( $is_active ? 'false' : 'true' ) . '"' . $slide_style . '>';
	echo '<div class="hmpro-hero-slide__media">';

	$bg = $resolved_url ? ' style="background-image:url(' . esc_url( $resolved_url ) . ')"' : '';
	echo '<div class="hmpro-hero-slide__bg"' . $bg . '></div>';

	echo '</div>'; // media

	echo '<div class="hmpro-hero__overlay" aria-hidden="true"></div>';

	echo '<div class="hmpro-hero__inner">';
	echo '<div class="hmpro-hero__content">';

	if ( $title !== '' ) {
		echo '<div class="hmpro-hero__title">' . esc_html( $title ) . '</div>';
	}
	if ( $subtitle !== '' ) {
		echo '<div class="hmpro-hero__subtitle">' . esc_html( $subtitle ) . '</div>';
	}
	if ( $btn_text !== '' && $btn_url !== '' ) {
		echo '<a class="hmpro-hero__btn" href="' . esc_url( $btn_url ) . '">' . esc_html( $btn_text ) . '</a>';
	}

	echo '</div>'; // content
	echo '</div>'; // inner

	echo '</div>'; // slide
}

echo '</div>'; // slides

if ( $has_multiple && $show_arrows ) {
	echo '<button class="hmpro-hero__arrow hmpro-hero__arrow--prev" type="button" aria-label="Previous slide">‹</button>';
	echo '<button class="hmpro-hero__arrow hmpro-hero__arrow--next" type="button" aria-label="Next slide">›</button>';
}

if ( $has_multiple && $show_dots ) {
	echo '<div class="hmpro-hero__dots" role="tablist" aria-label="Slides">';
	for ( $i = 0; $i < count( $slides ); $i++ ) {
		$active = ( $i === 0 );
		echo '<button class="hmpro-hero__dot' . ( $active ? ' is-active' : '' ) . '" type="button" data-index="' . esc_attr( (string) $i ) . '" aria-label="Go to slide ' . esc_attr( (string) ( $i + 1 ) ) . '"></button>';
	}
	echo '</div>';
}

echo '</div>'; // frame
echo '</div>'; // wrapper
