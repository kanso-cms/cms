<?php if (empty($emails)) : ?>
	<?php require 'list-empty.php'; ?>
<?php else : ?>
	<div class="floor-sm">
		<table class="table table-bordered"> 
			<thead>
				<tr>
					<th>Recipient</th>
					<th>Subject</th>
					<th>From</th>
					<th>From Email</th>
					<th>Sent</th>
					<th>Format</th>
					<th>Preview</th>
					<th>Resend</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($emails as $i => $email) : ?>
					<tr><?php require 'list-item.php'; ?></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

		
