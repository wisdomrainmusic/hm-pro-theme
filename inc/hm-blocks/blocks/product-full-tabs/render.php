<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * HM Product Full Tabs (SSR)
 * - Query by product_cat or product_tag term ID
 * - Horizontal scroll-snap "slider" (no external swiper dependency)
 * - Full-bleed hero mode (100vw)
 */

if ( ! function_exists( 'hmpro_pft_sanitize_tabs' ) ) {
  function hmpro_pft_sanitize_tabs( $tabs ) {
    $out = [];
    if ( ! is_array( $tabs ) ) return $out;
    foreach ( $tabs as $t ) {
      if ( ! is_array( $t ) ) continue;
      $out[] = [
        'tabTitle'       => isset( $t['tabTitle'] ) ? sanitize_text_field( $t['tabTitle'] ) : '',
        'queryType'      => ( isset( $t['queryType'] ) && $t['queryType'] === 'tag' ) ? 'tag' : 'category',
        'categoryId'     => isset( $t['categoryId'] ) ? absint( $t['categoryId'] ) : 0,
        'tagId'          => isset( $t['tagId'] ) ? absint( $t['tagId'] ) : 0,
        'productsPerTab' => isset( $t['productsPerTab'] ) ? min( 24, max( 1, absint( $t['productsPerTab'] ) ) ) : 12,
      ];
    }
    return $out;
  }
}

if ( ! function_exists( 'hmpro_pft_query_products' ) ) {
function hmpro_pft_query_products( $query_type, $term_id, $limit ) {
	if ( ! class_exists( 'WooCommerce' ) ) return [];
	if ( $term_id <= 0 ) return [];

	$tax = ( $query_type === 'tag' ) ? 'product_tag' : 'product_cat';

	// WooCommerce query object may not be available in some contexts (REST/editor).
	$wc_query = null;
	if ( function_exists( 'WC' ) ) {
		$wc = WC();
		if ( is_object( $wc ) && isset( $wc->query ) && is_object( $wc->query ) ) {
			$wc_query = $wc->query;
		}
	}

	$meta_query = $wc_query ? $wc_query->get_meta_query() : [];
	$tax_query  = $wc_query ? $wc_query->get_tax_query() : [];

	// Always include our term filter.
	$tax_query[] = [
		'taxonomy' => $tax,
		'field'    => 'term_id',
		'terms'    => [ $term_id ],
	];

	$q = new WP_Query( [
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_query'          => $meta_query,
		'tax_query'           => $tax_query,
	] );

    return $q->have_posts() ? $q->posts : [];
  }
}

$attrs = isset( $attributes ) && is_array( $attributes ) ? $attributes : [];

$columns_per_view = isset( $attrs['columnsPerView'] ) ? absint( $attrs['columnsPerView'] ) : 4;
$columns_per_view = in_array( $columns_per_view, [3,4], true ) ? $columns_per_view : 4;

$full_bleed = ! empty( $attrs['fullBleed'] );

$tabs_alignment = isset( $attrs['tabsAlignment'] ) ? (string) $attrs['tabsAlignment'] : 'center';
if ( ! in_array( $tabs_alignment, ['flex-start','center','flex-end'], true ) ) $tabs_alignment = 'center';

$box_bg     = isset( $attrs['boxBg'] ) ? (string) $attrs['boxBg'] : '';
$box_radius = isset( $attrs['boxRadius'] ) ? absint( $attrs['boxRadius'] ) : 22;
$box_pad    = isset( $attrs['boxPadding'] ) ? absint( $attrs['boxPadding'] ) : 34;

$tab_bg        = isset( $attrs['tabBg'] ) ? (string) $attrs['tabBg'] : '';
$tab_color     = isset( $attrs['tabColor'] ) ? (string) $attrs['tabColor'] : '';
$tab_bg_active = isset( $attrs['tabBgActive'] ) ? (string) $attrs['tabBgActive'] : '';
$tab_c_active  = isset( $attrs['tabColorActive'] ) ? (string) $attrs['tabColorActive'] : '';
$tab_bg_hover  = isset( $attrs['tabBgHover'] ) ? (string) $attrs['tabBgHover'] : '';
$tab_c_hover   = isset( $attrs['tabColorHover'] ) ? (string) $attrs['tabColorHover'] : '';

$title_color = isset( $attrs['titleColor'] ) ? (string) $attrs['titleColor'] : '';
$price_color = isset( $attrs['priceColor'] ) ? (string) $attrs['priceColor'] : '';

$tabs = hmpro_pft_sanitize_tabs( isset( $attrs['tabs'] ) ? $attrs['tabs'] : [] );
$is_editor = is_admin();
if ( empty( $tabs ) ) {
  $tabs[] = [
    'tabTitle' => 'New Arrivals',
    'queryType' => 'category',
    'categoryId' => 0,
    'tagId' => 0,
    'productsPerTab' => 12,
  ];
}

