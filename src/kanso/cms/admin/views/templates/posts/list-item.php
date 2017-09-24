<?php
	use kanso\framework\utility\Humanizer;
	use kanso\framework\utility\Str;
?>
<div class="row list-row">
	<div class="media">
		<div class="media-left">
			<div class="form-field">    
		        <span class="checkbox checkbox-primary">
		            <input type="checkbox" class="js-bulk-action-cb" name="posts[]" id="cb-article-<?php echo $article->id; ?>" value="<?php echo $article->id; ?>" />
		            <label for="cb-article-<?php echo $article->id; ?>"></label>
		        </span>
		    </div>
		</div>
		<div class="media-body gutter-md">
			<div>
	            <a class="h5" href="<?php echo the_permalink($article->id); ?><?php echo $article->status === 'published' ? '' : '?draft';?>" target="_blank">
	            	<?php echo $article->title; ?>
	            </a>
	        </div>

	        <span class="color-gray">
	        	<span>Slug:&nbsp;"<?php echo Str::getAfterLastChar(rtrim($article->slug, '/'), '/'); ?>"&nbsp;-&nbsp;</span>

	        	In <a class="color-gray text-underline" href="/admin/<?php echo $postSlug;?>?category=<?php echo $article->category->id; ?>">
					<?php echo $article->category->name;?>
				</a>
				<span class="p6">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	<?php echo comments_number($article->id); ?> Comments
	        	<span class="p6">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	<?php echo Humanizer::timeAgo($article->created); ?> ago
	        	<span class="p6">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	By <a class="color-gray text-underline" href="/admin/<?php echo $postSlug;?>?author=<?php echo $article->author_id; ?>">
					<?php echo $article->author->name; ?>
				</a>
	        </span>
	        <div class="margin-xs-n">
	        	<span class="glyph-icon glyph-icon-tags color-gray"></span>&nbsp;&nbsp;
		        <?php foreach ($article->tags as $_tag) : ?>
		        	<a class="label label-outline" style="opacity: 0.5" href="/admin/<?php echo $postSlug;?>?tag=<?php echo $_tag->id; ?>">
						<?php echo $_tag->name;?>
					</a>&nbsp;&nbsp;
		        <?php endforeach;?>
		    </div>
		    <div class="color-gray p5 roof-xs">
	       		<?php echo Str::reduce($article->excerpt, 100, '...'); ?>
	       	</div>
	       	<div class="post-edit-wrap collapsed" id="post-edit-<?php echo $article->id; ?>">
	       		<div class="roof-xs col-8">
	       			<form method="post" class="js-validation-form">
				        <div class="form-field row floor-xs">
				            <label for="post_title_<?php echo $article->title;?>">Title</label>
				            <input type="text" name="title" id="post_title_<?php echo $article->id;?>" placeholder="Title" value="<?php echo $article->title; ?>" data-js-required="true">
				        </div>

				        <div class="form-field row floor-xs">
				            <label for="post_slug_<?php echo $article->id;?>">Slug</label>
				            <input type="text" name="slug" id="post_slug_<?php echo $article->id;?>" placeholder="post-slug" value="<?php echo Str::getAfterLastChar(rtrim($article->slug, '/'), '/'); ?>" data-js-required="true" class="js-mask-alpha-dash">
				        </div>

				        <div class="form-field row floor-xs">
				            <label for="post_excerpt_<?php echo $article->id;?>">Excerpt</label>
				            <textarea name="excerpt" id="post_excerpt_<?php echo $article->id;?>" style="resize: vertical;" rows="5"><?php echo $article->excerpt;?></textarea>
				        </div>
				        
				        <input type="hidden" name="posts[]"      value="<?php echo $article->id;?>">
				        <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				        <input type="hidden" name="bulk_action"  value="update">

				        <button type="button" class="btn js-collapse" data-collapse-target="post-edit-<?php echo $article->id; ?>">Cancel</button>
				        <button type="submit" class="btn btn-success">Update <?php echo ucfirst($postType);?></button>
				    </form>
	       		</div>
	       	</div>
		</div>
		<div class="media-right nowrap">
			<a href="#" class="btn btn-pure btn-xs tooltipped tooltipped-n js-collapse" data-collapse-target="post-edit-<?php echo $article->id; ?>" data-tooltip="Quick edit <?php echo strtolower($postType);?>">
				<span class="glyph-icon glyph-icon-pencil icon-md"></span>
			</a>
			<a href="/admin/writer/?id=<?php echo $article->id;?>" class="btn btn-pure btn-xs tooltipped tooltipped-n" data-tooltip="Open <?php echo strtolower($postType);?> in writer" style="margin-top: 6px;">
				<span class="glyph-icon glyph-icon-align-left icon-md"></span>
			</a>
			<div class="form-field inline-block">
				<span class="tooltipped tooltipped-n" data-tooltip="<?php echo ($article->status === 'published') ? 'Draft' : 'Published'; ?>">
					<input onchange="document.getElementById('status-switch-form-<?php echo $article->id;?>').submit()" type="checkbox" id="status-switch-<?php echo $article->id;?>" name="posts[]" value="<?php echo $article->id;?>" class="switch switch-success" <?php if ($article->status === 'published') echo 'checked';?>>	
					<label for="status-switch-<?php echo $article->id;?>"></label>
				</span>
	        </div>
	        <a href="#" class="btn btn-pure btn-xs btn-danger tooltipped tooltipped-n js-confirm-delete" data-item="post" data-form="delete-form-<?php echo $article->id;?>" data-tooltip="Delete <?php echo $postType;?>" style="margin-top: 6px;">
				<span class="glyph-icon glyph-icon-trash-o icon-md"></span>
			</a>
			<form method="post" id="status-switch-form-<?php echo $article->id;?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="<?php echo ($article->status === 'published') ? 'draft' : 'published'; ?>">
				<input type="hidden" name="posts[]"      value="<?php echo $article->id;?>">
			</form>
			<form method="post" id="delete-form-<?php echo $article->id;?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="delete">
				<input type="hidden" name="posts[]"      value="<?php echo $article->id;?>">
			</form>
		</div>
	</div>
</div>
