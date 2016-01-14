<?php
/**
 * Sidebar template file
 *
 * This is the template file for the sidebar.
 * This template will be loaded whenever the_sidebar() is called.
 *
 */
?>
<div class="col col-12 no-gutter tablet-col tablet-col-3 tablet-huge-gutter tablet-left-gutter">

	<!-- Search -->
	<div class="row">

		<h4>Site Search</h4>

		<?php get_search_form(); ?>

		<hr>

	</div>

	<!-- Categories -->
	<div class="row">

		<h4>Categories</h4>

		<?php $categories = all_the_categories(); ?>

		<?php if (!empty($categories) && count($categories) > 1 ) : ?>

			<ul class="list-bullet">
				<?php foreach ($categories as $category) : ?>
					<?php if ((int)$category['id'] !== 1 ) :?>
						<li><a href="<?php the_category_url($category['id']);?>"><?php echo $category['name'];?></a></li>
					<?php endif; ?>
				<?php endforeach;?>
			</ul>

		<?php endif; ?>

		<hr>

	</div>

	<!-- Tags -->
	<div class="row">

		<h4>Tags</h4>
		
		<?php $tags = all_the_tags(); ?>

		<?php if (!empty($tags) && count($tags) > 1 ) : ?>

			<ul class="list-bullet">
				<?php foreach ($tags as $tag) : ?>
					<?php if ((int)$tag['id'] !== 1 ) :?>
						<li><a href="<?php the_tag_url($tag['id']);?>"><?php echo $tag['name'];?></a></li>
					<?php endif; ?>
				<?php endforeach;?>
			</ul>

		<?php endif; ?>

		<hr>

	</div>

</div>
