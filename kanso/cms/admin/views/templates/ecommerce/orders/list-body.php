<?php if (empty($orders)) : ?>
	<?php require 'list-empty.php'; ?>
<?php else : ?>
	<div class="floor-sm">
		<table class="table table-bordered"> 
			<thead>
				<tr>
					<th></th>
					<th>ID</th>
					<th>Reference</th>
					<th>Name</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Date</th>
					<th>Status</th>
					<th>Price</th>
					<th>Invoice</th>
					<th>Address</th>
					<th>Shipping</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($orders as $i => $order) : ?>
					<?php require 'list-item.php'; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>