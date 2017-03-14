<div class="list-body">
	<?php if (empty($cats)) : ?>
		<?php require($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Categories'.DIRECTORY_SEPARATOR.'list-empty.php'); ?>
	<?php else : ?>
		<?php foreach ($cats as $cat) : ?>
			<?php require($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Categories'.DIRECTORY_SEPARATOR.'list-item.php'); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>