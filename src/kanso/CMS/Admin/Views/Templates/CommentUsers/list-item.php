<?php

use \Kanso\Framework\Utility\Str;
use \Kanso\Framework\Utility\Pluralize;

$userSlug  =  Str::slug($commenter['email']);
$rep_text  = 'Average';
$rep_color = 'info';
if ($commenter['reputation'] < 0)
{
    $rep_text  = 'Low';
    $rep_color = 'danger';

}
else if ($commenter['reputation'] > 0 && $commenter['reputation'] < 2)
{
    $rep_text  = 'Average';
    $rep_color = 'info';

}
else if ($commenter['reputation'] > 2)
{
    $rep_text  = 'Good';
    $rep_color = 'success';
}

?>
<!-- LIST ITEM -->
<div class="row list-row">
	<div class="media">
		<div class="media-left">
			<div class="form-field">    
		        <span class="checkbox checkbox-primary">
		            <input type="checkbox" class="js-bulk-action-cb" name="users[]" id="cb-comment-<?php echo $userSlug; ?>" value="<?php echo $commenter['ip_address']; ?>" />
		            <label for="cb-comment-<?php echo $userSlug; ?>"></label>
		        </span>
		    </div>
		</div>
		<div class="media-body gutter-md">
			<div class="floor-xs">
				<span class="avatar inline-block v-middle">
					<img src="<?php echo get_gravatar($commenter['email'], 64, true); ?>" width="32" height="32">
				</span>
				<?php if ($commenter['blacklisted']) : ?>
	        		&nbsp;&nbsp;<span class="status status-danger tooltipped tooltipped-n" data-tooltip="Blacklisted"></span>
	        	<?php endif; ?>
	        	<?php if ($commenter['whitelisted']) : ?>
	        		&nbsp;&nbsp;<span class="status status-success tooltipped tooltipped-n" data-tooltip="whitelisted"></span>
	        	<?php endif; ?>
				<span class="color-black font-bolder">
					&nbsp;&nbsp;<?php echo $commenter['name']; ?>
				</span>
				<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
				<?php echo $commenter['email']; ?>
				<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
				<?php echo $commenter['ip_address']; ?>
	        </div>
	        <div class="color-gray">
	        	<span class="label label-<?php echo $rep_color;?> tooltipped tooltipped-n" data-tooltip="Reputation">
	        		<?php echo strtoupper($rep_text);?>
	        	</span>
	        	
	        	<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	with <?php echo $commenter['posted_count']; ?> <?php echo Pluralize::convert('comment', $commenter['posted_count']);?>
	        	<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	<?php echo $commenter['spam_count']; ?> spam
			</div>
		</div>
		<div class="media-right nowrap">
			<div class="btn-group inline-block">
				<div class="drop-container">
				    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
				        Moderate 
				        &nbsp;<span class="caret-s"></span>
				    </button>
				    <div class="drop-menu drop-se">
				        <div class="drop">
				            <ul>
				            	<li class="drop-header">Add IP to:</li>
				                <li><a href="#" onclick="document.getElementById('blacklist-<?php echo $userSlug; ?>').submit()" <?php if ($commenter['blacklisted']) echo 'class="selected"';?>>Blacklist</a></li>
				                <li><a href="#" onclick="document.getElementById('whitelist-<?php echo $userSlug; ?>').submit()" <?php if ($commenter['whitelisted']) echo 'class="selected"';?>>Whitelist</a></li>
				                <li><a href="#" onclick="document.getElementById('nolist-<?php echo $userSlug; ?>').submit()" <?php if (!$commenter['blacklisted'] && !$commenter['whitelisted']) echo 'class="selected"';?>>No list</a></li>
				        	</ul>
				        </div>
				    </div>
				</div>
			    <div class="drop-container">
				    <button type="button" class="btn btn-default btn-dropdown js-drop-trigger">
				       	<span class="glyph-icon glyph-icon-comments"></span>
				        &nbsp;<span class="caret-s"></span>
				    </button>
				    <div class="drop-menu drop-se">
				        <div class="drop">
				            <ul>
				            	<li class="drop-header">All comments from:</li>
				                <li><a href="/admin/comments/?search=name:<?php echo urlencode($commenter['name']); ?>"><?php echo $commenter['name'];?></a></li>
				                <li><a href="/admin/comments/?search=email:<?php echo urlencode($commenter['email']); ?>"><?php echo $commenter['email'];?></a></li>
				                <li><a href="/admin/comments/?search=ip_address:<?php echo urlencode($commenter['ip_address']); ?>"><?php echo $commenter['ip_address'];?></a></li>
				        	</ul>
				        </div>
				    </div>
				</div>
			</div>
			<form method="post" id="blacklist-<?php echo $userSlug;?>" style="display: none;">
				<input type="hidden" name="bulk_action"   value="blacklist">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="users[]"   value="<?php echo $commenter['ip_address'];?>">
			</form>
			<form method="post" id="whitelist-<?php echo $userSlug;?>" style="display: none;">
				<input type="hidden" name="bulk_action"   value="whitelist">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="users[]"   value="<?php echo $commenter['ip_address'];?>">
			</form>
			<form method="post" id="nolist-<?php echo $userSlug;?>" style="display: none;">
				<input type="hidden" name="bulk_action"   value="nolist">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="users[]"   value="<?php echo $commenter['ip_address'];?>">
			</form>
			
		</div>
	</div>
</div>