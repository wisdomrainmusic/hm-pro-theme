<?php

function hmpro_render_transparent_header_hero() {
	if ( ! is_front_page() ) {
		return;
	}
	if ( ! (int) get_theme_mod( 'hmpro_transparent_header_home', 0 ) ) {
		return;
	}

	$hero_image_raw = get_theme_mod( 'hmpro_th_hero_image', 0 );
	$image_id        = is_numeric( $hero_image_raw ) ? absint( $hero_image_raw ) : 0;
	$image_url_raw   = ( ! $image_id && is_string( $hero_image_raw ) ) ? trim( $hero_image_raw ) : '';
	$use_video = (int) get_theme_mod( 'hmpro_th_hero_use_video', 0 ) === 1;
	$hero_video_raw = get_theme_mod( 'hmpro_th_hero_video', 0 );
	$video_id        = is_numeric( $hero_video_raw ) ? absint( $hero_video_raw ) : 0;
	$video_url_raw   = ( ! $video_id && is_string( $hero_video_raw ) ) ? trim( $hero_video_raw ) : '';

	$height  = absint( get_theme_mod( 'hmpro_th_hero_height', 520 ) );
	$overlay = absint( get_theme_mod( 'hmpro_th_hero_overlay', 30 ) );
	$title   = trim( (string) get_theme_mod( 'hmpro_th_hero_title', '' ) );
	$text    = trim( (string) get_theme_mod( 'hmpro_th_hero_text', '' ) );

	$btn_text = trim( (string) get_theme_mod( 'hmpro_th_hero_btn_text', '' ) );
	$btn_url  = trim( (string) get_theme_mod( 'hmpro_th_hero_btn_url', '' ) );
	$btn_new  = (int) get_theme_mod( 'hmpro_th_hero_btn_newtab', 0 ) === 1;

	// Typography / style.
	$title_color = trim( (string) get_theme_mod( 'hmpro_th_hero_title_color', '#ffffff' ) );
	$text_color  = trim( (string) get_theme_mod( 'hmpro_th_hero_text_color', '#ffffff' ) );
	$btn_bg      = trim( (string) get_theme_mod( 'hmpro_th_hero_btn_bg', '#d4af37' ) );
	$btn_color   = trim( (string) get_theme_mod( 'hmpro_th_hero_btn_color', '#111111' ) );
	$font_family = trim( (string) get_theme_mod( 'hmpro_th_hero_font_family', 'inherit' ) );

	$title_size = absint( get_theme_mod( 'hmpro_th_hero_title_size', 44 ) );
	$text_size  = absint( get_theme_mod( 'hmpro_th_hero_text_size', 18 ) );

	// Group transform (scale + move).
	$scale = (float) get_theme_mod( 'hmpro_th_hero_group_scale', 1 );
	if ( $scale < 0.5 ) {
		$scale = 0.5;
	}
	if ( $scale > 2.0 ) {
		$scale = 2.0;
	}
	$offset_x = (int) get_theme_mod( 'hmpro_th_hero_group_x', 0 );
	$offset_y = (int) get_theme_mod( 'hmpro_th_hero_group_y', 0 );

	$img_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
	if ( ! $img_url && $image_url_raw !== '' ) {
		$img_url = esc_url_raw( $image_url_raw );
	}
	$vid_url = ( $use_video && $video_id ) ? wp_get_attachment_url( $video_id ) : '';
	if ( ! $vid_url && $use_video && $video_url_raw !== '' ) {
		$vid_url = esc_url_raw( $video_url_raw );
	}

	// Require at least one media source (video or image)
	if ( ! $vid_url && ! $img_url ) {
		return;
	}

	// Hero font logic:
	// - Default: inherit from active preset (heading for title, body for text)
	// - Override: if user picks a specific font-family, apply to both title and text
	$hero_title_font = 'var(--hm-heading-font)';
	$hero_text_font  = 'var(--hm-body-font)';
	if ( $font_family !== '' && $font_family !== 'inherit' ) {
		$hero_title_font = $font_family;
		$hero_text_font  = $font_family;
	}

	$style  = '--hmpro-hero-h:' . $height . 'px;';
	$style .= ' --hmpro-hero-overlay:' . ( $overlay / 100 ) . ';';
	$style .= ' --hmpro-hero-title-color:' . $title_color . ';';
	$style .= ' --hmpro-hero-text-color:' . $text_color . ';';
	$style .= ' --hmpro-hero-btn-bg:' . $btn_bg . ';';
	$style .= ' --hmpro-hero-btn-color:' . $btn_color . ';';
	$style .= ' --hmpro-hero-title-font:' . $hero_title_font . ';';
	$style .= ' --hmpro-hero-text-font:' . $hero_text_font . ';';
	$style .= ' --hmpro-hero-title-size:' . $title_size . 'px;';
	$style .= ' --hmpro-hero-text-size:' . $text_size . 'px;';
	$style .= ' --hmpro-hero-group-scale:' . $scale . ';';
	$style .= ' --hmpro-hero-group-x:' . $offset_x . 'px;';
	$style .= ' --hmpro-hero-group-y:' . $offset_y . 'px;';
	if ( ! $vid_url && $img_url ) {
		$style .= ' background-image:url(' . esc_url( $img_url ) . ');';
	}

	echo '<section class="hmpro-th-hero" style="' . esc_attr( $style ) . '">';
	if ( $vid_url ) {
		echo '<video class="hmpro-th-hero__video" autoplay muted loop playsinline>';
		echo '<source src="' . esc_url( $vid_url ) . '">';
		echo '</video>';
	}
	echo '<div class="hmpro-th-hero__overlay" aria-hidden="true"></div>';
	echo '<div class="hmpro-th-hero__inner">';
	echo '<div class="hmpro-th-hero__content">';
	if ( $title !== '' ) {
		echo '<h1 class="hmpro-th-hero__title">' . esc_html( $title ) . '</h1>';
	}
	if ( $text !== '' ) {
		echo '<p class="hmpro-th-hero__text">' . esc_html( $text ) . '</p>';
	}
	if ( $btn_text !== '' && $btn_url !== '' ) {
		$target = $btn_new ? ' target="_blank" rel="noopener noreferrer"' : '';
		echo '<a class="hmpro-th-hero__btn" href="' . esc_url( $btn_url ) . '"' . $target . '>' . esc_html( $btn_text ) . '</a>';
	}
	echo '</div>';
	echo '</div>';
	echo '</section>';
}
