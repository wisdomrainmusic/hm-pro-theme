<?php

function hmpro_render_transparent_header_hero() {
	if ( ! is_front_page() ) {
		return;
	}
	if ( ! (int) get_theme_mod( 'hmpro_transparent_header_home', 0 ) ) {
		return;
	}

	$image_id = absint( get_theme_mod( 'hmpro_th_hero_image', 0 ) );
	if ( ! $image_id ) {
		return;
	}

	$height  = absint( get_theme_mod( 'hmpro_th_hero_height', 520 ) );
	$overlay = absint( get_theme_mod( 'hmpro_th_hero_overlay', 30 ) );
	$title   = trim( (string) get_theme_mod( 'hmpro_th_hero_title', '' ) );
	$text    = trim( (string) get_theme_mod( 'hmpro_th_hero_text', '' ) );

	$url = wp_get_attachment_image_url( $image_id, 'full' );
	if ( ! $url ) {
		return;
	}

	echo '<section class="hmpro-th-hero" style="--hmpro-hero-h:' . esc_attr( $height ) . 'px; --hmpro-hero-overlay:' . esc_attr( $overlay / 100 ) . '; background-image:url(' . esc_url( $url ) . ');">';
	echo '<div class="hmpro-th-hero__overlay" aria-hidden="true"></div>';
	echo '<div class="hmpro-th-hero__inner">';
	if ( $title !== '' ) {
		echo '<h1 class="hmpro-th-hero__title">' . esc_html( $title ) . '</h1>';
	}
	if ( $text !== '' ) {
		echo '<p class="hmpro-th-hero__text">' . esc_html( $text ) . '</p>';
	}
	echo '</div>';
	echo '</section>';
}
