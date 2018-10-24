<div class="list-body">
	<?php if (empty($posts)) : ?>
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'list-empty.php'); ?>
	<?php else : ?>
		<?php foreach ($posts as $article) : ?>
			<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'posts' . DIRECTORY_SEPARATOR . 'list-item.php'); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>