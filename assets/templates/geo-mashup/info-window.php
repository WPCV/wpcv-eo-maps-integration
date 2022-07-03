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
add_filter( 'post_thumbnail_size', [ 'GeoMashupQuery', 'post_thumbnail_size' ] );

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

			// Init feature image array.
			$feature_image = [
				'exists'    => false,
				'class'     => '',
				'thumbnail' => '',
			];

			// Maybe fill out array with values.
			if ( has_post_thumbnail() ) {
				$feature_image['exists']    = true;
				$feature_image['class']     = ' has_feature_image';
				$feature_image['thumbnail'] = get_the_post_thumbnail( get_the_ID(), 'medium' );
			}

			/**
			 * Filter the feature image array.
			 *
			 * @since 1.0
			 *
			 * @param array $feature_image The array of feature image data.
			 * @param int $post_id The numeric ID of the WordPress Post.
			 */
			$feature_image = apply_filters( 'wpcv_eo_maps/info_window/thumbnail', $feature_image, get_the_ID() );

			// Get Post Type.
			$post_type_class = ' post_type_' . get_post_type( get_the_ID() );

			?>

			<div class="location-post<?php echo $multiple_items_class . $feature_image['class'] . $post_type_class; ?>">

				<div class="post_header">

					<?php if ( true === $feature_image['exists'] ) : ?>
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="feature-link">
							<?php echo $feature_image['thumbnail']; ?>
						</a>
					<?php endif; ?>

					<div class="post_header_text">
						<h2><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
					</div><!-- /.post_header_text -->

				</div><!-- /.post_header -->

				<?php if ( apply_filters( 'wpcv_eo_maps/info_window/content', true, get_the_ID() ) ) : ?>
					<?php if ( 1 === (int) $wp_query->post_count ) : ?>

						<div class="storycontent">
							<p>
							<?php

							echo apply_filters(
								'wpcv_eo_maps/info_window/content',
								wp_strip_all_tags( get_the_excerpt() ),
								get_the_ID()
							);

							?>
							</p>
							<?php if ( apply_filters( 'wpcv_eo_maps/info_window/more_link', true, get_the_ID() ) ) : ?>
								<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="more-link"><?php esc_html_e( 'Read more', 'wpcv-eo-maps-integration' ); ?></a>
							<?php endif; ?>
						</div>

					<?php else : ?>

						<?php if ( false === $feature_image['exists'] ) : ?>
							<div class="storycontent">
								<?php if ( apply_filters( 'wpcv_eo_maps/info_window/more_link', true, get_the_ID() ) ) : ?>
									<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="more-link"><?php esc_html_e( 'Read more', 'wpcv-eo-maps-integration' ); ?></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>

					<?php endif; ?>
				<?php endif; ?>

			</div><!-- /.location-post -->

		<?php endwhile; ?>

	<?php else : ?>

		<h2 class="center"><?php esc_html_e( 'Not Found', 'wpcv-eo-maps-integration' ); ?></h2>
		<p class="center"><?php esc_html_e( 'Sorry, but we can’t find what you are looking for.', 'wpcv-eo-maps-integration' ); ?></p>

	<?php endif; ?>

</div>
