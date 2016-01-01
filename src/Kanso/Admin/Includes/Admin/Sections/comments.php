<?php
$pending      = count($Kanso->CRUD()->SELECT('*')->FROM('comments')->WHERE('status','=','pending')->FIND_ALL());
$pendingCount = $pending > 0 ? '('+$pending+')' : '';
?>

<div class="list-wrap comments-wrap col col-8 tablet-col-12 right-gutter js-ajax-list-wrap js-comments-wrap" data-list-type="comments">

	<div class="tabs-wrap row js-tabs-wrap">
		<ul>
			<li><a data-tab="comments-all-panel" href="#" class="active">All</a></li>
			<li><a data-tab="comments-approved-panel" href="#">Approved</a></li>
			<li><a data-tab="comments-pending-panel" href="#">Pending <?php echo $pendingCount; ?></a></li>
			<li><a data-tab="comments-spam-panel" href="#">Spam</a></li>
			<li><a data-tab="comments-deleted-panel" href="#">Deleted</a></li>
		</ul>
	</div>

	<div class="row no-roof list-powers js-list-powers">
		
		<div class="col col-05 right-gutter">
			<div class="check-wrap">
				<input id="selectAll" class="js-check-all" type="checkbox" name="selectAll">
				<label class="checkbox small" for="selectAll"></label>
			</div>
		</div>

		<div class="col col-7 no-gutter">
			<ul class="segmented-buttons ">
				<li class="js-approve">
					<a href="#" class="button">Approve</a>
				</li>
				<li class="js-spam">
					<a href="#" class="button">Spam</a>
				</li>
				<li class="js-delete">
					<a href="#" class="button">Delete</a>
				</li>
			</ul>
			<div class="search-wrap">
				<input class="input-default js-search-input small" value="" placeholder="Search">
				<svg class="search-icon" viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#search"></use></svg>
				<a href="#" class="close-icon js-cancel-search">
					<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#cross"></use></svg>
				</a>
			</div>
		</div>

		<div class="col col-4 no-gutter text-right right">
			<a class="expand-list label js-expand-list" href="#">Expand</a>
			<span class="label">Sort</span>
			<div class="button-dropdown align-right text-left js-button-down js-sort-list">
				<a href="#" class="button">Newest
					<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#arrow-down"></use></svg>
				</a>
				<div class="drop">
					<div>
						<a data-sort="newest" href="#">Newest</a>
						<a data-sort="oldest" href="#">Oldest</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tab-panel active js-ajax-list" data-list-name="admin_all_comments" id="comments-all-panel"></div>

	<div class="tab-panel js-ajax-list" data-list-name="admin_approved_comments" id="comments-approved-panel"></div>

	<div class="tab-panel js-ajax-list" data-list-name="admin_pending_comments" id="comments-pending-panel"></div>

	<div class="tab-panel js-ajax-list" data-list-name="admin_spammed_comments" id="comments-spam-panel"></div>

	<div class="tab-panel js-ajax-list" data-list-name="admin_deleted_comments" id="comments-deleted-panel"></div>

	<div class="row large-V-gutter list-nav js-list-nav">

		<div class="js-page-input-wrap page-input-wrap text-left col col-9 no-gutter">
			<span>Page</span>
			<input class="input-default small js-current-page" value="1">
			<span class="js-max-pages">of 1</span>
		</div>

		<div class="text-right col col-3 no-gutter">

			<ul class="segmented-buttons right">
				<li class="js-prev">
					<a class="button" href="#">
						« Previous 
					</a>
				</li>
				<li class="js-next">
					<a class="button" href="#">
						Next »
					</a>
				</li>
			</ul>

		</div>

	</div>

</div>

<div class="comment-extras col col-4 tablet-hide left-gutter js-comment-extras">
	
	<div class="default-view js-default-view">

		<h4>Help</h4>

		<ul>
			<li><a href="#">Dealing with spam</a></li>
			<li><a href="#">Using the blacklist/whitelist</a></li>
			<li><a href="#">More moderation help</a></li>
			<li><a href="#">Moderation FAQs</a></li>
		</ul>
	</div>

	<div class="comment-info js-comment-info">
		
		<div class="panel-header col-12 col">
			<a href="#" class="close-comment-info js-close-comment-info button mini-icon">
				<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#cross"></use></svg>
			</a>
			<p class="comment-status js-comment-status"></p>
		</div>
		
		<div class="comment-content col col-12 js-comment-content">
		
		</div>

		<div class="comment-edit-reply col col-12 js-comment-edit-reply-wrap">
			<textarea class="input-default edit-reply-input js-edit-reply-input"></textarea>
			<button class="button small submit post-reply js-save-reply">Post Reply</button>
			<button class="button small submit save-edit js-save-edit">Save Edit</button>
			<button class="button small cancel js-cancel-edit">Cancel</button>
		</div>

		<div class="col col-12 clearfix row">
			<a href="#" class="reply js-reply">Reply</a>
			<span class="bullet">•</span>
			<a href="#" class="edit js-edit">Edit</a>
			<a href="#" target="_blank" class="right js-link-to-comment">View on original page</a>
		</div>

		<div class="info-tray clearfix">
		    
		    <div class="info-tray-actions col col-12 clearfix">

		    	<div class="info-user-detail left">
			        <div class="info-avatar js-avatar">
			        </div>
			        <p class="name js-name"></p>
			        <p class="email js-email"></p>
			    </div>

				<div class="button-dropdown right align-right text-left js-button-down">
					<a href="#" class="button">
						<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#user"></use></svg>
					</a>
					<div class="drop">
						<div>
							<a class="js-search-user" data-search="" href="#"></a>
							<a class="js-search-ip" data-search="" href="#"></a>
							<a class="js-search-email" data-search="" href="#"></a>
						</div>
					</div>
				</div>

				<ul class="segmented-buttons right">
					<li class="js-whitelist">
						<a href="#" class="button">Whitelist</a>
					</li>
					<li class="js-blacklist">
						<a href="#" class="button">Blacklist</a>
					</li>
				</ul>
					      
		    </div>

		    <div class="info-user-stats clearfix">
		        
		        <div class="col col-6 stat-block with-icon left-gutter">
		        	<span class="rep-icon js-rep-icon">
		        		<svg class="good icon js-good" viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#wink"></use></svg>
		        		<svg class="bad icon js-bad" viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#evil"></use></svg>
		        		<svg class="average icon js-average" viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#nuetral"></use></svg>
		        	</span>
		        	<span class="value js-reputation">
		        	</span>
		        	<span class="unit">Reputation</span>
		        </div>

		        <div class="col col-6 stat-block right-gutter">
		        	<span class="label">First comment</span>
		        	<span class="value js-first-comment-time"></span>
		        	<span class="unit js-first-comment-unit"></span>
		        </div>

		        <div class="col col-6 stat-block with-icon left-gutter">
		        	<svg class="icon" viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#screen"></use></svg>
		        	<span class="value ip js-ip-address"></span>
		        </div>

		        
	            <div class="col col-6 stat-block right-gutter">
	            	<span class="label">posted</span>
	            	<span class="value js-comment-count"></span>
	            	<span class="unit">Comments</span>
	            </div>
	            <div class="col col-6 stat-block right right-gutter">
	            	<span class="label">made</span>
	            	<span class="value js-spam-count"></span>
	            	<span class="unit">Spam</span>
	            </div>
		        

		    </div>

		</div>

	</div>

</div>
