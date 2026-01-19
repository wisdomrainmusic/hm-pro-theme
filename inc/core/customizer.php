<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', function ( $wp_customize ) {
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

} );
