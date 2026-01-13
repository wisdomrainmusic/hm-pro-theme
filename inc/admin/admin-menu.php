<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'hmpro_register_admin_menu' );

function hmpro_register_admin_menu() {

	add_menu_page(
		__( 'HM Pro Theme', 'hmpro' ),
		'HM Pro Theme',
		'manage_options',
		'hmpro-theme',
		'hmpro_theme_dashboard_page_render',
		'dashicons-admin-customizer',
		59
	);

	add_submenu_page(
		'hmpro-theme',
		__( 'Dashboard', 'hmpro' ),
		__( 'Dashboard', 'hmpro' ),
		'manage_options',
		'hmpro-theme',
		'hmpro_theme_dashboard_page_render'
	);

	add_submenu_page(
		'hmpro-theme',
		__( 'Presets', 'hmpro' ),
		__( 'Presets', 'hmpro' ),
		'manage_options',
		'hmpro-presets',
		'hmpro_render_presets_page'
	);

	add_submenu_page(
		'hmpro-theme',
		__( 'Header Builder', 'hmpro' ),
		__( 'Header Builder', 'hmpro' ),
		'manage_options',
		'hmpro-header-builder',
		'hmpro_render_header_builder_page'
	);

	add_submenu_page(
		'hmpro-theme',
		__( 'Footer Builder', 'hmpro' ),
		__( 'Footer Builder', 'hmpro' ),
		'manage_options',
		'hmpro-footer-builder',
		'hmpro_render_footer_builder_page'
	);

	// Hidden page for editing presets (not shown in menu).
	add_submenu_page(
		null,
		__( 'Edit Preset', 'hmpro' ),
		__( 'Edit Preset', 'hmpro' ),
		'manage_options',
		'hmpro-preset-edit',
		'hmpro_render_preset_edit_page'
	);
}

function hmpro_theme_dashboard_page_render() {
	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'HM Pro Theme', 'hmpro' ) . '</h1>';
	echo '<p>' . esc_html__( 'Welcome. Use the left menu to manage Presets, Header Builder, and Footer Builder.', 'hmpro' ) . '</p>';
	echo '</div>';
}
