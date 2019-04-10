<tr>
	<th>
		<div class="form-field">    
	        <span class="checkbox checkbox-primary">
	            <input type="checkbox" class="js-bulk-action-cb" name="orders[]" id="cb-order-<?php echo $order['id']; ?>" value="<?php echo $order['id']; ?>" />
	            <label for="cb-order-<?php echo $order['id']; ?>"></label>
	        </span>
	    </div>
	</th>
	<th><?php echo $order['id']; ?></th>
	<th><?php echo $order['bt_transaction_id']; ?></th>
	<th><?php echo $order['address']['first_name'] . ' ' . $order['address']['last_name']; ?></th>
	<th><?php echo $order['address']['email']; ?></th>
	<th><?php echo $order['address']['telephone']; ?></th>
	<th><?php echo date('l, F d, Y - h:ia', $order['date']); ?></th>
	<th><?php echo strtoupper($order['status']); ?></th>
	<th>$<?php echo number_format($order['sub_total'], 2, '.', ''); ?></th>
	<th style="text-align: center;">
		<a href="/admin/invoices/<?php echo $order['bt_transaction_id']; ?>/" target="_blank" class="tooltipped tooltipped-n" data-tooltip="View invoice">
			<span class="glyph-icon glyph-icon-print icon-md"></span>
		</a>
	</th>
	<th style="text-align: center;">
		<a href="#" class="tooltipped tooltipped-n" data-tooltip="View address"  onclick="document.getElementById('address-view-<?php echo $order['id']; ?>').classList.toggle('hidden');">
			<span class="glyph-icon glyph-icon-address-book icon-md"></span>
		</a>
	</th>
	<th style="text-align: center;">
		<a href="#" class="tooltipped tooltipped-n" data-tooltip="Mark as shipped" onclick="document.getElementById('shipping-view-<?php echo $order['id']; ?>').classList.toggle('hidden');">
			<span class="glyph-icon glyph-icon-truck icon-md"></span>
		</a>
	</th>
	<th>
		<a href="#" class="btn btn-pure btn-xs btn-danger tooltipped tooltipped-n js-confirm-delete" data-item="order" data-form="delete-form-<?php echo $order['id']; ?>" data-tooltip="Delete order">
			<span class="glyph-icon glyph-icon-trash-o icon-md"></span>
		</a>
		<form method="post" id="delete-form-<?php echo $order['id']; ?>" style="display: none">
			<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
			<input type="hidden" name="bulk_action" value="delete">
			<input type="hidden" name="orders[]" value="<?php echo $order['id']; ?>">
		</form>
	</th>
</tr>
<tr class="hidden" id="address-view-<?php echo $order['id']; ?>" style="width:100%;">
	<th width="100%" colspan="13">
		<?php echo $order['address']['first_name']; ?> <?php echo $order['address']['last_name']; ?><br>
		<?php echo $order['address']['street_address_1']; ?> <?php echo $order['address']['street_address_2']; ?><br>
		<?php echo $order['address']['suburb']; ?> <?php echo $order['address']['state']; ?> <?php echo $order['address']['zip_code']; ?>
	</th>
</tr>
<tr class="hidden" id="shipping-view-<?php echo $order['id']; ?>" style="width:100%;">
	<th width="100%" colspan="13">
		<div class="form-field inline-block">
			<input type="text" name="tracking_codes[]" value="<?php echo $order['tracking_code']; ?>" oninput="document.getElementById('switch-status-tracking-<?php echo $order['id']; ?>').value = this.value;" placeholder="Add tracking code" />
	    </div>
	    <form method="post" id="status-switch-form-<?php echo $order['id']; ?>" style="display: none">
			<input type="hidden" name="access_token"  value="<?php echo $ACCESS_TOKEN; ?>">
			<input type="hidden" name="bulk_action"   value="<?php echo ($order['shipped'] > 0) ? 'received' : 'shipped'; ?>">
			<input type="hidden" name="tracking_code" value="<?php echo $order['tracking_code']; ?>" id="switch-status-tracking-<?php echo $order['id']; ?>">
			<input type="hidden" name="orders[]"      value="<?php echo $order['id']; ?>">
		</form>
		<button class="btn btn-success" type="button" onclick="document.getElementById('status-switch-form-<?php echo $order['id']; ?>').submit()"><?php echo ($order['shipped'] > 0) ? 'Mark As Received' : 'Mark As Shipped'; ?></button>
	</th>
</tr>