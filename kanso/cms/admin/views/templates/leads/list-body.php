<?php if (empty($visitors)) : ?>
	<?php require 'list-empty.php'; ?>
<?php else : ?>
	<div class="floor-sm">
		<table class="table table-bordered"> 
			<thead>
				<tr>
					<th>ID</th>
					<th>Visitor ID</th>
					<th>Name</th>
					<th>Email</th>
					<th>Last Active</th>
					<th>Status</th>
					<th>Visit Count</th>
					<th>Medium</th>
					<th>Channel</th>
					<th>Visits</th>
					<th>Profile</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($visitors as $i => $visitor) : ?>
					<?php require 'list-item.php'; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>