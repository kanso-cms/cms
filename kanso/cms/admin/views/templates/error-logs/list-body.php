<div class="list-body">
	<?php if (empty($logs)) : ?>
		<?php require 'list-empty.php'; ?>
	<?php else : ?>
		<div class="error-logs-list row">
			<pre><code>
			<?php foreach ($logs as $i => $line) : $i = $i+1; ?>
				<?php require 'list-item.php'; ?>
			<?php endforeach; ?>
			</code></pre>
		</div>
	<?php endif; ?>
</div>