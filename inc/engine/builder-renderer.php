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


add_action( 'wp_footer', function () {
	hmpro_builder_output_social_sprite();
}, 20 );

/**
 * Generic renderer for layout rows (used by header/footer regions + mega layouts).
 * $context:
 * - header/footer: existing components
 * - mega: includes mega_column_menu + image
 */
function hmpro_builder_render_layout_rows( array $rows, $context = 'header' ) {
	if ( empty( $rows ) ) {
		return;
	}

	echo '<div class="hmpro-builder-region hmpro-builder-region-generic hmpro-context-' . esc_attr( $context ) . '">';

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

			// Alignment class: keep legacy behavior for 3 columns, neutral for mega.
			$align_class = 'hmpro-align-left';
			if ( 'mega' !== $context ) {
				if ( 1 === $col_index ) {
					$align_class = 'hmpro-align-center';
				} elseif ( 2 === $col_index ) {
					$align_class = 'hmpro-align-right';
				}
			}

			echo '<div class="hmpro-builder-col hmpro-col-' . esc_attr( (string) $width ) . ' ' . esc_attr( $align_class ) . '" data-col="' . esc_attr( $col_id ) . '">';

			$components = isset( $col['components'] ) && is_array( $col['components'] ) ? $col['components'] : [];
			foreach ( $components as $comp ) {
				hmpro_builder_render_component( $comp, $context );
			}

			echo '</div>';
			$col_index++;
		}

		echo '</div>';
	}

	echo '</div>';
}

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
	hmpro_builder_render_layout_rows( $rows, $area );
}

function hmpro_builder_render_component( $comp, $context = 'header' ) {
	if ( ! is_array( $comp ) ) {
		return;
	}

	$type = isset( $comp['type'] ) ? sanitize_key( $comp['type'] ) : '';
	$id   = isset( $comp['id'] ) ? sanitize_key( $comp['id'] ) : '';
	$set  = isset( $comp['settings'] ) && is_array( $comp['settings'] ) ? $comp['settings'] : array();

	$classes = array( 'hmpro-builder-comp', 'hmpro-comp-' . $type );
	echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-comp="' . esc_attr( $id ) . '">';

	switch ( $type ) {
		case 'mega_column_menu':
			if ( 'mega' === $context ) {
				hmpro_builder_comp_mega_column_menu( $set );
			}
			break;
		case 'image':
			if ( 'mega' === $context ) {
				hmpro_builder_comp_image( $set );
			}
			break;
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
		case 'social':
			hmpro_builder_comp_social( $set );
			break;
		case 'social_icon_button':
			hmpro_builder_comp_social_icon_button( $set );
			break;
		default:
			do_action( 'hmpro/builder/render_component', $type, $set );
			break;
	}

	echo '</div>';
}

function hmpro_builder_comp_image( array $set ) {
	$url = isset( $set['url'] ) ? esc_url( (string) $set['url'] ) : '';
	if ( '' === $url ) {
		return;
	}
	$alt     = isset( $set['alt'] ) ? esc_attr( (string) $set['alt'] ) : '';
	$link    = isset( $set['link'] ) ? esc_url( (string) $set['link'] ) : '';
	$new_tab = ! empty( $set['new_tab'] );

	$img = '<img src="' . $url . '" alt="' . $alt . '" loading="lazy" />';
	if ( '' !== $link ) {
		$attrs = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
		$img   = '<a href="' . $link . '"' . $attrs . '>' . $img . '</a>';
	}
	echo '<div class="hmpro-mega-image">' . $img . '</div>';
}

