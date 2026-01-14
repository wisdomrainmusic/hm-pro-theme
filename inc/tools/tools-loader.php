<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'HMPRO_CATEGORY_IMPORTER_EMBEDDED', true );
require_once HMPRO_PATH . '/inc/tools/category-importer/category-importer.php';

add_action( 'admin_init', function () {
    if ( function_exists( 'hmpro_get_category_importer' ) ) {
        hmpro_get_category_importer();
    } elseif ( class_exists( 'HM_Pro_Category_Importer' ) ) {
        new HM_Pro_Category_Importer( false );
    }
}, 20 );
