<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hmpro_render_legacy_header' ) ) {
	function hmpro_render_legacy_header() {
		?>
		<header class="site-header">
			<div class="hmpro-container">
				<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php bloginfo( 'name' ); ?>
				</a>

				<nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary Menu', 'hmpro' ); ?>">
					<?php
					wp_nav_menu( [
						'theme_location' => 'hm_primary',
						'container'      => false,
						'fallback_cb'    => '__return_false',
					] );
					?>
				</nav>
			</div>
		</header>
		<?php
	}
}

do_action( 'hmpro/header/before' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php $hmpro_account_url = home_url( '/hesabim/' ); ?>

<?php
// Inline SVG icons (consistent across fonts/browsers)
function hmpro_icon_hamburger() {
	return '<svg class="hmpro-icon hmpro-icon-burger" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<path d="M4 7h16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
		<path d="M4 12h16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
		<path d="M4 17h16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
	</svg>';
}
function hmpro_icon_close() {
	return '<svg class="hmpro-icon hmpro-icon-close" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<path d="M6 6l12 12" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
		<path d="M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"/>
	</svg>';
}
?>

<?php if ( function_exists( 'hmpro_header_builder_has_layout' ) && hmpro_header_builder_has_layout() ) : ?>

	<?php do_action( 'hmpro/header/builder/before' ); ?>

	<header id="site-header" class="hmpro-header-builder">
		<?php hmpro_render_builder_region( 'header_top', 'header' ); ?>
		<?php hmpro_render_builder_region( 'header_main', 'header' ); ?>
		<?php hmpro_render_builder_region( 'header_bottom', 'header' ); ?>

		<?php
		/**
		 * NOTE:
		 * The account CTA is intentionally NOT injected as a fixed desktop element.
		 * Use Header Builder components (HTML/Button) to place the CTA inside header_top/header_main.
		 * Mobile CTA remains inside the drawer for a consistent UX.
		 */
		?>

		<!-- Mobile hamburger toggle -->
		<button
			type="button"
			class="hmpro-mobile-menu-toggle"
			aria-label="<?php echo esc_attr__( 'Menüyü Aç', 'hm-pro-theme' ); ?>"
			aria-controls="hmpro-mobile-drawer"
			aria-expanded="false"
		>
			<?php echo hmpro_icon_hamburger(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</header>

	<!-- Mobile drawer (right side) -->
	<div id="hmpro-mobile-drawer" class="hmpro-mobile-drawer" aria-hidden="true">
		<div class="hmpro-mobile-drawer-overlay" data-hmpro-close="1"></div>
		<div class="hmpro-mobile-drawer-panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Mobil Menü', 'hm-pro-theme' ); ?>">
			<div class="hmpro-mobile-drawer-head">
				<a class="hmpro-mobile-account-cta" href="<?php echo esc_url( $hmpro_account_url ); ?>">
					<?php echo esc_html__( 'Giriş Yap / Kayıt Ol', 'hm-pro-theme' ); ?>
				</a>
				<button type="button" class="hmpro-mobile-drawer-close" data-hmpro-close="1" aria-label="<?php echo esc_attr__( 'Menüyü Kapat', 'hm-pro-theme' ); ?>">
					<?php echo hmpro_icon_close(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</div>
			<nav class="hmpro-mobile-nav" aria-label="<?php echo esc_attr__( 'Mobil Menü', 'hm-pro-theme' ); ?>">
				<?php
				// Prefer "Mobil Menü" location. Fallback to Primary if not assigned.
				if ( has_nav_menu( 'mobile_menu' ) ) {
					wp_nav_menu( [
						'theme_location' => 'mobile_menu',
						'container'      => false,
						'menu_class'     => 'hmpro-mobile-menu',
						'depth'          => 3,
					] );
				} elseif ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu( [
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'hmpro-mobile-menu',
						'depth'          => 3,
					] );
				}
				?>
			</nav>
		</div>
	</div>

	<?php do_action( 'hmpro/header/builder/after' ); ?>

<?php endif; ?>

<?php
if ( ! function_exists( 'hmpro_header_builder_has_layout' ) || ! hmpro_header_builder_has_layout() ) {
	hmpro_render_legacy_header();
}
?>

<?php do_action( 'hmpro/header/after' ); ?>
