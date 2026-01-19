<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', function ( $wp_customize ) {
	// NOTE: UI-only settings; frontend wiring happens via body classes + CSS/JS.
	$wp_customize->add_setting( 'hmpro_logo_max_height', [
		'default'           => 56,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'hmpro_logo_max_height', [
		'label'       => __( 'Logo Max Height (px)', 'hm-pro-theme' ),
		'section'     => 'title_tagline',
		'type'        => 'range',
		'input_attrs' => [
			'min'  => 24,
			'max'  => 160,
			'step' => 1,
		],
	] );

	// Mobile logo sizing (separate control for mobile header)
	$wp_customize->add_setting( 'hmpro_mobile_logo_max_height', [
		'default'           => 64,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'hmpro_mobile_logo_max_height', [
		'label'       => __( 'Mobil Logo Max Height (px)', 'hm-pro-theme' ),
		'section'     => 'title_tagline',
		'type'        => 'range',
		'input_attrs' => [
			'min'  => 28,
			'max'  => 140,
			'step' => 1,
		],
	] );


	// Footer logo sizing (separate control for footer builder logo).
	$wp_customize->add_setting( 'hmpro_footer_logo_max_height', [
		'default'           => 96,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'hmpro_footer_logo_max_height', [
		'label'       => __( 'Footer Logo Max Height (px)', 'hm-pro-theme' ),
		'section'     => 'title_tagline',
		'type'        => 'range',
		'input_attrs' => [
			'min'  => 24,
			'max'  => 220,
			'step' => 1,
		],
	] );

	// Mega Menu v2 kill switch (canvas frontend).
	$wp_customize->add_setting( 'hmpro_enable_mega_menu_v2', [
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );

	$wp_customize->add_control( 'hmpro_enable_mega_menu_v2', [
		'label'       => __( 'Mega Menü v2 (Canvas Düzen) Etkinleştir', 'hm-pro-theme' ),
		'description' => __( 'Deneyseldir. Sadece Mega Menü v2 (Canvas) ön yüz çıktısını açar/kapatır. Header Builder yerleşimini veya mevcut menü yapınızı değiştirmez. Sorun yaşarsanız kapatıp kaydedin.', 'hm-pro-theme' ),
		'section' => 'title_tagline',
		'type'    => 'checkbox',
	] );

	// ----------------------------
	// Hero Banner (Image/Video)
	// ----------------------------
	$wp_customize->add_section( 'hmpro_header_transparent', [
		'title'       => __( 'Hero Banner (Görsel/Video)', 'hm-pro-theme' ),
		'description' => __( 'Ana sayfada üst kısımda hero banner alanı oluşturur. Elementor kullanmadan görsel veya video döngüsü gösterebilirsiniz.', 'hm-pro-theme' ),
		'priority'    => 35,
	] );

	$wp_customize->add_setting( 'hmpro_transparent_header_home', [
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_transparent_header_home', [
		'label'       => __( 'Ana sayfada Hero Banner aktif', 'hm-pro-theme' ),
		'description' => __( 'Hero banner alanını ana sayfada aktif eder. Görsel/video seçerseniz üst bölümde banner oluşur.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'hmpro_transparent_header_offset', [
		'default'           => 1,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_transparent_header_offset', [
		'label'       => __( 'İçerik boşluğu ekle (opsiyonel)', 'hm-pro-theme' ),
		'description' => __( 'Hero banner kullanırken içerik kaymasını istemezseniz kapatabilirsiniz. Açıkken sayfa başına üst boşluk ekler.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'hmpro_transparent_header_logo', [
		'default'           => '',
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hmpro_transparent_header_logo', [
		'label'       => __( 'Şeffaf mod logo (opsiyonel)', 'hm-pro-theme' ),
		'description' => __( 'Şeffaf modda farklı (örn. beyaz) logo göstermek isterseniz seçin. Boş bırakılırsa normal logo kullanılır.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'mime_type'   => 'image',
	] ) );

	// ----------------------------
	// Homepage Hero Banner (for transparent header)
	// ----------------------------
	$wp_customize->add_setting( 'hmpro_th_hero_image', [
		'default'           => '',
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hmpro_th_hero_image', [
		'label'       => __( 'Hero Banner görseli', 'hm-pro-theme' ),
		'description' => __( 'Ana sayfada üst kısımda gösterilecek banner görseli. Video seçmezseniz bu görsel kullanılır.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'mime_type'   => 'image',
	] ) );

	$wp_customize->add_setting( 'hmpro_th_hero_use_video', [
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_use_video', [
		'label'       => __( 'Hero Banner video kullan (opsiyonel)', 'hm-pro-theme' ),
		'description' => __( 'MP4/WebM seçerseniz arkaplanda otomatik döngü oynar (muted). Video yoksa görsele düşer.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_video', [
		'default'           => '',
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hmpro_th_hero_video', [
		'label'       => __( 'Hero Banner video dosyası (mp4/webm)', 'hm-pro-theme' ),
		'description' => __( 'Örnek: şömine, şelale, manzara döngüsü. Kısa ve optimize video önerilir.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'mime_type'   => 'video',
	] ) );

	$wp_customize->add_setting( 'hmpro_th_hero_height', [
		'default'           => 520,
		'sanitize_callback' => function ( $v ) {
			$v = absint( $v );
			if ( $v < 240 ) {
				$v = 240;
			}
			if ( $v > 1200 ) {
				$v = 1200;
			}
			return $v;
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_height', [
		'label'       => __( 'Hero yüksekliği (px)', 'hm-pro-theme' ),
		'description' => __( 'Öneri: 520–820 arası. Büyük logo kullanıyorsanız biraz artırın.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 240, 'max' => 1200, 'step' => 10 ],
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_overlay', [
		'default'           => 30,
		'sanitize_callback' => function ( $v ) {
			$v = absint( $v );
			if ( $v < 0 ) {
				$v = 0;
			}
			if ( $v > 80 ) {
				$v = 80;
			}
			return $v;
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_overlay', [
		'label'       => __( 'Overlay karartma (%)', 'hm-pro-theme' ),
		'description' => __( 'Metin okunurluğu için arka plana karartma ekler.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 0, 'max' => 80, 'step' => 5 ],
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_title', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_title', [
		'label'   => __( 'Hero başlık (opsiyonel)', 'hm-pro-theme' ),
		'section' => 'hmpro_header_transparent',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_text', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_textarea_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_text', [
		'label'   => __( 'Hero açıklama (opsiyonel)', 'hm-pro-theme' ),
		'section' => 'hmpro_header_transparent',
		'type'    => 'textarea',
	] );

} );
