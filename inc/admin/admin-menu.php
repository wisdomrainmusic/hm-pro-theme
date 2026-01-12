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
		'hmpro_render_presets_page',
		'dashicons-art',
		58
	);

	add_submenu_page(
		'hmpro-theme',
		__( 'Presets', 'hmpro' ),
		__( 'Presets', 'hmpro' ),
		'manage_options',
		'hmpro-presets',
		'hmpro_render_presets_page'
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
