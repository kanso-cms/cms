<div class="list-body">
	<?php if (empty($articles)) : ?>
		<?php require($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Articles'.DIRECTORY_SEPARATOR.'list-empty.php'); ?>
	<?php else : ?>
		<?php foreach ($articles as $article) : ?>
			<?php require($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'Articles'.DIRECTORY_SEPARATOR.'list-item.php'); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>