<?php
/**
 * The main template file
 *
 * This is the main template file for the homepage.
 * This template will be loaded whenever a valid request
 * for homepage is made.
 * e.g http://example.com/ or http://example.com/page/3/
 *
 */
 the_header(); ?>

		
		<?php if ( have_posts() ) : ?>
			
			<div class="clear">
			
			<?php while ( have_posts() ) : the_post();  ?>

				<div class="col-12 clear">

					<a href="<?php the_permalink();?>" class="block">
						<div class="img-responsive bg-img-small" style="background:url(<?php the_post_thumbnail();?>)no-repeat center center;background-size: cover;"></div>
					</a>

					<h3 class="font-600"><?php the_title();?></h3>

					<p class="small"><?php the_time('M d, Y'); ?></p>

					<p><?php the_excerpt(); ?> </p>

					<a href="<?php the_permalink();?>" class="button">Read More</a>
					
					<hr/>

				</div>

			<?php endwhile;?>

			</div>
		
		<?php endif; ?>
		<!-- Loop End -->

		<!-- Pagination -->
		<div class="clear pagination">
			<div class="col-12">
				<ul>
					<?php pagination_links(); ?>
				</ul>
			</div>
		</div>

		<!-- Sidebar -->
		<?php the_sidebar(); ?>


<?php the_footer(); ?>