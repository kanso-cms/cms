<?php
/**
 * The post page template file
 *
 * This file will be loaded whenever a valid
 * request for a post (article) is made.
 *
 */
the_header(); ?>
		
<?php if ( have_posts() ) : ?>

<div class="col col-12 no-gutter tablet-col tablet-huge-gutter tablet-col-9 tablet-right-gutter">

	<?php if (has_post_thumbnail()) :?>
	
	<img src="<?php echo the_post_thumbnail_src(); ?>" class="col-12" />
	
	<?php endif;?>

	<h1 class="text-center font-600"><?php echo the_title();?></h1>

	<p class="small text-center">
		<?php the_time('M d, Y'); ?> • 
		<a href="<?php echo the_category_url();?>"><?php echo the_category_name();?></a> • 
		By <a href="<?php echo the_author_url();?>"><?php echo the_author_name();?></a> 
	</p>

	<hr>

	<article class="single-entry row">
		<?php echo the_content(); ?>
	</article>
	
	<div class="row">
		<h2>About The Author</h2>
		<hr>
		
		<?php echo display_thumbnail(the_author_thumbnail(), 'small', 60, 60); ?>
		
		<p><?php echo the_author_bio();?></p>

		<a class="button default" href="<?php echo the_author_twitter();?>">Twitter</a>
		<a class="button default" href="<?php echo the_author_facebook();?>">Facebook</a>
		<a class="button default" href="<?php echo the_author_google();?>">Google</a>
	</div>

	<div class="row comments-wrap">

		<?php if (comments_open()) :?>
		
			<?php if (has_comments()) :?>
				<h2>Comments</h2>
				<hr>
				<?php display_comments(); ?>
			<?php endif; ?>

			<h2>Leave A Comment</h2>
			<hr>
			<?php echo comment_form(); ?>
			
		<?php endif; ?>

	</div>

</div>

<?php endif; ?>

<?php the_sidebar(); ?>

<?php the_footer(); ?>