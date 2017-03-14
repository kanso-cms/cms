<div class="list-body">
	<?php if (empty($users)) : ?>
		<?php require($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'CommentUsers'.DIRECTORY_SEPARATOR.'list-empty.php'); ?>
	<?php else : ?>
		<?php foreach ($users as $user) : ?>
			<?php require($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'CommentUsers'.DIRECTORY_SEPARATOR.'list-item.php'); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>