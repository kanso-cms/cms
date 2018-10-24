<div class="list-body">
	<?php if (empty($categories)) : ?>
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'categories' . DIRECTORY_SEPARATOR . 'list-empty.php'); ?>
	<?php else : ?>
		<?php foreach ($categories as $category) : ?>
			<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'categories' . DIRECTORY_SEPARATOR . 'list-item.php'); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>