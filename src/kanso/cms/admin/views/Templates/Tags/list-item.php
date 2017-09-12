<!-- LIST ITEM -->
<div class="row list-row">
	<div class="media">
		<?php if ($tag->id !== 1) : ?>
		<div class="media-left">
			<div class="form-field">    
		        <span class="checkbox checkbox-primary">
		            <input type="checkbox" class="js-bulk-action-cb" name="tags[]" id="cb-tag-<?php echo $tag->id; ?>" value="<?php echo $tag->id; ?>" />
		            <label for="cb-tag-<?php echo $tag->id; ?>"></label>
		        </span>
		    </div>
		</div>
		<?php endif; ?>
		<div class="media-body gutter-md">
			<div>
	            <a class="color-black p4 font-bolder" href="/admin/posts/?category=<?php echo $tag->id; ?>" target="_blank">
	            	<?php echo $tag->name; ?>
	            </a>
	        </div>
	        
	        <span class="color-gray p5">
	        	With <?php echo $tag->article_count; ?> posts
	        </span>
		</div>
		<div class="media-right nowrap">
			<?php if ($tag->id !== 1) : ?>
			<a href="#" class="btn btn-pure btn-xs tooltipped tooltipped-n" data-tooltip="Clear tag" onclick="document.getElementById('clear-form-<?php echo $tag->id;?>').submit()">
				<span class="glyph-icon glyph-icon-chain-broken icon-md"></span>
			</a>
	        <a href="#" class="btn btn-pure btn-xs tooltipped tooltipped-n js-confirm-delete" data-item="tag" data-form="delete-form-<?php echo $tag->id;?>" data-tooltip="Delete tag">
				<span class="glyph-icon glyph-icon-trash-o icon-md"></span>
			</a>
			<form method="post" id="clear-form-<?php echo $tag->id;?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="clear">
				<input type="hidden" name="tags[]"      value="<?php echo $tag->id;?>">
			</form>
			<form method="post" id="delete-form-<?php echo $tag->id;?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="delete">
				<input type="hidden" name="tags[]"      value="<?php echo $tag->id;?>">
			</form>
			<?php endif;?>
		</div>
	</div>
</div>
