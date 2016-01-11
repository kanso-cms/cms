<!-- FOOTER SIDEBAR -->
<div class="clear">

	<!-- Search -->
	<div class="col-6-desktop gutter-large">

		<h4 class="text-center">Site Search</h4>

		<?php get_search_form(); ?>

	</div>

	<!-- Categories -->
	<div class="col-3-desktop">

		<h4 class="text-center">Categories</h4>

		<?php $categories = all_the_categories(); ?>

		<?php if (!empty($categories) && count($categories) > 1 ) : ?>

			<ul class="center-element text-center list-inline">
				<?php foreach ($categories as $category) : ?>
					<?php if ((int)$category['id'] !== 1 ) :?>
						<li><a href="<?php the_category_url($category['id']);?>"><?php echo $category['name'];?></a></li>
					<?php endif; ?>
				<?php endforeach;?>
			</ul>

		<?php endif; ?>

	</div>

	<!-- Tags -->
	<div class="col-3-desktop">

		<h4 class="text-center">Tags</h4>
		
		<?php $tags = all_the_tags(); ?>

		<?php if (!empty($tags) && count($tags) > 1 ) : ?>

			<ul class="center-element text-center list-inline">
				<?php foreach ($tags as $tag) : ?>
					<?php if ((int)$tag['id'] !== 1 ) :?>
						<li><a href="<?php the_tag_url($tag['id']);?>"><?php echo $tag['name'];?></a></li>
					<?php endif; ?>
				<?php endforeach;?>
			</ul>

		<?php endif; ?>

	</div>

</div>
