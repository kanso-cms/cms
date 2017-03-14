<div class="row list-row">
	<div class="media">
		<div class="media-left">
			<div class="form-field">    
		        <span class="checkbox checkbox-primary">
		            <input type="checkbox" class="js-bulk-action-cb" name="posts[]" id="cb-article-<?php echo $article['id']; ?>" value="<?php echo $article['id']; ?>" />
		            <label for="cb-article-<?php echo $article['id']; ?>"></label>
		        </span>
		    </div>
		</div>
		<div class="media-body gutter-md">
			<div>
	            <a class="color-black p4 font-bolder" href="<?php echo $article['permalink']; ?>" target="_blank">
	            	<?php echo $article['title']; ?>
	            </a>
	        </div>
	        
	        <span class="color-gray">
	        	In <a class="color-gray text-underline" href="/admin/articles?category=<?php echo $article['category_id']; ?>">
					<?php echo $article['category']['name'];?>
				</a>
				<span class="p6">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	<?php echo $article['comment_count']; ?> Comments
	        	<span class="p6">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	<?php echo Kanso\Utility\Humanizer::timeAgo($article['created']); ?> ago
	        	<span class="p6">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	By <a class="color-gray text-underline" href="/admin/articles?author=<?php echo $article['author']['id']; ?>">
					<?php echo $article['author']['name'];?>
				</a>
	        </span>
	        <div class="margin-xs-n">
	        	<span class="glyph-icon glyph-icon-tags color-gray"></span>&nbsp;&nbsp;
		        <?php foreach ($article['tags'] as $i => $_tag) : ?>
		        	<a class="label label-outline" style="opacity: 0.5" href="/admin/articles?tag=<?php echo $_tag['id']; ?>">
						<?php echo $_tag['name'];?>
					</a>&nbsp;&nbsp;
		        <?php endforeach;?>
		    </div>
		</div>
		<div class="media-right nowrap">
			<div class="form-field inline-block">
				<span class="tooltipped tooltipped-n" data-tooltip="Published">
					<input onchange="document.getElementById('status-switch-form-<?php echo $article['id'];?>').submit()" type="checkbox" id="status-switch-<?php echo $article['id'];?>" name="posts[]" value="<?php echo $article['id'];?>" class="switch switch-success" <?php if ($article['status'] === 'published') echo 'checked';?>>	
					<label for="status-switch-<?php echo $article['id'];?>"></label>
				</span>
	        </div>
	        <a href="/admin/writer/?id=<?php echo $article['id'];?>" class="btn btn-pure btn-xs tooltipped tooltipped-n" data-tooltip="Edit page" style="margin-top: 6px;">
				<span class="glyph-icon glyph-icon-font icon-md"></span>
			</a>
	        <a href="#" class="btn btn-pure btn-xs btn-danger tooltipped tooltipped-n js-confirm-delete" data-item="page" data-form="delete-form-<?php echo $article['id'];?>" data-tooltip="Delete article" style="margin-top: 6px;">
				<span class="glyph-icon glyph-icon-trash-o icon-md"></span>
			</a>
			<form method="post" id="status-switch-form-<?php echo $article['id'];?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="<?php echo ($article['status'] === 'published') ? 'draft' : 'published'; ?>">
				<input type="hidden" name="posts[]"      value="<?php echo $article['id'];?>">
			</form>
			<form method="post" id="delete-form-<?php echo $article['id'];?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="bulk_action"  value="delete">
				<input type="hidden" name="posts[]"      value="<?php echo $article['id'];?>">
			</form>

		</div>
	</div>
</div>
