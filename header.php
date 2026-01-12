<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

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
