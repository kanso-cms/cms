<?php the_header(); ?>
		
		<!-- POST CONTENT -->

		<?php if ( have_posts() ) : ?>

			<div class="clear">

				<div class="col-12 clear">

					<h1 class="text-center font-600"><?php the_title();?></h1>

					<hr>

					<div class="clear">
						<?php the_content(); ?>
					</div>

				</div>

			</div>
		
		<?php endif; ?>

		<?php the_sidebar(); ?>


<?php the_footer(); ?>