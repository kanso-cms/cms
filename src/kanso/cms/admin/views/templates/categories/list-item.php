<!-- LIST ITEM -->
<div class="row list-row">
	<div class="media">
		<div class="media-left">
			<?php if ($category->id !== 1) : ?>
			<div class="form-field">    
		        <span class="checkbox checkbox-primary">
		            <input type="checkbox" class="js-bulk-action-cb" name="tags[]" id="cb-tag-<?php echo $category->id; ?>" value="<?php echo $category->id; ?>" />
		            <label for="cb-tag-<?php echo $category->id; ?>"></label>
		        </span>
		    </div>
		    <?php endif; ?>
		</div>
		<div class="media-body gutter-md">
			<div>
	            <a class="color-black p4 font-bolder" href="<?php echo the_category_url($category->id);?>" target="_blank">
	            	<?php echo $category->name; ?>
	            </a>
	        </div>
	        
	        <a class="color-gray p5" href="/admin/posts/?category=<?php echo $category->id; ?>">
	           	With <?php echo $category->article_count; ?> posts
	       	</a>
		</div>
		<div class="media-right nowrap">
			<?php if ($category->id !== 1) : ?>
			<a href="#" class="btn btn-pure btn-xs tooltipped tooltipped-n" data-tooltip="Clear category" onclick="document.getElementById('clear-form-<?php echo $category->id;?>').submit()">
				<span class="glyph-icon glyph-icon-chain-broken icon-md"></span>
			</a>
	        <a href="#" class="btn btn-pure btn-xs tooltipped tooltipped-n js-confirm-delete" data-item="category" data-form="delete-form-<?php echo $category->id;?>" data-tooltip="Delete category">
				<span class="glyph-icon glyph-icon-trash-o icon-md"></span>
			</a>
			<form method="post" id="clear-form-<?php echo $category->id;?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="clear">
				<input type="hidden" name="tags[]"      value="<?php echo $category->id;?>">
			</form>
			<form method="post" id="delete-form-<?php echo $category->id;?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="delete">
				<input type="hidden" name="tags[]"      value="<?php echo $category->id;?>">
			</form>
			<?php endif; ?>
		</div>
	</div>
</div>
