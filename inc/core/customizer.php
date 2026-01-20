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

	// -------------------------------------------------------
	// Hero Banner (Homepage)
	// -------------------------------------------------------
	$wp_customize->add_section( 'hmpro_hero_section', [
		'title'    => __( 'Hero Banner', 'hmpro-theme' ),
		'priority' => 35,
	] );

	$wp_customize->add_setting( 'hmpro_hero_enable', [
		'default'           => 0,
		'transport'         => 'refresh',
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( 'hmpro_hero_enable', [
		'type'        => 'checkbox',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Ana sayfada Hero Banner kullan', 'hmpro-theme' ),
		'description' => __( 'Sadece ana sayfada çalışır. Elementor kullanmadan hızlı bir hero alanı oluşturur.', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_image', [
		'default'           => 0,
		'transport'         => 'refresh',
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hmpro_hero_image', [
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero görseli', 'hmpro-theme' ),
		'mime_type'   => 'image',
		'description' => __( 'Video seçmezseniz görsel kullanılır.', 'hmpro-theme' ),
	] ) );

	$wp_customize->add_setting( 'hmpro_hero_video_enable', [
		'default'           => 0,
		'transport'         => 'refresh',
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( 'hmpro_hero_video_enable', [
		'type'        => 'checkbox',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero arkaplanda video kullan (opsiyonel)', 'hmpro-theme' ),
		'description' => __( 'MP4/WebM seçerseniz arkaplanda otomatik döngü oynar. Mobilde sessiz (muted) oynatır. Video yoksa görsele düşer.', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_video', [
		'default'           => 0,
		'transport'         => 'refresh',
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hmpro_hero_video', [
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero video dosyası (mp4/webm)', 'hmpro-theme' ),
		'mime_type'   => 'video',
		'description' => __( 'Örnek: şömine, şelale, manzara döngüsü. Kısa ve optimize video önerilir.', 'hmpro-theme' ),
	] ) );

	$wp_customize->add_setting( 'hmpro_hero_height', [
		'default'           => 520,
		'transport'         => 'refresh',
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( 'hmpro_hero_height', [
		'type'        => 'number',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero yüksekliği (px)', 'hmpro-theme' ),
		'description' => __( 'Öneri: 520–820 arası. Büyük logo kullanıyorsanız biraz artırın.', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_overlay', [
		'default'           => 30,
		'transport'         => 'refresh',
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( 'hmpro_hero_overlay', [
		'type'        => 'number',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Overlay karartma (%)', 'hmpro-theme' ),
		'description' => __( 'Metin okunurluğu için arka planı karartır.', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_title', [
		'default'           => '',
		'transport'         => 'refresh',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'hmpro_hero_title', [
		'type'    => 'text',
		'section' => 'hmpro_hero_section',
		'label'   => __( 'Hero başlık (opsiyonel)', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_text', [
		'default'           => '',
		'transport'         => 'refresh',
		'sanitize_callback' => 'wp_kses_post',
	] );
	$wp_customize->add_control( 'hmpro_hero_text', [
		'type'    => 'textarea',
		'section' => 'hmpro_hero_section',
		'label'   => __( 'Hero açıklama (opsiyonel)', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_btn_text', [
		'default'           => '',
		'transport'         => 'refresh',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'hmpro_hero_btn_text', [
		'type'        => 'text',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero buton metni (opsiyonel)', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_btn_url', [
		'default'           => '',
		'transport'         => 'refresh',
		'sanitize_callback' => 'esc_url_raw',
	] );
	$wp_customize->add_control( 'hmpro_hero_btn_url', [
		'type'        => 'url',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero buton linki (opsiyonel)', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_title_color', [
		'default'           => '#ffffff',
		'transport'         => 'refresh',
		'sanitize_callback' => 'sanitize_hex_color',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hmpro_hero_title_color', [
		'section' => 'hmpro_hero_section',
		'label'   => __( 'Hero başlık rengi', 'hmpro-theme' ),
	] ) );

	$wp_customize->add_setting( 'hmpro_hero_text_color', [
		'default'           => '#ffffff',
		'transport'         => 'refresh',
		'sanitize_callback' => 'sanitize_hex_color',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hmpro_hero_text_color', [
		'section' => 'hmpro_hero_section',
		'label'   => __( 'Hero açıklama rengi', 'hmpro-theme' ),
	] ) );

	$wp_customize->add_setting( 'hmpro_hero_btn_bg', [
		'default'           => '#ffffff',
		'transport'         => 'refresh',
		'sanitize_callback' => 'sanitize_hex_color',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hmpro_hero_btn_bg', [
		'section' => 'hmpro_hero_section',
		'label'   => __( 'Hero buton arka plan rengi', 'hmpro-theme' ),
	] ) );

	$wp_customize->add_setting( 'hmpro_hero_btn_color', [
		'default'           => '#111111',
		'transport'         => 'refresh',
		'sanitize_callback' => 'sanitize_hex_color',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hmpro_hero_btn_color', [
		'section' => 'hmpro_hero_section',
		'label'   => __( 'Hero buton yazı rengi', 'hmpro-theme' ),
	] ) );

	$wp_customize->add_setting( 'hmpro_hero_title_size', [
		'default'           => 48,
		'transport'         => 'refresh',
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( 'hmpro_hero_title_size', [
		'type'    => 'number',
		'section' => 'hmpro_hero_section',
		'label'   => __( 'Hero başlık font boyutu (px)', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_text_size', [
		'default'           => 18,
		'transport'         => 'refresh',
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( 'hmpro_hero_text_size', [
		'type'    => 'number',
		'section' => 'hmpro_hero_section',
		'label'   => __( 'Hero açıklama font boyutu (px)', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_font_family', [
		'default'           => 'inherit',
		'transport'         => 'refresh',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'hmpro_hero_font_family', [
		'type'        => 'text',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero font-family (opsiyonel)', 'hmpro-theme' ),
		'description' => __( 'Örnek: inherit, Arial, "Poppins", "Inter", serif', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_scale', [
		'default'           => 1,
		'transport'         => 'refresh',
		'sanitize_callback' => function ( $v ) { return (float) $v; },
	] );
	$wp_customize->add_control( 'hmpro_hero_scale', [
		'type'        => 'number',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero içerik grubu ölçek (büyüt/küçült)', 'hmpro-theme' ),
		'description' => __( 'Başlık + açıklama + buton birlikte büyür/küçülür.', 'hmpro-theme' ),
		'input_attrs' => [ 'step' => '0.05', 'min' => '0.5', 'max' => '2.0' ],
	] );

	$wp_customize->add_setting( 'hmpro_hero_x', [
		'default'           => 0,
		'transport'         => 'refresh',
		'sanitize_callback' => 'intval',
	] );
	$wp_customize->add_control( 'hmpro_hero_x', [
		'type'        => 'number',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero içerik X kaydırma (px)', 'hmpro-theme' ),
		'description' => __( 'Sağa (+) / sola (-) kaydırır.', 'hmpro-theme' ),
	] );

	$wp_customize->add_setting( 'hmpro_hero_y', [
		'default'           => 0,
		'transport'         => 'refresh',
		'sanitize_callback' => 'intval',
	] );
	$wp_customize->add_control( 'hmpro_hero_y', [
		'type'        => 'number',
		'section'     => 'hmpro_hero_section',
		'label'       => __( 'Hero içerik Y kaydırma (px)', 'hmpro-theme' ),
		'description' => __( 'Aşağı (+) / yukarı (-) kaydırır.', 'hmpro-theme' ),
	] );

	// (İstersen ileride “şeffaf mod logo” gibi ayrı logo seçimini tekrar ekleyebiliriz.)

} );
