<?php

use kanso\framework\utility\Humanizer;

$statusColor = '';
if ($comment->status === 'approved') {
	$statusColor = 'success';
}
elseif ($comment->status === 'pending') {
	$statusColor = 'info';
}
if ($comment->status === 'spam') {
	$statusColor = 'warning';
}
if ($comment->status === 'deleted') {
	$statusColor = 'danger';
}

?>
<!-- LIST ITEM -->
<div class="row list-row">
	<div class="media">
		<div class="media-left">
			<div class="form-field">    
		        <span class="checkbox checkbox-primary">
		            <input type="checkbox" class="js-bulk-action-cb" name="comments[]" id="cb-comment-<?php echo $comment->id; ?>" value="<?php echo $comment->id; ?>" />
		            <label for="cb-comment-<?php echo $comment->id; ?>"></label>
		        </span>
		    </div>
		</div>
		
		<div class="media-body gutter-md">
			<div class="floor-xs">
				<span class="status status-<?php echo $statusColor; ?> tooltipped tooltipped-n" data-tooltip="<?php echo ucfirst($comment->status); ?>"></span>
	        	<span>&nbsp;&nbsp;</span>
	            <a class="color-black p4 font-bolder" href="<?php echo the_permalink($comment->post_id) . '#comment-' . $comment->id; ?>" target="_blank">
	            	<?php echo the_title($comment->post_id); ?>
	            </a>
	        </div>
	        
	        <span class="color-gray">
	        	By <a class="color-gray text-underline" href="/admin/comments?search=name:<?php echo urlencode($comment->name); ?>">
					<?php echo $comment->name; ?>
				</a>
				<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
				<a class="color-gray text-underline" href="/admin/comments?search=email:<?php echo urlencode($comment->email); ?>">
					<?php echo $comment->email; ?>
				</a>
	            <span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	            <a class="color-gray text-underline" href="/admin/comments?search=ip_address:<?php echo urlencode($comment->ip_address); ?>">
					<?php echo $comment->ip_address; ?>
				</a>
				<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
				<span class="color-gray">
	            	<?php echo Humanizer::timeAgo($comment->date); ?> ago
	            </span>
	            <div class="roof-xs floor-xs">
	            	<?php echo $comment->content; ?>
	            </div>
	        </span>
		</div>

		<div class="media-right nowrap">
			<form class="inline-block" method="post" id="status-form-<?php echo $comment->id; ?>">
				<div class="form-field" style="width: 130px;">
			    	<select name="bulk_action" onchange="document.getElementById('status-form-<?php echo $comment->id; ?>').submit()">
						<option value="approved" <?php if ($comment->status === 'approved') echo 'selected'; ?>>Approved</option>
						<option value="pending" <?php if ($comment->status === 'pending') echo 'selected'; ?>>Pending</option>
						<option value="spam" <?php if ($comment->status === 'spam') echo 'selected'; ?>>Spam</option>
					</select>
					<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
					<input type="hidden" name="comments[]"   value="<?php echo $comment->id; ?>">
			    </div>
			</form>
			<a href="#" class="btn btn-pure btn-xs tooltipped tooltipped-n js-confirm-delete" data-item="comment" data-form="delete-form-<?php echo $comment->id; ?>" data-tooltip="Delete comment">
				<span class="glyph-icon glyph-icon-trash-o icon-md"></span>
			</a>
			
			<form method="post" id="delete-form-<?php echo $comment->id; ?>" style="display: none">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
				<input type="hidden" name="bulk_action"  value="delete">
				<input type="hidden" name="comments[]"   value="<?php echo $comment->id; ?>">
			</form>

		</div>
	</div>
</div>