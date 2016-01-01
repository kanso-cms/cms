	<div class="site-container cleafix">
		<div class="panel">
			
			<?php // <!-- TABS NAVIGATION --> 

				# What tab is being requested?
				$url = $Kanso->Environment['REQUEST_URL'];
				$request = str_replace($Kanso->Environment['HTTP_HOST'].'/admin/', "", $url);
				$ADMIN_ACTIVE_TAB = trim($request, '/');
			?> 

			<div class="tabs-wrap js-tabs-wrap js-url-tabs">
				<ul>
					<li><a data-tab-url="articles" data-tab-title="Articles" data-tab="posts-panel" href="#" class="<?php echo $ADMIN_ACTIVE_TAB === 'articles' ? 'active' : '';?>">Articles</a></li>
					<li><a data-tab-url="taxonomy" data-tab-title="Taxonomy" data-tab="tags-panel" href="#" class="<?php echo $ADMIN_ACTIVE_TAB === 'taxonomy' ? 'active' : '';?>">Tags &amp; Categories</a></li>
					<li><a data-tab-url="comments" data-tab-title="Comments" data-tab="comments-panel" href="#" class="<?php echo $ADMIN_ACTIVE_TAB === 'comments' ? 'active' : '';?>">Comments</a></li>
				</ul>
			</div>

			<div class="tab-panel <?php echo $ADMIN_ACTIVE_TAB === 'articles' ? 'active' : '';?>" id="posts-panel">
				<?php require_once($ADMIN_INCLUDES_DIR.'Admin/Sections/articles.php');?>
			</div>

			<div class="tab-panel <?php echo $ADMIN_ACTIVE_TAB === 'taxonomy' ? 'active' : '';?>" id="tags-panel">
				<?php require_once($ADMIN_INCLUDES_DIR.'Admin/Sections/tags.php');?>
			</div>

			<div class="tab-panel <?php echo $ADMIN_ACTIVE_TAB === 'comments' ? 'active' : '';?>" id="comments-panel">
				<?php require_once($ADMIN_INCLUDES_DIR.'Admin/Sections/comments.php');?>
			</div>

		</div>
	</div>
