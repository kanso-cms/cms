<?php
/**
 * The static page template file
 *
 * This file will be loaded whenever a valid
 * request for a static post (post with post type "page") is made.
 *
 */
the_header(); ?>
		
<?php if ( have_posts() ) : the_post(); ?>

<div class="col col-12 no-gutter tablet-col tablet-huge-gutter tablet-col-9 tablet-right-gutter">

	<?php if (has_post_thumbnail()) :?>
	
	<img src="<?php the_post_thumbnail(); ?>" class="col-12" />
	
	<?php endif;?>

	<h1><?php the_title();?></h1>

	<hr>

	<article class="single-entry row">
		<?php the_content(); ?>
	</article>

</div>

<?php endif; ?>

<?php the_sidebar(); ?>

<?php the_footer(); ?>