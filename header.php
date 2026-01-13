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

<?php if ( function_exists( 'hmpro_header_builder_has_layout' ) && hmpro_header_builder_has_layout() ) : ?>

	<?php do_action( 'hmpro/header/builder/before' ); ?>

	<header id="site-header" class="hmpro-header-builder">
		<?php hmpro_render_builder_region( 'header_top', 'header' ); ?>
		<?php hmpro_render_builder_region( 'header_main', 'header' ); ?>
		<?php hmpro_render_builder_region( 'header_bottom', 'header' ); ?>
	</header>

	<?php do_action( 'hmpro/header/builder/after' ); ?>

<?php endif; ?>

<?php
if ( ! function_exists( 'hmpro_header_builder_has_layout' ) || ! hmpro_header_builder_has_layout() ) {
	hmpro_render_legacy_header();
}
?>

<?php do_action( 'hmpro/header/after' ); ?>
