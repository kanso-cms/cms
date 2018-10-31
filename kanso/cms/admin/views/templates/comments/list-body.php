<div class="list-body">
	<?php if (empty($comments)) : ?>
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'comments' . DIRECTORY_SEPARATOR . 'list-empty.php'); ?>
	<?php else : ?>
		<?php foreach ($comments as $comment) : ?>
			<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'comments' . DIRECTORY_SEPARATOR . 'list-item.php'); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>