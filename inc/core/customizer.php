<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize media control values.
 *
 * WP_Customize_Media_Control usually stores an attachment ID, but in some edge cases
 * (migrations, older exports, or manual input) a URL may be passed.
 *
 * We accept either:
 * - a positive integer attachment ID
 * - a safe URL string (will be normalized + stored as URL)
 */
function hmpro_sanitize_media_id_or_url( $value ) {
	if ( is_numeric( $value ) ) {
		return absint( $value );
	}

	$value = trim( (string) $value );
	if ( $value === '' ) {
		return '';
	}

	// Try to map URL back to an attachment ID when possible.
	$maybe_id = attachment_url_to_postid( $value );
	if ( $maybe_id ) {
		return absint( $maybe_id );
	}

	return esc_url_raw( $value );
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
		'sanitize_callback' => 'hmpro_sanitize_media_id_or_url',
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
		'sanitize_callback' => 'hmpro_sanitize_media_id_or_url',
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
		'sanitize_callback' => 'hmpro_sanitize_media_id_or_url',
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
			// Allow compact hero banners (e.g., small logo demos).
			// Guard very small values that would break layout.
			if ( $v < 50 ) {
				$v = 50;
			}
			if ( $v > 1400 ) {
				$v = 1400;
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
		'input_attrs' => [ 'min' => 50, 'max' => 1400, 'step' => 10 ],
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

	// ----------------------------
	// Hero Button (CTA)
	// ----------------------------
	$wp_customize->add_setting( 'hmpro_th_hero_btn_text', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_btn_text', [
		'label'   => __( 'Hero buton metni (opsiyonel)', 'hm-pro-theme' ),
		'section' => 'hmpro_header_transparent',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_btn_url', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_btn_url', [
		'label'   => __( 'Hero buton linki (URL)', 'hm-pro-theme' ),
		'section' => 'hmpro_header_transparent',
		'type'    => 'url',
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_btn_newtab', [
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_btn_newtab', [
		'label'   => __( 'Butonu yeni sekmede aç', 'hm-pro-theme' ),
		'section' => 'hmpro_header_transparent',
		'type'    => 'checkbox',
	] );

	// ----------------------------
	// Hero Typography & Colors (theme-independent)
	// ----------------------------
	$wp_customize->add_setting( 'hmpro_th_hero_title_color', [
		'default'           => '#ffffff',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hmpro_th_hero_title_color', [
		'label'   => __( 'Hero başlık rengi', 'hm-pro-theme' ),
		'section' => 'hmpro_header_transparent',
	] ) );

	$wp_customize->add_setting( 'hmpro_th_hero_text_color', [
		'default'           => '#ffffff',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hmpro_th_hero_text_color', [
		'label'   => __( 'Hero açıklama rengi', 'hm-pro-theme' ),
		'section' => 'hmpro_header_transparent',
	] ) );

	$wp_customize->add_setting( 'hmpro_th_hero_btn_bg', [
		'default'           => '#d4af37',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hmpro_th_hero_btn_bg', [
		'label'   => __( 'Hero buton arka plan rengi', 'hm-pro-theme' ),
		'section' => 'hmpro_header_transparent',
	] ) );

	$wp_customize->add_setting( 'hmpro_th_hero_btn_color', [
		'default'           => '#111111',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hmpro_th_hero_btn_color', [
		'label'   => __( 'Hero buton yazı rengi', 'hm-pro-theme' ),
		'section' => 'hmpro_header_transparent',
	] ) );

	$wp_customize->add_setting( 'hmpro_th_hero_title_size', [
		'default'           => 44,
		'sanitize_callback' => function ( $v ) {
			$v = absint( $v );
			if ( $v < 18 ) {
				$v = 18;
			}
			if ( $v > 96 ) {
				$v = 96;
			}
			return $v;
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_title_size', [
		'label'       => __( 'Hero başlık font boyutu (px)', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 18, 'max' => 96, 'step' => 1 ],
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_text_size', [
		'default'           => 18,
		'sanitize_callback' => function ( $v ) {
			$v = absint( $v );
			if ( $v < 12 ) {
				$v = 12;
			}
			if ( $v > 40 ) {
				$v = 40;
			}
			return $v;
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_text_size', [
		'label'       => __( 'Hero açıklama font boyutu (px)', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 12, 'max' => 40, 'step' => 1 ],
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_font_family', [
		'default'           => 'inherit',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_font_family', [
		'label'       => __( 'Hero yazı tipi (Font Family)', 'hm-pro-theme' ),
		'description' => __( 'Hazır seçeneklerden seçebilirsiniz. Özel bir font-family yazmak isterseniz “Tema varsayılanı” seçip aşağıdaki alana manuel girebilirsiniz.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'select',
		'choices'     => [
			'inherit'                      => __( 'Tema varsayılanı (inherit)', 'hm-pro-theme' ),
			'Inter, sans-serif'            => __( 'Inter', 'hm-pro-theme' ),
			'Poppins, sans-serif'          => __( 'Poppins', 'hm-pro-theme' ),
			'Lato, sans-serif'             => __( 'Lato', 'hm-pro-theme' ),
			'"Playfair Display", serif'   => __( 'Playfair Display', 'hm-pro-theme' ),
			'"Dancing Script", cursive'   => __( 'Dancing Script', 'hm-pro-theme' ),
			'serif'                        => __( 'Serif (genel)', 'hm-pro-theme' ),
			'sans-serif'                   => __( 'Sans-serif (genel)', 'hm-pro-theme' ),
		],
	] );

	// ----------------------------
	// Hero Content Group Transform (Scale + Move)
	// ----------------------------
	$wp_customize->add_setting( 'hmpro_th_hero_group_scale', [
		'default'           => 1,
		'sanitize_callback' => function ( $v ) {
			$v = (float) $v;
			if ( $v < 0.5 ) {
				$v = 0.5;
			}
			if ( $v > 2.0 ) {
				$v = 2.0;
			}
			return $v;
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_group_scale', [
		'label'       => __( 'Hero içerik grubu ölçek (büyüt/küçült)', 'hm-pro-theme' ),
		'description' => __( 'Başlık + açıklama + buton birlikte büyür/küçülür.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 0.5, 'max' => 2.0, 'step' => 0.05 ],
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_group_x', [
		'default'           => 0,
		'sanitize_callback' => function ( $v ) {
			// Be strict: Customizer values may arrive as strings; also tolerate comma decimals.
			if ( is_array( $v ) || is_object( $v ) ) {
				return 0;
			}
			$v = (string) $v;
			$v = trim( $v );
			if ( $v === '' ) {
				return 0;
			}
			// Convert Turkish decimal comma to dot and then cast.
			$v = str_replace( ',', '.', $v );
			$int = (int) round( (float) $v );
			if ( $int < -600 ) {
				$int = -600;
			}
			if ( $int > 600 ) {
				$int = 600;
			}
			return $int;
		},
		// postMessage gives instant preview and avoids edge cases where refresh doesn't reflect the value.
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_group_x', [
		'label'       => __( 'Hero içerik X kaydırma (px)', 'hm-pro-theme' ),
		'description' => __( 'Sağa (+) / sola (-) kaydırır.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'number',
		'input_attrs' => [ 'min' => -600, 'max' => 600, 'step' => 5 ],
	] );

	$wp_customize->add_setting( 'hmpro_th_hero_group_y', [
		'default'           => 0,
		'sanitize_callback' => function ( $v ) {
			// Be strict: Customizer values may arrive as strings; also tolerate comma decimals.
			if ( is_array( $v ) || is_object( $v ) ) {
				return 0;
			}
			$v = (string) $v;
			$v = trim( $v );
			if ( $v === '' ) {
				return 0;
			}
			$v = str_replace( ',', '.', $v );
			$int = (int) round( (float) $v );
			if ( $int < -600 ) {
				$int = -600;
			}
			if ( $int > 600 ) {
				$int = 600;
			}
			return $int;
		},
		// postMessage gives instant preview and avoids edge cases where refresh doesn't reflect the value.
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'hmpro_th_hero_group_y', [
		'label'       => __( 'Hero içerik Y kaydırma (px)', 'hm-pro-theme' ),
		'description' => __( 'Aşağı (+) / yukarı (-) kaydırır.', 'hm-pro-theme' ),
		'section'     => 'hmpro_header_transparent',
		'type'        => 'number',
		'input_attrs' => [ 'min' => -600, 'max' => 600, 'step' => 5 ],
	] );

} );

// Live preview for hero group X/Y offsets (postMessage).
add_action( 'customize_preview_init', function () {
	$src = get_template_directory_uri() . '/assets/js/customizer-preview.js';
	$path = get_template_directory() . '/assets/js/customizer-preview.js';
	$ver = file_exists( $path ) ? (string) filemtime( $path ) : null;
	wp_enqueue_script( 'hmpro-customizer-preview', $src, [ 'customize-preview' ], $ver, true );
} );
