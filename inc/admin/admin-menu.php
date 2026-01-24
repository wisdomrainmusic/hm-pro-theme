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

	add_submenu_page(
		'hmpro-theme',
		__( 'Mega Menu Builder', 'hmpro' ),
		__( 'Mega Menu Builder', 'hmpro' ),
		'manage_options',
		'hmpro-mega-menu-builder',
		'hmpro_render_mega_menu_builder_page'
	);

	// Menu Access Control (HM Menu Controller)
	// HM Menu Controller registers its submenu on admin_menu as well, but it can
	// run before the parent HM Pro Theme menu is registered depending on load order.
	// This fallback ensures the page always appears under HM Pro Theme.
	if ( class_exists( 'HM_MC_Admin_Page' ) ) {
		add_submenu_page(
			'hmpro-theme',
			__( 'Menu Access Control', 'hmpro' ),
			__( 'Menu Access Control', 'hmpro' ),
			'manage_options',
			'hmpro-menu-controller',
			[ 'HM_MC_Admin_Page', 'render_page' ]
		);
	}

	add_submenu_page(
		'hmpro-theme',
		__( 'Importers', 'hmpro' ),
		__( 'Importers', 'hmpro' ),
		'manage_options',
		'hmpro-importers',
		'hmpro_render_importers_page'
	);

	add_submenu_page(
		'hmpro-theme',
		__( 'Category Importer', 'hmpro' ),
		__( 'Category Importer', 'hmpro' ),
		'manage_options',
		'hmpro-category-importer',
		'hmpro_render_category_importer_page'
	);


	add_submenu_page(
		'hmpro-theme',
		__( 'Slug Menu Builder', 'hmpro' ),
		__( 'Slug Menu Builder', 'hmpro' ),
		'manage_options',
		'hmpro-slug-menu-builder',
		'hmpro_render_slug_menu_builder_page'
	);


	add_submenu_page(
		'hmpro-theme',
		__( 'Product Importer', 'hmpro' ),
		__( 'Product Importer', 'hmpro' ),
		'manage_woocommerce',
		'hmpro-product-importer',
		'hmpro_render_product_importer_page'
	);


	add_submenu_page(
		'hmpro-theme',
		__( 'Product Exporter', 'hmpro' ),
		__( 'Product Exporter', 'hmpro' ),
		'manage_woocommerce',
		'hmpro-product-exporter',
		'hmpro_render_product_exporter_page'
	);

	/**
	 * Hidden page for editing presets (not shown in menu).
	 * IMPORTANT: Do NOT pass null as parent_slug (triggers PHP 8.1+ deprecations via plugin_basename()).
	 */
	add_submenu_page(
		'hmpro-theme',
		__( 'Edit Preset', 'hmpro' ),
		__( 'Edit Preset', 'hmpro' ),
		'manage_options',
		'hmpro-preset-edit',
		'hmpro_render_preset_edit_page'
	);
	// Keep it accessible by URL, but hide it from the submenu list:
	remove_submenu_page( 'hmpro-theme', 'hmpro-preset-edit' );
}

function hmpro_theme_dashboard_page_render() {
	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'HM Pro Theme', 'hmpro' ) . '</h1>';
	echo '<p>' . esc_html__( 'Welcome. Use the left menu to manage Presets, Header Builder, and Footer Builder.', 'hmpro' ) . '</p>';
	echo '</div>';
}

function hmpro_render_importers_page() {
	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Importers', 'hmpro' ) . '</h1>';

	if ( ! class_exists( 'WooCommerce' ) ) {
		hmpro_render_woocommerce_notice();
	}

	echo '<p>' . esc_html__( 'Access import and export tools for your store.', 'hmpro' ) . '</p>';

	echo '<ul style="list-style:disc;padding-left:20px;max-width:780px;">';

	echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=hmpro-category-importer' ) ) . '"><strong>' . esc_html__( 'Category Importer', 'hmpro' ) . '</strong></a> — ' . esc_html__( 'Import product category hierarchies from CSV.', 'hmpro' ) . '</li>';

	echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=hmpro-slug-menu-builder' ) ) . '"><strong>' . esc_html__( 'Slug Menu Builder', 'hmpro' ) . '</strong></a> — ' . esc_html__( 'Build or sync navigation menus from taxonomy hierarchies.', 'hmpro' ) . '</li>';

	echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=hmpro-product-importer' ) ) . '"><strong>' . esc_html__( 'Product Importer', 'hmpro' ) . '</strong></a> — ' . esc_html__( 'Import products from CSV using a queued workflow.', 'hmpro' ) . '</li>';

	echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=hmpro-product-exporter' ) ) . '"><strong>' . esc_html__( 'Product Exporter', 'hmpro' ) . '</strong></a> — ' . esc_html__( 'Export products to CSV with filters.', 'hmpro' ) . '</li>';

	echo '</ul>';

	echo '</div>';
}

function hmpro_render_woocommerce_notice() {
	echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'WooCommerce is not active. Some tools require WooCommerce to function.', 'hmpro' ) . '</p></div>';
}
