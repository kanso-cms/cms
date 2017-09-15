<?php use kanso\framework\utility\Str; ?>
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
	            <a class="color-black p4 font-bolder" href="<?php echo the_tag_url($tag->id);?>" target="_blank">
	            	<?php echo $tag->name; ?>
	            </a>
	        </div>
	        <span class="color-gray">Slug:&nbsp;"<?php echo $tag->slug; ?>"&nbsp;-&nbsp;</span>
	        <a class="color-gray p5" href="/admin/posts/?tag=<?php echo $tag->id; ?>">
	           	with <?php echo $tag->article_count; ?> posts
	       	</a>
	       
	       	<div class="color-gray p5">
	       		<?php echo Str::reduce($tag->description, 100, '...'); ?>
	       	</div>
	       	<div class="taxonomy-edit-wrap collapsed" id="tag-edit-<?php echo $tag->id; ?>">
	       		<div class="roof-xs col-8">
	       			<form method="post" class="js-validation-form">
				        <div class="form-field row floor-xs">
				            <label for="tag_name_<?php echo $tag->id;?>">Name</label>
				            <input type="text" name="name" id="tag_name_<?php echo $tag->id;?>" placeholder="Tag name" value="<?php echo $tag->name; ?>" data-js-required="true">
				        </div>

				        <div class="form-field row floor-xs">
				            <label for="tag_slug_<?php echo $tag->id;?>">Slug</label>
				            <input type="text" name="slug" id="tag_slug_<?php echo $tag->id;?>" placeholder="tag-slug" value="<?php echo $tag->slug; ?>" data-js-required="true" class="js-mask-alpha-dash">
				        </div>

				        <div class="form-field row floor-xs">
				            <label for="tag_description_<?php echo $tag->id;?>">Description</label>
				            <textarea name="description" id="tag_description_<?php echo $tag->id;?>" style="resize: vertical;" rows="5"><?php echo $tag->description;?></textarea>
				        </div>
				        
				        <input type="hidden" name="tags[]"       value="<?php echo $tag->id;?>">
				        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				        <input type="hidden" name="bulk_action"  value="update">

				        <button type="button" class="btn js-collapse" data-collapse-target="tag-edit-<?php echo $tag->id; ?>">Cancel</button>
				        <button type="submit" class="btn btn-success">Update Tag</button>
				    </form>
	       		</div>
	       	</div>
		</div>
		<div class="media-right nowrap">
			<?php if ($tag->id !== 1) : ?>
			<a href="#" class="btn btn-pure btn-xs tooltipped tooltipped-n js-collapse" data-collapse-target="tag-edit-<?php echo $tag->id; ?>" data-tooltip="Quick Edit tag">
				<span class="glyph-icon glyph-icon-pencil-square-o icon-md"></span>
			</a>
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
