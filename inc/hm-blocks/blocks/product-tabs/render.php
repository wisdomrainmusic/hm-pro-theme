<?php
/**
 * Server-side render for HM Product Tabs block.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$full_width = ! empty( $attributes['fullWidth'] );
$columns_desktop = isset( $attributes['columnsDesktop'] ) ? absint( $attributes['columnsDesktop'] ) : 4;
$grid_gap = isset( $attributes['gridGap'] ) ? absint( $attributes['gridGap'] ) : 20;
$tabs_align = isset( $attributes['tabsAlign'] ) ? sanitize_text_field( $attributes['tabsAlign'] ) : 'center';
$tabs = isset( $attributes['tabs'] ) && is_array( $attributes['tabs'] ) ? $attributes['tabs'] : array();

$styles = sprintf(
	'--hmpro-pft-columns:%d; --hmpro-pft-gap:%dpx; --hmpro-pft-tabs-align:%s;',
	$columns_desktop > 0 ? $columns_desktop : 4,
	$grid_gap,
	esc_attr( $tabs_align )
);

$classes = 'hmpro-product-tabs';
if ( $full_width ) {
	$classes .= ' is-full-width';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $classes,
		'style' => $styles,
	)
);

if ( empty( $tabs ) ) {
	$tabs = array(
		array(
			'title' => __( 'Tab 1', 'hm-pro-theme' ),
		),
	);
}

$tab_buttons = array();
foreach ( $tabs as $index => $tab ) {
	$title = isset( $tab['title'] ) && $tab['title'] !== '' ? $tab['title'] : sprintf( __( 'Tab %d', 'hm-pro-theme' ), $index + 1 );
	$tab_buttons[] = sprintf(
		'<button class="hmpro-product-tabs__tab" type="button" data-index="%1$d" aria-selected="false">%2$s</button>',
		absint( $index ),
		esc_html( $title )
	);
}
?>
<div <?php echo $wrapper_attributes; ?>>
	<div class="hmpro-product-tabs__tabs" role="tablist">
		<?php echo implode( '', $tab_buttons ); ?>
	</div>

	<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
		<div class="hmpro-product-tabs__notice">
			<?php esc_html_e( 'WooCommerce is not active. The product grid will render once WooCommerce is enabled.', 'hm-pro-theme' ); ?>
		</div>
	<?php else : ?>
		<div class="hmpro-product-tabs__panel">
			<?php esc_html_e( 'Product tabs will render here on the frontend.', 'hm-pro-theme' ); ?>
		</div>
	<?php endif; ?>
</div>
