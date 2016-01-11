<?php the_header(); ?>

		<!-- Loop Start -->
		<?php if ( have_posts() ) : ?>
			
			<div class="clear">

				<div class="col-12 clear">
					
					<h1> Search Results for: "<?php search_query();?>"</h1>

				</div>
			
			<?php while ( have_posts() ) :   ?>

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

			<?php the_post(); endwhile;?>

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