<?php
/**
 * HM Pro Blocks (theme module)
 *
 * Location: /inc/hm-blocks/
 *
 * This module registers custom Gutenberg blocks used for landing pages.
 * It is intentionally theme-embedded ("closed package").
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'HMPRO_VERSION' ) ) {
	// Fallback in case constants are not loaded yet.
	define( 'HMPRO_VERSION', '0.1.0' );
}

// Paths.
if ( ! defined( 'HMPRO_BLOCKS_PATH' ) ) {
	define( 'HMPRO_BLOCKS_PATH', trailingslashit( get_template_directory() ) . 'inc/hm-blocks' );
}
if ( ! defined( 'HMPRO_BLOCKS_URL' ) ) {
	define( 'HMPRO_BLOCKS_URL', trailingslashit( get_template_directory_uri() ) . 'inc/hm-blocks' );
}

// Shared renderer utilities.
require_once HMPRO_BLOCKS_PATH . '/render/shared/helpers.php';
require_once HMPRO_BLOCKS_PATH . '/render/shared/sanitize.php';
require_once HMPRO_BLOCKS_PATH . '/render/shared/templates.php';

/**
 * Register "HM Pro" block category in the inserter.
 */
add_filter( 'block_categories_all', function ( $categories ) {
	$slug = 'hmpro';
	foreach ( $categories as $cat ) {
		if ( isset( $cat['slug'] ) && $cat['slug'] === $slug ) {
			return $categories;
		}
	}

	$categories[] = [
		'slug'  => $slug,
		'title' => __( 'HM Pro Blocks', 'hm-pro-theme' ),
		'icon'  => null,
	];

	return $categories;
}, 20 );

/**
 * Register shared assets for HM Pro blocks.
 */
function hmpro_blocks_register_shared_assets() {
	wp_register_style(
		'hmpro-blocks',
		HMPRO_BLOCKS_URL . '/assets/css/blocks.css',
		[],
		file_exists( HMPRO_BLOCKS_PATH . '/assets/css/blocks.css' ) ? filemtime( HMPRO_BLOCKS_PATH . '/assets/css/blocks.css' ) : HMPRO_VERSION
	);

	wp_register_style(
		'hmpro-blocks-editor',
		HMPRO_BLOCKS_URL . '/assets/css/editor.css',
		[ 'wp-edit-blocks' ],
		file_exists( HMPRO_BLOCKS_PATH . '/assets/css/editor.css' ) ? filemtime( HMPRO_BLOCKS_PATH . '/assets/css/editor.css' ) : HMPRO_VERSION
	);

	wp_register_script(
		'hmpro-blocks-editor',
		HMPRO_BLOCKS_URL . '/assets/js/editor.js',
		[ 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ],
		file_exists( HMPRO_BLOCKS_PATH . '/assets/js/editor.js' ) ? filemtime( HMPRO_BLOCKS_PATH . '/assets/js/editor.js' ) : HMPRO_VERSION,
		true
	);
}
add_action( 'init', 'hmpro_blocks_register_shared_assets', 5 );

/**
 * Enqueue shared frontend styles for any HM Pro block.
 */
function hmpro_blocks_enqueue_block_assets() {
	wp_enqueue_style( 'hmpro-blocks' );
}
add_action( 'enqueue_block_assets', 'hmpro_blocks_enqueue_block_assets' );

/**
 * Enqueue editor-only assets.
 */
function hmpro_blocks_enqueue_editor_assets() {
	wp_enqueue_style( 'hmpro-blocks-editor' );
	wp_enqueue_script( 'hmpro-blocks-editor' );
}
add_action( 'enqueue_block_editor_assets', 'hmpro_blocks_enqueue_editor_assets' );

/**
 * Register blocks located under /inc/hm-blocks/blocks/*
 *
 * Each block folder is expected to contain a block.json.
 */
