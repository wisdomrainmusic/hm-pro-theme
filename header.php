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

<?php if ( function_exists( 'hmpro_header_builder_has_layout' ) && hmpro_header_builder_has_layout() ) : ?>

	<?php do_action( 'hmpro/header/builder/before' ); ?>

	<header id="site-header" class="hmpro-header-builder">
		<?php hmpro_render_builder_region( 'header_top', 'header' ); ?>
		<?php hmpro_render_builder_region( 'header_main', 'header' ); ?>
		<?php hmpro_render_builder_region( 'header_bottom', 'header' ); ?>

		<!-- Desktop persistent account CTA (Woo /hesabim) -->
		<div class="hmpro-header-account-cta">
			<a class="hmpro-account-link" href="<?php echo esc_url( $hmpro_account_url ); ?>">
				<?php echo esc_html__( 'Giriş Yap / Kayıt Ol', 'hm-pro-theme' ); ?>
			</a>
		</div>

		<!-- Mobile hamburger toggle -->
		<button
			type="button"
			class="hmpro-mobile-menu-toggle"
			aria-label="<?php echo esc_attr__( 'Menüyü Aç', 'hm-pro-theme' ); ?>"
			aria-controls="hmpro-mobile-drawer"
			aria-expanded="false"
		>
			<span class="hmpro-burger" aria-hidden="true"></span>
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
					×
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
