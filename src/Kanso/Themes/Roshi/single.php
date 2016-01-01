<?php the_header(); ?>
		
		<!-- POST CONTENT -->

		<?php if ( have_posts() ) : ?>

			<div class="clear">

				<div class="col-12 clear">

					<div class="img-responsive bg-img-medium" style="background:url(<?php the_post_thumbnail();?>)no-repeat center center;background-size: cover;"></div>

					<h1 class="text-center font-600"><?php the_title();?></h1>

					<p class="small">
						<?php the_time('M d, Y'); ?> • 
						<a href="<?php the_category_url();?>"><?php the_category();?></a> • 
						By <a href="<?php the_author_url();?>"><?php the_author();?></a> 
					</p>

					<hr>

					<div class="clear">
						<?php the_content(); ?>
					</div>
					
					<hr>
					
					<div class="clear">
						<h2 class="font-600">About The Author</h2>
						<img width="60" height="60" src="<?php the_author_thumbnail('small');?>" />
						<p>
							<?php the_author_bio();?>
							<br>
							<a class="button" href="<?php the_author_twitter();?>">Twitter</a>
							<a class="button" href="<?php the_author_facebook();?>">Facebook</a>
							<a class="button" href="<?php the_author_google();?>">Facebook</a>
						</p>
					</div>

					<hr>

					<div class="clear comments-wrap">
						<?php if (comments_open()) :?>
							<h2 class="font-600">Leave A Comment</h2>
								
							<?php comment_form(); ?>
							
							<hr>

							<h2 class="font-600">Comments</h2>

							<?php if (has_comments()) :?>
								<?php display_comments(); ?>
							<?php endif; ?>

							<hr>
							
						<?php endif; ?>
					</div>

				</div>

			</div>
		
		<?php endif; ?>

		<?php the_sidebar(); ?>


<?php the_footer(); ?>