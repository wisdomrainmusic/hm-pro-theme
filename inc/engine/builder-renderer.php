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

		foreach ( $row['columns'] as $col ) {
			if ( ! is_array( $col ) ) {
				continue;
			}

			$col_id = isset( $col['id'] ) ? sanitize_key( $col['id'] ) : '';
			$width  = isset( $col['width'] ) ? absint( $col['width'] ) : 12;
			if ( $width < 1 || $width > 12 ) {
				$width = 12;
			}

			echo '<div class="hmpro-builder-col hmpro-col-' . esc_attr( (string) $width ) . '" data-col="' . esc_attr( $col_id ) . '">';

			$components = isset( $col['components'] ) && is_array( $col['components'] ) ? $col['components'] : array();
			foreach ( $components as $comp ) {
				hmpro_builder_render_component( $comp );
			}

			echo '</div>';
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
			hmpro_builder_comp_search( $set );
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
	if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
		echo '<a class="hmpro-logo" href="' . esc_url( $home ) . '" rel="home">';
		the_custom_logo();
		echo '</a>';
		return;
	}
	echo '<a class="hmpro-logo hmpro-logo-text" href="' . esc_url( $home ) . '" rel="home">';
	echo esc_html( get_bloginfo( 'name' ) );
	echo '</a>';
}

function hmpro_builder_comp_menu( array $set ) {
	// Defaults: primary (later we add settings modal)
	$location = isset( $set['location'] ) ? sanitize_key( (string) $set['location'] ) : '';
	$depth    = isset( $set['depth'] ) ? absint( $set['depth'] ) : 2;
	if ( $depth < 1 || $depth > 5 ) {
		$depth = 2;
	}

	$locations = get_nav_menu_locations();
	if ( '' === $location ) {
		if ( isset( $locations['primary'] ) ) {
			$location = 'primary';
		} else {
			$keys     = array_keys( (array) $locations );
			$location = $keys[0] ?? '';
		}
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

function hmpro_builder_comp_search( array $set ) {
	$ph = isset( $set['placeholder'] ) ? sanitize_text_field( (string) $set['placeholder'] ) : __( 'Search…', 'hmpro' );
	echo '<form class="hmpro-search" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">';
	echo '<label class="screen-reader-text" for="hmpro-search-field">' . esc_html__( 'Search for:', 'hmpro' ) . '</label>';
	echo '<input id="hmpro-search-field" class="hmpro-search-field" type="search" name="s" value="' . esc_attr( get_search_query() ) . '" placeholder="' . esc_attr( $ph ) . '" />';
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