function hmpro_builder_comp_mega_column_menu( array $set ) {
	$menu_id      = isset( $set['menu_id'] ) ? absint( $set['menu_id'] ) : 0;
	$root_item_id = isset( $set['root_item_id'] ) ? absint( $set['root_item_id'] ) : 0;
	$max_depth    = isset( $set['max_depth'] ) ? max( 1, min( 3, absint( $set['max_depth'] ) ) ) : 2;
	$show_root    = ! empty( $set['show_root_title'] );

	if ( $menu_id < 1 || $root_item_id < 1 ) {
		return;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( empty( $items ) || ! is_array( $items ) ) {
		return;
	}

	$by_parent  = [];
	$root_title = '';
	foreach ( $items as $it ) {
		$pid = absint( $it->menu_item_parent );
		$iid = absint( $it->ID );
		if ( $iid === $root_item_id ) {
			$root_title = (string) $it->title;
		}
		if ( ! isset( $by_parent[ $pid ] ) ) {
			$by_parent[ $pid ] = [];
		}
		$by_parent[ $pid ][] = $it;
	}

	$render_list = function ( $parent_id, $depth ) use ( &$render_list, $by_parent, $max_depth ) {
		if ( $depth > $max_depth ) {
			return;
		}
		if ( empty( $by_parent[ $parent_id ] ) ) {
			return;
		}
		echo '<ul class="hmpro-mega-col-list hmpro-depth-' . esc_attr( (string) $depth ) . '">';
		foreach ( $by_parent[ $parent_id ] as $it ) {
			$url   = ! empty( $it->url ) ? esc_url( (string) $it->url ) : '#';
			$title = esc_html( (string) $it->title );
			echo '<li class="hmpro-mega-col-item">';
			echo '<a class="hmpro-mega-col-link" href="' . $url . '">' . $title . '</a>';
			$render_list( absint( $it->ID ), $depth + 1 );
			echo '</li>';
		}
		echo '</ul>';
	};

	echo '<div class="hmpro-mega-column-menu" data-menu-id="' . esc_attr( (string) $menu_id ) . '" data-root-item="' . esc_attr( (string) $root_item_id ) . '">';
	if ( $show_root && '' !== $root_title ) {
		echo '<div class="hmpro-mega-root-title">' . esc_html( $root_title ) . '</div>';
	}
	$render_list( $root_item_id, 1 );
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
	$ph = isset( $set['placeholder'] ) ? sanitize_text_field( (string) $set['placeholder'] ) : __( 'Ara…', 'hmpro' );
	$comp_id  = sanitize_key( (string) $comp_id );
	$field_id = 'hmpro-search-field';
	if ( '' !== $comp_id ) {
		$field_id = 'hmpro-search-field-' . $comp_id;
	}
	echo '<form class="hmpro-search" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">';
	echo '<label class="screen-reader-text" for="' . esc_attr( $field_id ) . '">' . esc_html__( 'Search for:', 'hmpro' ) . '</label>';
	echo '<input id="' . esc_attr( $field_id ) . '" class="hmpro-search-field" type="search" name="s" value="' . esc_attr( get_search_query() ) . '" placeholder="' . esc_attr( $ph ) . '" required />';
	echo '<button class="hmpro-search-submit" type="submit">' . esc_html__( 'Ara', 'hmpro' ) . '</button>';
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

function hmpro_load_social_svg_preset( string $preset ): string {
	static $cache = array();

	$preset = sanitize_key( $preset );
	if ( isset( $cache[ $preset ] ) ) {
		return $cache[ $preset ];
	}

	$map = array(
		'facebook'  => 'facebook.svg',
		'x'         => 'x.svg',
		'twitter'   => 'x.svg',
		'instagram' => 'instagram.svg',
		'linkedin'  => 'linkedin.svg',
		'youtube'   => 'youtube.svg',
		'tiktok'    => 'tiktok.svg',
		'whatsapp'  => 'whatsapp.svg',
		'telegram'  => 'telegram.svg',
	);

	if ( empty( $map[ $preset ] ) ) {
		$cache[ $preset ] = '';
		return '';
	}

	$rel_candidates = array(
		'assets/icon/social/' . $map[ $preset ],
		'assets/icons/social/' . $map[ $preset ],
	);

	$path = '';
	foreach ( $rel_candidates as $rel ) {
		$p = trailingslashit( get_stylesheet_directory() ) . $rel;
		if ( file_exists( $p ) ) {
			$path = $p;
			break;
		}
	}

	if ( '' === $path ) {
		$cache[ $preset ] = '';
		return '';
	}

	$svg = (string) file_get_contents( $path );
	if ( '' === $svg ) {
		$cache[ $preset ] = '';
		return '';
	}

	$allowed = array(
		'svg'    => array(
			'xmlns'       => true,
			'viewBox'     => true,
			'aria-hidden' => true,
			'focusable'   => true,
			'role'        => true,
			'width'       => true,
			'height'      => true,
		),
		'path'   => array(
			'd'    => true,
			'fill' => true,
		),
		'g'      => array( 'fill' => true ),
		'circle' => array(
			'cx'   => true,
			'cy'   => true,
			'r'    => true,
			'fill' => true,
		),
		'rect'   => array(
			'x'      => true,
			'y'      => true,
			'width'  => true,
			'height' => true,
			'rx'     => true,
			'ry'     => true,
			'fill'   => true,
		),
	);

	$svg = wp_kses( $svg, $allowed );

	$cache[ $preset ] = $svg;
	return $svg;
}

/**
 * Social icon button component.
 */
function hmpro_builder_comp_social_icon_button( array $set ) {
	$url = isset( $set['url'] ) ? esc_url( (string) $set['url'] ) : '';
	if ( '' === $url ) {
		return;
	}

	$new_tab = ! empty( $set['new_tab'] );
	$icon_mode = isset( $set['icon_mode'] ) ? sanitize_key( (string) $set['icon_mode'] ) : 'preset';
	$icon_preset = isset( $set['icon_preset'] ) ? sanitize_key( (string) $set['icon_preset'] ) : 'facebook';
	$custom_icon = isset( $set['custom_icon'] ) ? (string) $set['custom_icon'] : '';
	$transparent = ! empty( $set['transparent'] );

	$allowed_presets = array( 'facebook', 'instagram', 'linkedin', 'x', 'twitter', 'youtube', 'tiktok', 'whatsapp', 'telegram' );
	if ( ! in_array( $icon_preset, $allowed_presets, true ) ) {
		$icon_preset = 'facebook';
	}

	$label_map = array(
		'facebook' => 'Facebook',
		'instagram' => 'Instagram',
		'linkedin' => 'LinkedIn',
		'x' => 'X',
		'twitter' => 'X',
		'youtube' => 'YouTube',
		'tiktok' => 'TikTok',
		'whatsapp' => 'WhatsApp',
		'telegram' => 'Telegram',
	);
	$label = isset( $label_map[ $icon_preset ] ) ? $label_map[ $icon_preset ] : __( 'Social link', 'hmpro' );

	$attrs = $new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
	$cls = 'hmpro-socialicon hmpro-socialicon--' . esc_attr( $icon_preset );
	if ( $transparent ) {
		$cls .= ' is-transparent';
	}
	echo '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $label ) . '"' . $attrs . '>';
	$icon_html = '';
	if ( 'custom' === $icon_mode && '' !== trim( $custom_icon ) ) {
		$allowed_svg_tags = array(
			'svg'      => array(
				'viewBox'    => true,
				'xmlns'      => true,
				'width'      => true,
				'height'     => true,
				'fill'       => true,
				'stroke'     => true,
				'aria-hidden'=> true,
				'role'       => true,
				'focusable'  => true,
			),
			'g'        => array( 'fill' => true, 'stroke' => true ),
			'path'     => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
			'circle'   => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
			'rect'     => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
			'line'     => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true ),
			'polyline' => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
			'polygon'  => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
			'use'      => array( 'href' => true, 'xlink:href' => true ),
		);
		$icon_html = wp_kses( $custom_icon, $allowed_svg_tags );
	} else {
		$icon_html = hmpro_load_social_svg_preset( $icon_preset );
	}

	if ( '' === $icon_html ) {
		$badge_map = array(
			'facebook'  => 'f',
			'instagram' => 'IG',
			'linkedin'  => 'in',
			'x'         => 'X',
			'twitter'   => 'X',
			'youtube'   => 'YT',
			'tiktok'    => 'TT',
			'whatsapp'  => 'WA',
			'telegram'  => 'TG',
		);
		$badge = isset( $badge_map[ $icon_preset ] ) ? $badge_map[ $icon_preset ] : strtoupper( substr( $icon_preset, 0, 2 ) );
		$icon_html = '<span class="hmpro-socialicon__badge">' . esc_html( $badge ) . '</span>';
	} else {
		$icon_html = '<span class="hmpro-socialicon__svg" aria-hidden="true">' . $icon_html . '</span>';
	}

	echo $icon_html;
	echo '</a>';
}


/**
 * Social Media Icons component (Commit 020)
 */
function hmpro_builder_comp_social( array $set ) {
	$urls    = isset( $set['urls'] ) && is_array( $set['urls'] ) ? $set['urls'] : array();
	$size    = isset( $set['size'] ) ? sanitize_key( (string) $set['size'] ) : 'normal';
	$gap     = isset( $set['gap'] ) ? sanitize_key( (string) $set['gap'] ) : 'normal';
	$new_tab = ! empty( $set['new_tab'] );

	$allowed_sizes = array( 'small', 'normal', 'large' );
	$allowed_gaps  = array( 'small', 'normal', 'large' );
	if ( ! in_array( $size, $allowed_sizes, true ) ) {
		$size = 'normal';
	}
	if ( ! in_array( $gap, $allowed_gaps, true ) ) {
		$gap = 'normal';
	}

	$map = array(
		'facebook'  => array( 'label' => 'Facebook', 'icon' => 'facebook' ),
		'instagram' => array( 'label' => 'Instagram', 'icon' => 'instagram' ),
		'x'         => array( 'label' => 'X', 'icon' => 'x' ),
		'youtube'   => array( 'label' => 'YouTube', 'icon' => 'youtube' ),
		'tiktok'    => array( 'label' => 'TikTok', 'icon' => 'tiktok' ),
		'linkedin'  => array( 'label' => 'LinkedIn', 'icon' => 'linkedin' ),
		'whatsapp'  => array( 'label' => 'WhatsApp', 'icon' => 'whatsapp' ),
		'telegram'  => array( 'label' => 'Telegram', 'icon' => 'telegram' ),
	);

	$items = array();
	foreach ( $map as $key => $meta ) {
		if ( empty( $urls[ $key ] ) ) {
			continue;
		}
		$url = esc_url( (string) $urls[ $key ] );
		if ( '' === $url ) {
			continue;
		}
		$items[] = array(
			'url'   => $url,
			'label' => $meta['label'],
			'icon'  => $meta['icon'],
		);
	}

	if ( empty( $items ) ) {
		return;
	}

	$classes = array( 'hmpro-social', 'hmpro-social--' . $size, 'hmpro-social--gap-' . $gap );
	echo '<nav class="' . esc_attr( implode( ' ', $classes ) ) . '" aria-label="' . esc_attr__( 'Social links', 'hmpro' ) . '">';

	foreach ( $items as $item ) {
		$attrs = '';
		if ( $new_tab ) {
			$attrs = ' target="_blank" rel="noopener noreferrer"';
		}
		echo '<a class="hmpro-social__link hmpro-social__' . esc_attr( $item['icon'] ) . '" href="' . esc_url( $item['url'] ) . '" aria-label="' . esc_attr( $item['label'] ) . '"' . $attrs . '>';
		echo '<svg class="hmpro-social__icon" aria-hidden="true" focusable="false"><use href="#hmpro-icon-' . esc_attr( $item['icon'] ) . '"></use></svg>';
		echo '<span class="screen-reader-text">' . esc_html( $item['label'] ) . '</span>';
		echo '</a>';
	}

	echo '</nav>';
}

/**
 * Outputs an inline SVG sprite (lightweight, no external dependencies).
 * For now: simple letter icons; can be replaced with full brand SVG paths later.
 */
function hmpro_builder_output_social_sprite() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	echo '<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true" focusable="false">';
	echo '<symbol id="hmpro-icon-facebook" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2"/><text x="12" y="16" text-anchor="middle" font-size="12" font-family="Arial" fill="currentColor">f</text></symbol>';
	echo '<symbol id="hmpro-icon-instagram" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2"/><text x="12" y="16" text-anchor="middle" font-size="11" font-family="Arial" fill="currentColor">i</text></symbol>';
	echo '<symbol id="hmpro-icon-x" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2"/><text x="12" y="16" text-anchor="middle" font-size="11" font-family="Arial" fill="currentColor">x</text></symbol>';
	echo '<symbol id="hmpro-icon-youtube" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2"/><text x="12" y="16" text-anchor="middle" font-size="11" font-family="Arial" fill="currentColor">></text></symbol>';
	echo '<symbol id="hmpro-icon-tiktok" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2"/><text x="12" y="16" text-anchor="middle" font-size="11" font-family="Arial" fill="currentColor">t</text></symbol>';
	echo '<symbol id="hmpro-icon-linkedin" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2"/><text x="12" y="16" text-anchor="middle" font-size="10" font-family="Arial" fill="currentColor">in</text></symbol>';
	echo '<symbol id="hmpro-icon-whatsapp" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2"/><text x="12" y="16" text-anchor="middle" font-size="10" font-family="Arial" fill="currentColor">w</text></symbol>';
	echo '<symbol id="hmpro-icon-telegram" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="2"/><text x="12" y="16" text-anchor="middle" font-size="10" font-family="Arial" fill="currentColor">tg</text></symbol>';
	echo '</svg>';
}