$uid = 'hm-pft-' . wp_generate_uuid4();

$vars = [];
$vars[] = '--hm-pft-cols:' . $columns_per_view;
$vars[] = '--hm-pft-tabs-align:' . $tabs_alignment;
$vars[] = '--hm-pft-box-radius:' . $box_radius . 'px';
$vars[] = '--hm-pft-box-pad:' . $box_pad . 'px';
if ( $box_bg ) $vars[] = '--hm-pft-box-bg:' . esc_attr( $box_bg );
if ( $tab_bg ) $vars[] = '--hm-pft-tab-bg:' . esc_attr( $tab_bg );
if ( $tab_color ) $vars[] = '--hm-pft-tab-color:' . esc_attr( $tab_color );
if ( $tab_bg_active ) $vars[] = '--hm-pft-tab-bg-active:' . esc_attr( $tab_bg_active );
if ( $tab_c_active ) $vars[] = '--hm-pft-tab-color-active:' . esc_attr( $tab_c_active );
if ( $tab_bg_hover ) $vars[] = '--hm-pft-tab-bg-hover:' . esc_attr( $tab_bg_hover );
if ( $tab_c_hover ) $vars[] = '--hm-pft-tab-color-hover:' . esc_attr( $tab_c_hover );
if ( $title_color ) $vars[] = '--hm-pft-title-color:' . esc_attr( $title_color );
if ( $price_color ) $vars[] = '--hm-pft-price-color:' . esc_attr( $price_color );

$wrapper_classes = [
  'hm-pft',
  $full_bleed ? 'hm-pft--fullbleed-yes' : 'hm-pft--fullbleed-no',
];

?>
<div
  id="<?php echo esc_attr( $uid ); ?>"
  class="<?php echo esc_attr( implode( ' ', array_filter( $wrapper_classes ) ) ); ?>"
  style="<?php echo esc_attr( implode( ';', $vars ) ); ?>"
>
  <div class="hm-pft__box">
    <div class="hm-pft__tabs" role="tablist">
      <?php foreach ( $tabs as $i => $t ) :
        $tab_id = $uid . '-tab-' . $i;
        $is_active = ( $i === 0 );
      ?>
        <button
          type="button"
          class="hm-pft__tab-btn<?php echo $is_active ? ' is-active' : ''; ?>"
          data-tab="<?php echo esc_attr( $tab_id ); ?>"
          role="tab"
          aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
        >
          <?php echo esc_html( $t['tabTitle'] ? $t['tabTitle'] : ( 'Tab ' . ( $i + 1 ) ) ); ?>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="hm-pft__panes">
      <?php foreach ( $tabs as $i => $t ) :
        $tab_id = $uid . '-tab-' . $i;
        $is_active = ( $i === 0 );
        $term_id = ( $t['queryType'] === 'tag' ) ? $t['tagId'] : $t['categoryId'];
        $posts = hmpro_pft_query_products( $t['queryType'], $term_id, $t['productsPerTab'] );
      ?>
        <div class="hm-pft__pane<?php echo $is_active ? ' is-active' : ''; ?>" data-pane="<?php echo esc_attr( $tab_id ); ?>" role="tabpanel">
          <div class="hm-pft__nav hm-pft__nav--prev" data-nav="prev" aria-label="Previous"></div>
          <div class="hm-pft__nav hm-pft__nav--next" data-nav="next" aria-label="Next"></div>

          <div class="hm-pft__track" data-cols="<?php echo esc_attr( $columns_per_view ); ?>">
            <?php if ( empty( $posts ) ) : ?>
              <div class="hm-pft__empty">
                <?php echo esc_html__( 'No products found for this tab.', 'hmpro' ); ?>
              </div>
            <?php else : ?>
              <?php foreach ( $posts as $p ) :
                $product = wc_get_product( $p->ID );
                if ( ! $product ) continue;
                $permalink = get_permalink( $p->ID );
                $title = get_the_title( $p->ID );
                $img = get_the_post_thumbnail( $p->ID, 'large' );
                $price_html = $product->get_price_html();
              ?>
                <article class="hm-pft__slide">
                  <div class="hm-pft__card">
                    <div class="hm-pft__img">
                      <a href="<?php echo esc_url( $permalink ); ?>">
                        <?php echo $img ? $img : ''; ?>
                      </a>
                    </div>

                    <h3 class="hm-pft__title">
                      <a href="<?php echo esc_url( $permalink ); ?>">
                        <?php echo esc_html( $title ); ?>
                      </a>
                    </h3>

                    <div class="hm-pft__price">
                      <?php echo wp_kses_post( $price_html ); ?>
                    </div>

                    <div class="hm-pft__actions">
                      <a class="hm-pft__btn" href="<?php echo esc_url( $permalink ); ?>">
                        <?php echo esc_html__( 'Options', 'hmpro' ); ?>
                      </a>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
