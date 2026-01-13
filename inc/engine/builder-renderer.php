<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builder Renderer (Commit 018)
 * Renders the stored layout safely on frontend.
 */

add_action(
	'hmpro/header/render_region',
	function ( $region_key ) {
		hmpro_builder_render_region( 'header', $region_key );
	},
	10,
	1
);

add_action(
	'hmpro/footer/render_region',
	function ( $region_key ) {
		hmpro_builder_render_region( 'footer', $region_key );
	},
	10,
	1
);

add_action( 'wp_head', function () {
	$height = absint( get_theme_mod( 'hmpro_logo_max_height', 56 ) );
	echo '<style>:root{--hmpro-logo-max-height:' . $height . 'px;}</style>';
}, 20 );

function hmpro_builder_render_region( $area, $region_key ) {
	$area = ( 'footer' === $area ) ? 'footer' : 'header';
	if ( ! function_exists( 'hmpro_builder_get_layout' ) ) {
		return;
	}

	$layout  = hmpro_builder_get_layout( $area );
	$regions = isset( $layout['regions'] ) && is_array( $layout['regions'] ) ? $layout['regions'] : array();
	$rows    = isset( $regions[ $region_key ] ) && is_array( $regions[ $region_key ] ) ? $regions[ $region_key ] : array();

	if ( empty( $rows ) ) {
		return;
	}

	echo '<div class="hmpro-builder-region hmpro-builder-region-' . esc_attr( $region_key ) . '">';

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || empty( $row['columns'] ) || ! is_array( $row['columns'] ) ) {
			continue;
		}

		$row_id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';
		echo '<div class="hmpro-builder-row" data-row="' . esc_attr( $row_id ) . '">';

		$col_index = 0;
		foreach ( $row['columns'] as $col ) {
			if ( ! is_array( $col ) ) {
				continue;
			}

			$col_id = isset( $col['id'] ) ? sanitize_key( $col['id'] ) : '';
			$width  = isset( $col['width'] ) ? absint( $col['width'] ) : 12;
			if ( $width < 1 || $width > 12 ) {
				$width = 12;
			}
			$align_class = 'hmpro-align-left';
			if ( 1 === $col_index ) {
				$align_class = 'hmpro-align-center';
			} elseif ( 2 === $col_index ) {
				$align_class = 'hmpro-align-right';
			}

			echo '<div class="hmpro-builder-col hmpro-col-' . esc_attr( (string) $width ) . ' ' . esc_attr( $align_class ) . '" data-col="' . esc_attr( $col_id ) . '">';

			$components = isset( $col['components'] ) && is_array( $col['components'] ) ? $col['components'] : array();
			foreach ( $components as $comp ) {
				hmpro_builder_render_component( $comp );
			}

			echo '</div>';
			$col_index++;
		}

		echo '</div>';
	}

	echo '</div>';
}

function hmpro_builder_render_component( $comp ) {
	if ( ! is_array( $comp ) ) {
		return;
	}

	$type = isset( $comp['type'] ) ? sanitize_key( $comp['type'] ) : '';
	$id   = isset( $comp['id'] ) ? sanitize_key( $comp['id'] ) : '';
	$set  = isset( $comp['settings'] ) && is_array( $comp['settings'] ) ? $comp['settings'] : array();

	$classes = array( 'hmpro-builder-comp', 'hmpro-comp-' . $type );
	echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-comp="' . esc_attr( $id ) . '">';

	switch ( $type ) {
		case 'logo':
			hmpro_builder_comp_logo();
			break;
		case 'menu':
			hmpro_builder_comp_menu( $set );
			break;
		case 'search':
			hmpro_builder_comp_search( $set, $id );
			break;
		case 'cart':
			if ( class_exists( 'WooCommerce' ) ) {
				hmpro_builder_comp_cart();
			}
			break;
		case 'button':
			hmpro_builder_comp_button( $set );
			break;
		case 'html':
			hmpro_builder_comp_html( $set );
			break;
		case 'spacer':
			hmpro_builder_comp_spacer( $set );
			break;
		default:
			do_action( 'hmpro/builder/render_component', $type, $set );
			break;
	}

	echo '</div>';
}

function hmpro_builder_comp_logo() {
	$home = home_url( '/' );

	if ( function_exists( 'get_custom_logo' ) && has_custom_logo() ) {
		$logo = get_custom_logo(); // returns <a class="custom-logo-link"><img class="custom-logo"></a>
		if ( is_string( $logo ) && '' !== $logo ) {
			if ( false === strpos( $logo, 'hmpro-logo' ) ) {
				$logo = preg_replace( '/class=("|\')custom-logo-link(.*?)("|\')/i', 'class=$1custom-logo-link hmpro-logo$2$3', $logo );
			}
			echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}
		return;
	}

	echo '<a class="hmpro-logo hmpro-logo-text" href="' . esc_url( $home ) . '" rel="home">';
	echo esc_html( get_bloginfo( 'name' ) );
	echo '</a>';
}

