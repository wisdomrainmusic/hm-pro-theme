<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin screens: Header/Footer Builder (UI shell only)
 * - Commit 017 will add storage
 * - Commit 019 will add drag/drop + settings
 */

function hmpro_render_header_builder_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'hmpro' ) );
	}

	hmpro_render_builder_shell( 'header' );
}

function hmpro_render_footer_builder_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'hmpro' ) );
	}

	hmpro_render_builder_shell( 'footer' );
}

/**
 * Shared builder UI shell (no persistence in this commit).
 */
function hmpro_render_builder_shell( $area ) {
	$area = ( 'footer' === $area ) ? 'footer' : 'header';

	$title = ( 'footer' === $area )
		? __( 'Footer Builder', 'hmpro' )
		: __( 'Header Builder', 'hmpro' );

	$sections = [
		'top'    => __( 'Top', 'hmpro' ),
		'main'   => __( 'Main', 'hmpro' ),
		'bottom' => __( 'Bottom', 'hmpro' ),
	];

	$elements = [
		'logo'   => __( 'Logo', 'hmpro' ),
		'menu'   => __( 'Menu', 'hmpro' ),
		'search' => __( 'Search', 'hmpro' ),
		'cart'   => __( 'Cart', 'hmpro' ),
		'button' => __( 'Button', 'hmpro' ),
		'html'   => __( 'HTML', 'hmpro' ),
		'spacer' => __( 'Spacer', 'hmpro' ),
	];

	?>
	<div class="wrap hmpro-builder-wrap" data-area="<?php echo esc_attr( $area ); ?>">
		<h1><?php echo esc_html( $title ); ?></h1>

		<form method="post" action="">
			<?php wp_nonce_field( 'hmpro_builder_' . $area, 'hmpro_builder_nonce' ); ?>
			<input type="hidden" name="hmpro_action" value="builder_placeholder" />

			<div class="hmpro-builder-layout">

				<aside class="hmpro-builder-panel hmpro-builder-sections">
					<h2><?php esc_html_e( 'Sections', 'hmpro' ); ?></h2>
					<ul class="hmpro-builder-list">
						<?php foreach ( $sections as $key => $label ) : ?>
							<li>
								<button type="button" class="button hmpro-builder-section-btn" data-section="<?php echo esc_attr( $key ); ?>">
									<?php echo esc_html( $label ); ?>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
					<p class="description">
						<?php esc_html_e( 'Select a section to edit. Layout saving will be enabled in the next commit.', 'hmpro' ); ?>
					</p>
				</aside>

				<main class="hmpro-builder-canvas" aria-label="<?php esc_attr_e( 'Builder Canvas', 'hmpro' ); ?>">
					<div class="hmpro-builder-canvas-inner">
						<div class="hmpro-builder-empty">
							<h2><?php esc_html_e( 'Canvas', 'hmpro' ); ?></h2>
							<p><?php esc_html_e( 'No components yet. Add items from the Elements panel.', 'hmpro' ); ?></p>
							<p>
								<button type="button" class="button button-primary hmpro-builder-add-first">
									<?php esc_html_e( 'Add a component', 'hmpro' ); ?>
								</button>
							</p>
						</div>
					</div>
				</main>

				<aside class="hmpro-builder-panel hmpro-builder-elements">
					<h2><?php esc_html_e( 'Elements', 'hmpro' ); ?></h2>
					<ul class="hmpro-builder-list">
						<?php foreach ( $elements as $type => $label ) : ?>
							<li>
								<button type="button" class="button hmpro-builder-element-btn" data-type="<?php echo esc_attr( $type ); ?>">
									<?php echo esc_html( $label ); ?>
								</button>
							</li>
						<?php endforeach; ?>
					</ul>
					<p class="description">
						<?php esc_html_e( 'Drag & drop and component settings will be added in a later commit.', 'hmpro' ); ?>
					</p>
				</aside>

			</div>
		</form>
	</div>
	<?php
}
