<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'HMPRO_CATEGORY_IMPORTER_EMBEDDED', true );
define( 'HMPRO_SLUG_MENU_BUILDER_EMBEDDED', true );
define( 'HMPRO_PRODUCT_IMPORTER_EMBEDDED', true );
define( 'HMPRO_MENU_CONTROLLER_EMBEDDED', true );

require_once HMPRO_PATH . '/inc/tools/category-importer/category-importer.php';
require_once HMPRO_PATH . '/inc/tools/slug-menu-builder/slug-menu-builder.php';
require_once HMPRO_PATH . '/inc/tools/product-importer/product-importer.php';
require_once HMPRO_PATH . '/inc/tools/hm-menu-controller/hm-menu-controller.php';

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
