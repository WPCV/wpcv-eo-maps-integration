<?php
/**
 * Geo Mashup Info Window Template.
 *
 * This is a copy of the default template for the info window in Geo Mashup maps.
 *
 * See "info-window.php" the Geo Mashup "default-templates" directory.
 *
 * For styling of the info window, see "map-style.css".
 *
 * @package WPCV_EO_Maps
 * @since 1.0
 */

// Modify the Post Thumbnail size.
add_filter( 'post_thumbnail_size', [ 'GeoMashupQuery', 'post_thumbnail_size' ]);

// A potentially heavy-handed way to remove shortcode-like content.
add_filter( 'the_excerpt', [ 'GeoMashupQuery', 'strip_brackets' ] );

?><!-- assets/templates/geo-mashup/info-window.php -->

<div class="locationinfo post-location-info">

	<?php if ( have_posts() ) : ?>

		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php

			/*
			$e = new \Exception();
			$trace = $e->getTraceAsString();
			error_log( print_r( [
				'method' => __METHOD__,
				'wp_query' => $wp_query,
				//'backtrace' => $trace,
			], true ) );
			*/

			$multiple_items_class = '';
			if ( $wp_query->post_count > 1 ) {
				$multiple_items_class = ' multiple_items';
			}

			?>

			<div class="location-post<?php echo $multiple_items_class; ?>">

				<?php

				// Init feature image vars.
				$has_feature_image = false;
				$feature_image_class = '';
				if ( has_post_thumbnail() ) {
					$has_feature_image = true;
					$feature_image_class = ' has_feature_image';
				}

				?>

				<div class="post_header<?php echo $feature_image_class; ?>">

					<?php if ( $has_feature_image ) : ?>
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="feature-link"><?php the_post_thumbnail( 'medium' ); ?></a>
					<?php endif; ?>

					<div class="post_header_text">
						<h2><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
					</div><!-- /.post_header_text -->

				</div><!-- /.post_header -->

				<?php if ( apply_filters( 'wpcv_eo_maps/info_window/content', true ) ) : ?>
					<?php if ( $wp_query->post_count == 1 ) : ?>
						<div class="storycontent">
							<p><?php echo wp_strip_all_tags( get_the_excerpt() ); ?></p>
							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="more-link"><?php esc_html_e( 'Read more', 'wpcv-eo-maps-integration' ); ?></a>
						</div>
					<?php else : ?>
						<?php if ( ! $has_feature_image ) : ?>
						<div class="storycontent">
							<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="more-link"><?php esc_html_e( 'Read more', 'wpcv-eo-maps-integration' ); ?></a>
						</div>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>

			</div><!-- /.location-post -->

		<?php endwhile; ?>

	<?php else : ?>

		<h2 class="center"><?php esc_html_e( 'Not Found', 'wpcv-eo-maps-integration' ); ?></h2>
		<p class="center"><?php esc_html_e( 'Sorry, but you are looking for something that isn’t here.', 'wpcv-eo-maps-integration' ); ?></p>

	<?php endif; ?>

</div>