function hmpro_blocks_register_blocks() {
	$blocks_dir = trailingslashit( HMPRO_BLOCKS_PATH ) . 'blocks';
	if ( ! is_dir( $blocks_dir ) ) {
		return;
	}

	$entries = glob( $blocks_dir . '/*', GLOB_ONLYDIR );
	if ( empty( $entries ) ) {
		return;
	}

	foreach ( $entries as $dir ) {
		$block_json = trailingslashit( $dir ) . 'block.json';
		if ( file_exists( $block_json ) ) {
			register_block_type( $dir );
		}
	}
}
add_action( 'init', 'hmpro_blocks_register_blocks', 20 );

/**
 * REST: Return ALL WooCommerce terms (categories/tags) for editor controls.
 *
 * Some hosts/caches strip REST pagination headers which can cause incomplete term lists
 * in editor combobox controls. This endpoint returns the full list in a single response.
 */
function hmpro_pft_register_terms_rest_route() {
	register_rest_route(
		'hmpro/v1',
		'/terms',
		array(
			'methods'             => 'GET',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'args'                => array(
				'taxonomy' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_key',
				),
			),
			'callback'            => function ( WP_REST_Request $request ) {
				$taxonomy = $request->get_param( 'taxonomy' );
				if ( ! in_array( $taxonomy, array( 'product_cat', 'product_tag' ), true ) ) {
					return new WP_Error( 'hmpro_invalid_taxonomy', 'Invalid taxonomy.', array( 'status' => 400 ) );
				}

				$terms = get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
					)
				);

				if ( is_wp_error( $terms ) ) {
					return $terms;
				}

				$data = array();
				foreach ( $terms as $term ) {
					$data[] = array(
						'id'     => (int) $term->term_id,
						'name'   => (string) $term->name,
						'parent' => (int) $term->parent,
					);
				}

				return rest_ensure_response( $data );
			},
		)
	);
}
add_action( 'rest_api_init', 'hmpro_pft_register_terms_rest_route' );

/**
 * AJAX: Fetch paged products HTML for HM Product Tabs.
 */
function hmpro_pft_ajax_fetch_products() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'hmpro_pft' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce.' ), 403 );
	}

	$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : 'product_cat';
	$term_id  = isset( $_POST['termId'] ) ? absint( $_POST['termId'] ) : 0;
	$per_page = isset( $_POST['perPage'] ) ? max( 1, absint( $_POST['perPage'] ) ) : 8;
	$page     = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;

	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error( array( 'message' => 'WooCommerce is not active.' ), 400 );
	}

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'no_found_rows'  => true,
	);

	if ( $term_id > 0 && in_array( $taxonomy, array( 'product_cat', 'product_tag' ), true ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		);
	}

	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) {
		wp_send_json_success( array( 'html' => '<div class="hmpro-pft__empty">Ürün bulunamadı.</div>' ) );
	}

	ob_start();
	?>
	<div class="hmpro-pft__grid" role="list">
		<?php $hmpro_pft_count = 0; ?>
		<?php while ( $q->have_posts() ) : $q->the_post(); ?>
			<?php
				$product = wc_get_product( get_the_ID() );
				if ( ! $product ) {
					continue;
				}
			?>
			<?php $hmpro_pft_count++; ?>
			<a class="hmpro-pft__card" href="<?php echo esc_url( get_permalink() ); ?>" role="listitem">
				<div class="hmpro-pft__thumb">
					<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
				</div>
				<div class="hmpro-pft__meta">
					<div class="hmpro-pft__title"><?php echo esc_html( get_the_title() ); ?></div>
					<div class="hmpro-pft__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
				</div>
			</a>
		<?php endwhile; ?>
		<?php
			$hmpro_pft_target = max( 1, absint( $per_page ) );
			for ( $hmpro_pft_i = $hmpro_pft_count; $hmpro_pft_i < $hmpro_pft_target; $hmpro_pft_i++ ) {
				echo '<div class="hmpro-pft__card hmpro-pft__card--placeholder" aria-hidden="true"></div>';
			}
		?>
	</div>
	<?php
	wp_reset_postdata();
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}

add_action( 'wp_ajax_hmpro_pft_fetch_products', 'hmpro_pft_ajax_fetch_products' );
add_action( 'wp_ajax_nopriv_hmpro_pft_fetch_products', 'hmpro_pft_ajax_fetch_products' );
