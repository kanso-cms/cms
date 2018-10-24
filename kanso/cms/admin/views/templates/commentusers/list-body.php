<div class="list-body">
	<?php if (empty($commenters)) : ?>
		<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'commentusers' . DIRECTORY_SEPARATOR . 'list-empty.php'); ?>
	<?php else : ?>
		<?php foreach ($commenters as $commenter) : ?>
			<?php require($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'commentusers' . DIRECTORY_SEPARATOR . 'list-item.php'); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>