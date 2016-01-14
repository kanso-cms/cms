<?php
/**
 * The post page template file
 *
 * This file will be loaded whenever a valid
 * request for a post (article) is made.
 *
 */
the_header(); ?>
		
<?php if ( have_posts() ) : the_post(); ?>

<div class="col col-12 no-gutter tablet-col tablet-huge-gutter tablet-col-9 tablet-right-gutter">

	<?php if (has_post_thumbnail()) :?>
	
	<img src="<?php the_post_thumbnail(); ?>" class="col-12" />
	
	<?php endif;?>

	<h1 class="text-center font-600"><?php the_title();?></h1>

	<p class="small text-center">
		<?php the_time('M d, Y'); ?> • 
		<a href="<?php the_category_url();?>"><?php the_category();?></a> • 
		By <a href="<?php the_author_url();?>"><?php the_author();?></a> 
	</p>

	<hr>

	<article class="single-entry row">
		<?php the_content(); ?>
	</article>
	
	<div class="row">
		<h2>About The Author</h2>
		<hr>
		
		<img width="60" height="60" src="<?php the_author_thumbnail('small');?>" />
		
		<p><?php the_author_bio();?></p>

		<a class="button default" href="<?php the_author_twitter();?>">Twitter</a>
		<a class="button default" href="<?php the_author_facebook();?>">Facebook</a>
		<a class="button default" href="<?php the_author_google();?>">Google</a>
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
			<?php comment_form(); ?>
			
		<?php endif; ?>

	</div>

</div>

<?php endif; ?>

<?php the_sidebar(); ?>

<?php the_footer(); ?>