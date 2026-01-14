<?php
/**
 * Plugin Name: HM Pro Category Importer
 * Description: Import WooCommerce product categories via CSV.
 * Version: 1.0.0
 * Author: HM Pro
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HM_Pro_Category_Importer {
	const ACTION = 'hm_pro_cat_import';
	const NONCE_ACTION = 'hm_pro_cat_import';
	const TAXONOMY = 'product_cat';

	public function __construct( $register_menu = true ) {
		if ( $register_menu ) {
			add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		}

		add_action( 'admin_post_' . self::ACTION, [ $this, 'handle_import' ] );
	}

	public function admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'Category Importer', 'hmpro' ),
			__( 'Category Importer', 'hmpro' ),
			'manage_options',
			'hmpro-category-importer',
			[ $this, 'render_page' ]
		);
	}

	public function render_page() {
		$notice = isset( $_GET['hmpro_cat_notice'] ) ? sanitize_key( wp_unslash( $_GET['hmpro_cat_notice'] ) ) : '';

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Category Importer', 'hmpro' ) . '</h1>';

		if ( ! class_exists( 'WooCommerce' ) ) {
			hmpro_render_woocommerce_notice();
		}

		if ( 'file_missing' === $notice ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Please choose a CSV file to import.', 'hmpro' ) . '</p></div>';
		} elseif ( 'woocommerce_missing' === $notice ) {
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'WooCommerce is required to import categories.', 'hmpro' ) . '</p></div>';
		} elseif ( 'imported' === $notice ) {
			$created = isset( $_GET['created'] ) ? absint( $_GET['created'] ) : 0;
			$updated = isset( $_GET['updated'] ) ? absint( $_GET['updated'] ) : 0;
			$skipped = isset( $_GET['skipped'] ) ? absint( $_GET['skipped'] ) : 0;
			$failed = isset( $_GET['failed'] ) ? absint( $_GET['failed'] ) : 0;
			echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(
				esc_html__( 'Import complete. Created: %1$d, Updated: %2$d, Skipped: %3$d, Failed: %4$d.', 'hmpro' ),
				$created,
				$updated,
				$skipped,
				$failed
			) . '</p></div>';
		}

		echo '<p>' . esc_html__( 'Upload a CSV with columns: name, slug (optional), parent_slug (optional).', 'hmpro' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data">';
		wp_nonce_field( self::NONCE_ACTION );
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		echo '<input type="file" name="hmpro_cat_csv" accept=".csv" required />';
		submit_button( __( 'Import Categories', 'hmpro' ) );
		echo '</form>';
		echo '</div>';
	}

	public function handle_import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to import categories.', 'hmpro' ) );
		}

		check_admin_referer( self::NONCE_ACTION );

		$back = wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=hmpro-category-importer' );
		$back = remove_query_arg( [ 'hmpro_cat_notice', 'created', 'updated', 'skipped', 'failed' ], $back );

		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_safe_redirect( add_query_arg( [ 'hmpro_cat_notice' => 'woocommerce_missing' ], $back ) );
			exit;
		}

		if ( empty( $_FILES['hmpro_cat_csv']['tmp_name'] ) ) {
			wp_safe_redirect( add_query_arg( [ 'hmpro_cat_notice' => 'file_missing' ], $back ) );
			exit;
		}

		$created = 0;
		$updated = 0;
		$skipped = 0;
		$failed = 0;

		$file = fopen( $_FILES['hmpro_cat_csv']['tmp_name'], 'r' );
		if ( false === $file ) {
			wp_safe_redirect( add_query_arg( [ 'hmpro_cat_notice' => 'file_missing' ], $back ) );
			exit;
		}

		while ( ( $row = fgetcsv( $file ) ) !== false ) {
			$name = isset( $row[0] ) ? sanitize_text_field( $row[0] ) : '';
			$slug = isset( $row[1] ) ? sanitize_title( $row[1] ) : '';
			$parent_slug = isset( $row[2] ) ? sanitize_title( $row[2] ) : '';

			if ( '' === $name ) {
				$skipped++;
				continue;
			}

			$parent_id = 0;
			if ( '' !== $parent_slug ) {
				$parent_term = get_term_by( 'slug', $parent_slug, self::TAXONOMY );
				if ( $parent_term ) {
					$parent_id = (int) $parent_term->term_id;
				}
			}

			$existing = null;
			if ( '' !== $slug ) {
				$existing = get_term_by( 'slug', $slug, self::TAXONOMY );
			} else {
				$maybe_existing = term_exists( $name, self::TAXONOMY );
				if ( is_array( $maybe_existing ) && isset( $maybe_existing['term_id'] ) ) {
					$existing = get_term( (int) $maybe_existing['term_id'], self::TAXONOMY );
				}
			}

			if ( $existing ) {
				$result = wp_update_term(
					(int) $existing->term_id,
					self::TAXONOMY,
					[
						'name'   => $name,
						'slug'   => '' !== $slug ? $slug : $existing->slug,
						'parent' => $parent_id,
					]
				);
				if ( is_wp_error( $result ) ) {
					$failed++;
				} else {
					$updated++;
				}
				continue;
			}

			$args = [ 'name' => $name ];
			if ( '' !== $slug ) {
				$args['slug'] = $slug;
			}
			if ( 0 !== $parent_id ) {
				$args['parent'] = $parent_id;
			}

			$result = wp_insert_term( $name, self::TAXONOMY, $args );
			if ( is_wp_error( $result ) ) {
				$failed++;
			} else {
				$created++;
			}
		}

		fclose( $file );

		$back = add_query_arg(
			[
				'hmpro_cat_notice' => 'imported',
				'created'          => $created,
				'updated'          => $updated,
				'skipped'          => $skipped,
				'failed'           => $failed,
			],
			$back
		);

		wp_safe_redirect( $back );
		exit;
	}
}

function hmpro_get_category_importer() {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new HM_Pro_Category_Importer( false );
	}

	return $instance;
}

function hmpro_render_category_importer_page() {
	hmpro_get_category_importer()->render_page();
}
