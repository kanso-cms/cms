<!-- LIST ITEM -->
<div class="row list-row">
	<div class="media">
		<div class="media-left">
			<div class="form-field">    
		        <span class="checkbox checkbox-primary">
		            <input type="checkbox" class="js-bulk-action-cb" name="cats[]" id="cb-cat-<?php echo $cat['id']; ?>" value="<?php echo $cat['id']; ?>" />
		            <label for="cb-cat-<?php echo $cat['id']; ?>"></label>
		        </span>
		    </div>
		</div>
		<div class="media-body gutter-md">
			<div>
	            <a class="color-black p4 font-bolder" href="<?php echo $cat['permalink']; ?>" target="_blank">
	            	<?php echo $cat['name']; ?>
	            </a>
	        </div>
	        
	        <span class="color-gray p5">
	        	With <?php echo $cat['article_count']; ?> articles
	        </span>
		</div>
		<div class="media-right nowrap">
			<a href="#" class="btn btn-pure btn-xs tooltipped tooltipped-n" data-tooltip="Clear category" onclick="document.getElementById('clear-form-<?php echo $cat['id'];?>').submit()">
				<span class="glyph-icon glyph-icon-chain-broken icon-md"></span>
			</a>
	        <a href="#" class="btn btn-pure btn-xs tooltipped tooltipped-n js-confirm-delete" data-item="category" data-form="delete-form-<?php echo $cat['id'];?>" data-tooltip="Delete category">
				<span class="glyph-icon glyph-icon-trash-o icon-md"></span>
			</a>
			<form method="post" id="clear-form-<?php echo $cat['id'];?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="clear">
				<input type="hidden" name="cats[]"      value="<?php echo $cat['id'];?>">
			</form>
			<form method="post" id="delete-form-<?php echo $cat['id'];?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="delete">
				<input type="hidden" name="cats[]"      value="<?php echo $cat['id'];?>">
			</form>

		</div>
	</div>
</div>
