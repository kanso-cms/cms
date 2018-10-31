<div class="list-body">
	<?php if (empty($tags)) : ?>
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'tags' . DIRECTORY_SEPARATOR . 'list-empty.php'); ?>
	<?php else : ?>
		<?php foreach ($tags as $tag) : ?>
			<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'tags' . DIRECTORY_SEPARATOR . 'list-item.php'); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>