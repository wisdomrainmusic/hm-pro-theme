<?php
/**
 * The main template file
 *
 * @package HMPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main">
	<div class="hmpro-container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php
						// Astra-like title visibility toggle (per page/post).
						if ( ! function_exists( 'hmpro_is_title_hidden' ) || ! hmpro_is_title_hidden( get_the_ID() ) ) {
							the_title( '<h1 class="entry-title">', '</h1>' );
						}
						?>
					</header>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>
				<?php
			endwhile;
		else :
			?>
			<p><?php esc_html_e( 'No content found.', 'hmpro' ); ?></p>
			<?php
		endif;
		?>
	</div>
</main>

<?php
get_footer();
