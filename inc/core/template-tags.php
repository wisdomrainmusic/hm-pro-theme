<?php

function hmpro_th_render_homepage_hero() {
	if ( ! is_front_page() ) {
		return;
	}

	$enabled = get_theme_mod( 'hmpro_hero_enable', 0 );
	if ( ! $enabled ) {
		return;
	}

	$image_id = absint( get_theme_mod( 'hmpro_hero_image', 0 ) );
	$video_on = absint( get_theme_mod( 'hmpro_hero_video_enable', 0 ) );
	$video_id = absint( get_theme_mod( 'hmpro_hero_video', 0 ) );

	$height   = max( 120, absint( get_theme_mod( 'hmpro_hero_height', 520 ) ) );
	$overlay  = min( 90, max( 0, absint( get_theme_mod( 'hmpro_hero_overlay', 30 ) ) ) );
	$title    = trim( (string) get_theme_mod( 'hmpro_hero_title', '' ) );
	$text     = (string) get_theme_mod( 'hmpro_hero_text', '' );
	$btn_text = trim( (string) get_theme_mod( 'hmpro_hero_btn_text', '' ) );
	$btn_url  = trim( (string) get_theme_mod( 'hmpro_hero_btn_url', '' ) );

	$title_color = sanitize_hex_color( get_theme_mod( 'hmpro_hero_title_color', '#ffffff' ) ) ?: '#ffffff';
	$text_color  = sanitize_hex_color( get_theme_mod( 'hmpro_hero_text_color', '#ffffff' ) ) ?: '#ffffff';
	$btn_bg      = sanitize_hex_color( get_theme_mod( 'hmpro_hero_btn_bg', '#ffffff' ) ) ?: '#ffffff';
	$btn_color   = sanitize_hex_color( get_theme_mod( 'hmpro_hero_btn_color', '#111111' ) ) ?: '#111111';

	$title_size  = max( 12, absint( get_theme_mod( 'hmpro_hero_title_size', 48 ) ) );
	$text_size   = max( 10, absint( get_theme_mod( 'hmpro_hero_text_size', 18 ) ) );
	$font_family = sanitize_text_field( get_theme_mod( 'hmpro_hero_font_family', 'inherit' ) );
	if ( $font_family === '' ) {
		$font_family = 'inherit';
	}

	$scale = (float) get_theme_mod( 'hmpro_hero_scale', 1 );
	if ( $scale < 0.5 ) {
		$scale = 0.5;
	}
	if ( $scale > 2.0 ) {
		$scale = 2.0;
	}

	$x = intval( get_theme_mod( 'hmpro_hero_x', 0 ) );
	$y = intval( get_theme_mod( 'hmpro_hero_y', 0 ) );

	if ( ! $image_id && ! $video_id ) {
		return;
	}

	$style = implode( ';', [
		'--hmpro-hero-h:' . $height . 'px',
		'--hmpro-hero-overlay:' . ( $overlay / 100 ),
		'--hmpro-hero-title-color:' . $title_color,
		'--hmpro-hero-text-color:' . $text_color,
		'--hmpro-hero-btn-bg:' . $btn_bg,
		'--hmpro-hero-btn-color:' . $btn_color,
		'--hmpro-hero-title-size:' . $title_size . 'px',
		'--hmpro-hero-text-size:' . $text_size . 'px',
		'--hmpro-hero-font:' . $font_family,
		'--hmpro-hero-scale:' . $scale,
		'--hmpro-hero-x:' . $x . 'px',
		'--hmpro-hero-y:' . $y . 'px',
	] ) . ';';

	$bg = '';
	if ( $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( $url ) {
			$bg = 'background-image:url(' . esc_url( $url ) . ');';
		}
	}

	?>
	<section class="hmpro-th-hero" style="<?php echo esc_attr( $style . $bg ); ?>">
		<?php if ( $video_on && $video_id ) :
			$video_url = wp_get_attachment_url( $video_id );
			if ( $video_url ) : ?>
				<video class="hmpro-th-hero__video" autoplay muted loop playsinline>
					<source src="<?php echo esc_url( $video_url ); ?>">
				</video>
			<?php endif;
		endif; ?>
		<div class="hmpro-th-hero__overlay"></div>
		<div class="hmpro-th-hero__inner">
			<div class="hmpro-th-hero__content">
				<?php if ( $title ) : ?>
					<h1 class="hmpro-th-hero__title"><?php echo esc_html( $title ); ?></h1>
				<?php endif; ?>
				<?php if ( $text ) : ?>
					<div class="hmpro-th-hero__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
				<?php endif; ?>
				<?php if ( $btn_text && $btn_url ) : ?>
					<p class="hmpro-th-hero__actions">
						<a class="hmpro-th-hero__btn" href="<?php echo esc_url( $btn_url ); ?>">
							<?php echo esc_html( $btn_text ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}
