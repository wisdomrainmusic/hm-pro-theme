<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer class="site-footer">
	<div class="hmpro-container">
		<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer Menu', 'hmpro' ); ?>">
			<?php
			wp_nav_menu( [
				'theme_location' => 'hm_footer',
				'container'      => false,
				'fallback_cb'    => '__return_false',
			] );
			?>
		</nav>
		<p class="hmpro-copyright">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
