<?php use kanso\framework\utility\Str; ?>
<div class="row floor-xs">
	<ul class="tab-nav tab-border">
		<?php foreach (admin_sirebar_links() as $key => $parent) : ?>
			<?php if ($key === 'e-commerce') : ?>
				<?php foreach ($parent['children'] as $childSlug => $child) : ?>
					<li><a href="<?php echo $child['link'];?>" <?php if ($active_tab === Str::getAfterLastChar(trim($child['link'], '/'), '/') ) echo 'class="active"';?>><?php echo $child['text'];?></a></li>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</div>