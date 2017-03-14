<?php 
	$userSlug  = \Kanso\Utility\Str::slugFilter($user['email']);
	$rep_text  = 'Average';
	$rep_color = 'info';
	if ($user['reputation'] < 0) {
        $rep_text  = 'Low';
        $rep_color = 'danger';

    } else if ($user['reputation'] > 0 && $user['reputation'] < 2) {
        $rep_text  = 'Average';
        $rep_color = 'info';

    } else if ($user['reputation'] > 2) {
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
		            <input type="checkbox" class="js-bulk-action-cb" name="users[]" id="cb-comment-<?php echo $userSlug; ?>" value="<?php echo $user['ip_address']; ?>" />
		            <label for="cb-comment-<?php echo $userSlug; ?>"></label>
		        </span>
		    </div>
		</div>
		<div class="media-body gutter-md">
			<div class="floor-xs">
				<span class="avatar inline-block v-middle">
					<img src="<?php echo $user['avatar']; ?>" width="32" height="32">
				</span>
				<?php if ($user['blacklisted']) : ?>
	        		&nbsp;&nbsp;<span class="status status-danger tooltipped tooltipped-n" data-tooltip="Blacklisted"></span>
	        	<?php endif; ?>
	        	<?php if ($user['whitelisted']) : ?>
	        		&nbsp;&nbsp;<span class="status status-success tooltipped tooltipped-n" data-tooltip="whitelisted"></span>
	        	<?php endif; ?>
				<span class="color-black font-bolder">
					&nbsp;&nbsp;<?php echo $user['name']; ?>
				</span>
				<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
				<?php echo $user['email']; ?>
				<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
				<?php echo $user['ip_address']; ?>
	        </div>
	        <div class="color-gray">
	        	<span class="label label-<?php echo $rep_color;?> tooltipped tooltipped-n" data-tooltip="Reputation">
	        		<?php echo strtoupper($rep_text);?>
	        	</span>
	        	
	        	<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	with <?php echo $user['posted_count']; ?> <?php echo \Kanso\Utility\Pluralize::convert('comment', $user['posted_count']);?>
	        	<span class="p6 color-gray-light">&nbsp;&nbsp;•&nbsp;&nbsp;</span>
	        	<?php echo $user['spam_count']; ?> spam
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
				                <li><a href="#" onclick="document.getElementById('blacklist-<?php echo $userSlug; ?>').submit()" <?php if ($user['blacklisted']) echo 'class="selected"';?>>Blacklist</a></li>
				                <li><a href="#" onclick="document.getElementById('whitelist-<?php echo $userSlug; ?>').submit()" <?php if ($user['whitelisted']) echo 'class="selected"';?>>Whitelist</a></li>
				                <li><a href="#" onclick="document.getElementById('nolist-<?php echo $userSlug; ?>').submit()" <?php if (!$user['blacklisted'] && !$user['whitelisted']) echo 'class="selected"';?>>No list</a></li>
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
				                <li><a href="/admin/comments/?search=name:<?php echo urlencode($user['name']); ?>"><?php echo $user['name'];?></a></li>
				                <li><a href="/admin/comments/?search=email:<?php echo urlencode($user['email']); ?>"><?php echo $user['email'];?></a></li>
				                <li><a href="/admin/comments/?search=ip_address:<?php echo urlencode($user['ip_address']); ?>"><?php echo $user['ip_address'];?></a></li>
				        	</ul>
				        </div>
				    </div>
				</div>
			</div>
			<form method="post" id="blacklist-<?php echo $userSlug;?>" style="display: none;">
				<input type="hidden" name="bulk_action"   value="blacklist">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="users[]"   value="<?php echo $user['ip_address'];?>">
			</form>
			<form method="post" id="whitelist-<?php echo $userSlug;?>" style="display: none;">
				<input type="hidden" name="bulk_action"   value="whitelist">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="users[]"   value="<?php echo $user['ip_address'];?>">
			</form>
			<form method="post" id="nolist-<?php echo $userSlug;?>" style="display: none;">
				<input type="hidden" name="bulk_action"   value="nolist">
				<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
				<input type="hidden" name="users[]"   value="<?php echo $user['ip_address'];?>">
			</form>
			
		</div>
	</div>
</div>