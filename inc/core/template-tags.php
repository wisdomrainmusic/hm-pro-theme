<?php

// Renders ONLY the hero media layer (no inner content). Used behind header.
function hmpro_render_th_hero_media_layer() {
	if ( ! is_front_page() ) {
		return;
	}
	if ( ! (int) get_theme_mod( 'hmpro_transparent_header_home', 0 ) ) {
		return;
	}

	$image_id  = absint( get_theme_mod( 'hmpro_th_hero_image', 0 ) );
	$use_video = (int) get_theme_mod( 'hmpro_th_hero_use_video', 0 ) === 1;
	$video_id  = absint( get_theme_mod( 'hmpro_th_hero_video', 0 ) );
	$height    = absint( get_theme_mod( 'hmpro_th_hero_height', 520 ) );
	$overlay   = absint( get_theme_mod( 'hmpro_th_hero_overlay', 30 ) );

	$img_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
	$vid_url = ( $use_video && $video_id ) ? wp_get_attachment_url( $video_id ) : '';

	// Require at least one media source (video or image)
	if ( ! $vid_url && ! $img_url ) {
		return;
	}

	$style = '--hmpro-hero-h:' . $height . 'px; --hmpro-hero-overlay:' . ( $overlay / 100 ) . ';';
	if ( ! $vid_url && $img_url ) {
		$style .= ' background-image:url(' . esc_url( $img_url ) . ');';
	}

	echo '<div class="hmpro-th-hero-layer" style="' . esc_attr( $style ) . '">';
	if ( $vid_url ) {
		$poster = $img_url ? $img_url : '';
		echo '<video class="hmpro-th-hero__video" autoplay muted loop playsinline preload="metadata" ' . ( $poster ? 'poster="' . esc_url( $poster ) . '"' : '' ) . '>';
		echo '<source src="' . esc_url( $vid_url ) . '">';
		echo '</video>';
	}
	echo '<div class="hmpro-th-hero__overlay" aria-hidden="true"></div>';
	echo '</div>';
}

function hmpro_render_transparent_header_hero() {
	if ( ! is_front_page() ) {
		return;
	}
	if ( ! (int) get_theme_mod( 'hmpro_transparent_header_home', 0 ) ) {
		return;
	}

	$image_id  = absint( get_theme_mod( 'hmpro_th_hero_image', 0 ) );
	$use_video = (int) get_theme_mod( 'hmpro_th_hero_use_video', 0 ) === 1;
	$video_id  = absint( get_theme_mod( 'hmpro_th_hero_video', 0 ) );

	$height  = absint( get_theme_mod( 'hmpro_th_hero_height', 520 ) );
	$overlay = absint( get_theme_mod( 'hmpro_th_hero_overlay', 30 ) );
	$title   = trim( (string) get_theme_mod( 'hmpro_th_hero_title', '' ) );
	$text    = trim( (string) get_theme_mod( 'hmpro_th_hero_text', '' ) );

	$img_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
	$vid_url = ( $use_video && $video_id ) ? wp_get_attachment_url( $video_id ) : '';

	// Require at least one media source (video or image)
	if ( ! $vid_url && ! $img_url ) {
		return;
	}

	// This section is now content-only; media is injected behind header.
	$style = '--hmpro-hero-h:' . $height . 'px; --hmpro-hero-overlay:' . ( $overlay / 100 ) . ';';
	if ( ! $vid_url && $img_url ) {
		$style .= ' background-image:url(' . esc_url( $img_url ) . ');';
	}

	echo '<section class="hmpro-th-hero" style="' . esc_attr( $style ) . '">';
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
