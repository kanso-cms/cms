<tr>
	<th>
		<div class="form-field">    
	        <span class="checkbox checkbox-primary">
	            <input type="checkbox" class="js-bulk-action-cb" name="customers[]" id="cb-customer-<?php echo $customer['id']; ?>" value="<?php echo $customer['id']; ?>" />
	            <label for="cb-customer-<?php echo $customer['id']; ?>"></label>
	        </span>
	    </div>
	</th>
	<th><?php echo $customer['id']; ?></th>
	<th><?php echo $customer['name'];?></th>
	<th><?php echo $customer['email'];?></th>
	<th><?php echo strtoupper($customer['status']); ?></th>
	<th style="text-align: center;">
		<div class="form-field inline-block">
			<span class="tooltipped tooltipped-n" data-tooltip="<?php echo ($customer['status'] !== 'confirmed') ? 'Confirmed' : 'Pending'; ?>">
				<input onchange="document.getElementById('status-switch-form-<?php echo $customer['id'];?>').submit()" type="checkbox" id="status-switch-<?php echo $customer['id'];?>" name="customers[]" value="<?php echo $customer['id'];?>" class="switch switch-success" <?php if ($customer['status'] === 'confirmed') echo 'checked';?>>	
				<label for="status-switch-<?php echo $customer['id'];?>"></label>
			</span>
        </div>
		<form method="post" id="status-switch-form-<?php echo $customer['id'];?>" style="display: none">
			<input type="hidden" name="access_token"  value="<?php echo $ACCESS_TOKEN;?>">
			<input type="hidden" name="bulk_action"   value="<?php echo ($customer['status'] !== 'confirmed') ? 'confirmed' : 'pending'; ?>">
			<input type="hidden" name="customers[]"   value="<?php echo $customer['id'];?>">
		</form>
	</th>
	<th style="text-align: center;">
		<a href="#" class="btn btn-pure btn-xs btn-danger tooltipped tooltipped-n js-confirm-delete" data-item="customer" data-form="delete-form-<?php echo $customer['id'];?>" data-tooltip="Delete customer" style="margin-top: 6px;">
			<span class="glyph-icon glyph-icon-trash-o icon-md"></span>
		</a>
		<form method="post" id="delete-form-<?php echo $customer['id'];?>" style="display: none">
			<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
			<input type="hidden" name="bulk_action"  value="delete">
			<input type="hidden" name="customers[]"     value="<?php echo $customer['id'];?>">
		</form>
	</th>
</tr>
