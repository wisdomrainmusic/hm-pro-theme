<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

<?php if ( function_exists( 'hmpro_has_builder_layout' ) && hmpro_has_builder_layout( 'header' ) ) : ?>

	<?php do_action( 'hmpro/header/builder/before' ); ?>

	<header id="site-header" class="hmpro-header-builder">
		<?php hmpro_render_builder_region( 'header_top', 'header' ); ?>
		<?php hmpro_render_builder_region( 'header_main', 'header' ); ?>
		<?php hmpro_render_builder_region( 'header_bottom', 'header' ); ?>
	</header>

	<?php do_action( 'hmpro/header/builder/after' ); ?>

<?php else : ?>

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

<?php endif; ?>

<?php do_action( 'hmpro/header/after' ); ?>
