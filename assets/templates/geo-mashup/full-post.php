<?php
/**
 * Geo Mashup Full Post Template.
 *
 * This is a copy of the default template for full post display of a clicked
 * marker in a Geo Mashup map.
 *
 * See "full-post.php" in the Geo Mashup "default-templates" directory.
 *
 * @package WPCV_EO_Maps
 * @since 1.0
 */

?><!-- assets/templates/geo-mashup/full-post.php -->

<?php if ( have_posts() ) : ?>

	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>

		<h2><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
		<p class="meta"><span class="blogdate"><?php the_time( 'F jS, Y' ); ?></span> <?php the_category( ', ' ); ?></p>
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium' ); ?>
		<?php endif; ?>

		<div class="storycontent">
			<?php the_content(); ?>
		</div>

	<?php endwhile; ?>

<?php else : ?>

	<h2 class="center"><?php esc_html_e( 'Not Found', 'wpcv-eo-maps-integration' ); ?></h2>
	<p class="center"><?php esc_html_e( 'Sorry, but you are looking for something that isn’t here.', 'wpcv-eo-maps-integration' ); ?></p>

<?php endif; ?>
