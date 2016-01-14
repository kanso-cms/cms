<?php
/**
 * List template file
 *
 * This is the main template file for post listings by
 * author, tag and author.
 * This template will be loaded whenever a valid request
 * for an author listing, tag listing and category listing
 * e.g http://example.com/tags/css/ or http://example.com/tags/css/page/3/
 *
 */
the_header(); ?>
			
	<div class="col col-12 no-gutter tablet-col tablet-huge-gutter tablet-col-9 tablet-right-gutter">
		
		<!-- BEGIN LOOP -->
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post();  ?>

			<div class="col-12 no-gutter clearfix">

				<?php if (has_post_thumbnail()) :?>

				<a href="<?php the_permalink();?>" class="block hide-overflow" style="max-height:250px;">
					<img src="<?php the_post_thumbnail(); ?>" class="col-12" />
				</a>
				
				<?php endif;?>

				<a class="block" href="<?php the_permalink();?>"><h3 class="font-600"><?php the_title();?></h3></a>

				<p class="info-text"><?php the_time('M d, Y'); ?></p>

				<p><?php echo customExcerpt(150, '...') ?> </p>

				<a href="<?php the_permalink();?>" class="button primary">Read More&nbsp;&nbsp;»</a>
				
				<br><br><br>

			</div>

		<?php endwhile;

		else : ?>

			<div class="info message">
	            <div class="message-icon">
	                <span class="ion">⊝</span>
	            </div>
	            <div class="message-body">
	                <p>Sorry, there are no posts to display.</p>
	            </div>
	        </div>

        <?php endif; ?>

	</div>

	<!-- SIDEBAR -->
	<?php the_sidebar(); ?>

	<!-- Pagination -->
	<div class="col col-12 no-gutter">
		<div class="row pagination">
			<ul>
				<?php pagination_links(); ?>
			</ul>
		</div>
	</div>


<?php the_footer(); ?>