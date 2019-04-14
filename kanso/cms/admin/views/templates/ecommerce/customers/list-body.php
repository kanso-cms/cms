<?php if (empty($customers)) : ?>
	<?php require 'list-empty.php'; ?>
<?php else : ?>
	<div class="floor-sm">
		<table class="table table-bordered"> 
			<thead>
				<tr>
					<th></th>
					<th>ID</th>
					<th>Name</th>
					<th>Email</th>
					<th>Status</th>
					<th>Set Status</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($customers as $i => $customer) : ?>
					<?php require 'list-item.php'; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>