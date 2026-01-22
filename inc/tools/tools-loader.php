<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'HMPRO_CATEGORY_IMPORTER_EMBEDDED', true );
define( 'HMPRO_SLUG_MENU_BUILDER_EMBEDDED', true );
define( 'HMPRO_PRODUCT_IMPORTER_EMBEDDED', true );
define( 'HMPRO_MENU_CONTROLLER_EMBEDDED', true );
define( 'HMPRO_BASIC_CEVIRI_INLINE_EMBEDDED', true );

require_once HMPRO_PATH . '/inc/tools/category-importer/category-importer.php';
require_once HMPRO_PATH . '/inc/tools/slug-menu-builder/slug-menu-builder.php';
require_once HMPRO_PATH . '/inc/tools/product-importer/product-importer.php';
require_once HMPRO_PATH . '/inc/tools/hm-menu-controller/hm-menu-controller.php';
require_once HMPRO_PATH . '/inc/tools/hm-basic-ceviri-inline/hm-basic-ceviri-inline.php';

// Move HM Basic Çeviri (Inline) from Settings -> HM Pro Theme submenu.
// We keep the original module intact and only relocate its admin page.
add_action( 'admin_menu', function () {
    if ( ! class_exists( 'HM_Basic_Ceviri_Inline' ) ) {
        return;
    }

    // Remove Settings submenu.
    remove_submenu_page( 'options-general.php', 'hm-basic-ceviri-inline' );

    // Re-add under HM Pro Theme.
    add_submenu_page(
        'hmpro-theme',
        'HM Basic Translate',
        'HM Basic Translate',
        'manage_options',
        'hm-basic-ceviri-inline',
        [ 'HM_Basic_Ceviri_Inline', 'settings_page' ]
    );
}, 999 );

add_action( 'admin_init', function () {

    // Category Importer boot
    if ( function_exists( 'hmpro_get_category_importer' ) ) {
        hmpro_get_category_importer();
    } elseif ( class_exists( 'HM_Pro_Category_Importer' ) ) {
        new HM_Pro_Category_Importer( false );
    }

    // Slug Menu Builder boot
    if ( function_exists( 'hmpro_get_slug_menu_builder' ) ) {
        hmpro_get_slug_menu_builder();
    } elseif ( class_exists( 'HM_Product_Cat_Menu_Builder' ) ) {
        new HM_Product_Cat_Menu_Builder( false );
    }

}, 20 );