/**
 * Pick a menu location that actually has a menu assigned.
 * Prefer builder-friendly locations first, but support legacy keys too.
 */
function hmpro_builder_pick_menu_location( array $preferred_keys ) {
	$locations = (array) get_nav_menu_locations(); // [ location_key => menu_id ]
	if ( empty( $locations ) ) {
		return '';
	}

	// 1) Preferred keys that have a menu_id.
	foreach ( $preferred_keys as $key ) {
		$key = sanitize_key( (string) $key );
		if ( isset( $locations[ $key ] ) && absint( $locations[ $key ] ) > 0 ) {
			return $key;
		}
	}

	// 2) Any assigned location.
	foreach ( $locations as $key => $menu_id ) {
		if ( absint( $menu_id ) > 0 ) {
			return sanitize_key( (string) $key );
		}
	}

	return '';
}

function hmpro_builder_comp_menu( array $set ) {
	// Defaults: primary (later we add settings modal)
	$location = isset( $set['location'] ) ? sanitize_key( (string) $set['location'] ) : '';
	$depth    = isset( $set['depth'] ) ? absint( $set['depth'] ) : 2;
	if ( $depth < 1 || $depth > 5 ) {
		$depth = 2;
	}

	$locations = (array) get_nav_menu_locations();

	// If a location was set explicitly, but no menu is assigned to it, ignore and auto-pick.
	if ( '' !== $location ) {
		if ( ! isset( $locations[ $location ] ) || absint( $locations[ $location ] ) <= 0 ) {
			$location = '';
		}
	}

	// Smart pick: support both new builder keys and legacy theme keys.
	if ( '' === $location ) {
		$location = hmpro_builder_pick_menu_location( array( 'primary', 'hm_primary', 'topbar', 'footer', 'hm_footer' ) );
	}

	$location = sanitize_key( (string) $location );
	if ( '' === $location ) {
		return;
	}

	wp_nav_menu(
		array(
			'theme_location' => $location,
			'container'      => 'nav',
			'container_class'=> 'hmpro-nav',
			'menu_class'     => 'hmpro-menu',
			'fallback_cb'    => false,
			'depth'          => $depth,
		)
	);
}

function hmpro_builder_comp_search( array $set, $comp_id = '' ) {
	$ph = isset( $set['placeholder'] ) ? sanitize_text_field( (string) $set['placeholder'] ) : __( 'Search…', 'hmpro' );
	$comp_id  = sanitize_key( (string) $comp_id );
	$field_id = 'hmpro-search-field';
	if ( '' !== $comp_id ) {
		$field_id = 'hmpro-search-field-' . $comp_id;
	}
	echo '<form class="hmpro-search" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">';
	echo '<label class="screen-reader-text" for="' . esc_attr( $field_id ) . '">' . esc_html__( 'Search for:', 'hmpro' ) . '</label>';
	echo '<input id="' . esc_attr( $field_id ) . '" class="hmpro-search-field" type="search" name="s" value="' . esc_attr( get_search_query() ) . '" placeholder="' . esc_attr( $ph ) . '" required />';
	echo '<button class="hmpro-search-submit" type="submit">' . esc_html__( 'Search', 'hmpro' ) . '</button>';
	echo '</form>';
}

function hmpro_builder_comp_cart() {
	if ( ! function_exists( 'wc_get_cart_url' ) ) {
		return;
	}
	$url = wc_get_cart_url();
	if ( ! $url ) {
		return;
	}
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	echo '<a class="hmpro-cart" href="' . esc_url( $url ) . '">';
	echo esc_html__( 'Cart', 'hmpro' ) . ' <span class="hmpro-cart-count">' . esc_html( (string) $count ) . '</span>';
	echo '</a>';
}

function hmpro_builder_comp_button( array $set ) {
	$text = isset( $set['text'] ) ? sanitize_text_field( (string) $set['text'] ) : __( 'Button', 'hmpro' );
	$url  = isset( $set['url'] ) ? esc_url( (string) $set['url'] ) : '#';
	echo '<a class="hmpro-btn" href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
}

function hmpro_builder_comp_html( array $set ) {
	$content = isset( $set['content'] ) ? (string) $set['content'] : '';
	if ( '' === trim( $content ) ) {
		return;
	}
	echo '<div class="hmpro-html">' . wp_kses_post( $content ) . '</div>';
}

function hmpro_builder_comp_spacer( array $set ) {
	$w     = isset( $set['width'] ) ? absint( $set['width'] ) : 0;
	$h     = isset( $set['height'] ) ? absint( $set['height'] ) : 0;
	$style = '';
	if ( $w ) {
		$style .= 'width:' . $w . 'px;';
	}
	if ( $h ) {
		$style .= 'height:' . $h . 'px;';
	}
	echo '<span class="hmpro-spacer" style="' . esc_attr( $style ) . '"></span>';
}
