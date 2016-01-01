	<div class="site-container cleafix">
		<div class="admin-settings panel">

			<?php // <!-- TABS NAVIGATION --> 

				# What tab is being requested?
				$url = $Kanso->Environment['REQUEST_URL'];
				$request = str_replace($Kanso->Environment['HTTP_HOST'].'/admin/settings/', "", $url);
				$ADMIN_ACTIVE_TAB = trim($request, '/');
			?> 
			<div class="tabs-wrap js-tabs-wrap js-url-tabs">
				<ul>
					<li><a data-tab-url="account" data-tab-title="Account" data-tab="admin-panel" href="#" <?php if ($ADMIN_ACTIVE_TAB === 'account') echo 'class="active" '; ?>>Account</a></li>
					<li><a data-tab-url="author" data-tab-title="Author" data-tab="author-panel" href="#" <?php if ($ADMIN_ACTIVE_TAB === 'author') echo 'class="active" '; ?>>Author</a></li>
					<?php if ($ADMIN_USER_DATA['role'] === 'administrator') : ?> <li><a data-tab-url="kanso" data-tab-title="Kanso" data-tab="kanso-panel" href="#" <?php if ($ADMIN_ACTIVE_TAB === 'kanso') echo 'class="active" '; ?>>Kanso</a></li><?php endif;?>
					<?php if ($ADMIN_USER_DATA['role'] === 'administrator') : ?> <li><a data-tab-url="users" data-tab-title="Users" data-tab="users-panel" href="#" <?php if ($ADMIN_ACTIVE_TAB === 'users') echo 'class="active" '; ?>>Users</a></li><?php endif;?>
					<?php if ($ADMIN_USER_DATA['role'] === 'administrator') : ?> <li><a data-tab-url="tools" data-tab-title="Tools" data-tab="tools-panel" href="#" <?php if ($ADMIN_ACTIVE_TAB === 'tools') echo 'class="active" '; ?>>Tools</a></li><?php endif;?>
				</ul>
			</div>

			<?php // <!-- ADMIN SETTINGS TAB PANE --> ?> 
			<div class="row form-section tab-panel <?php if ($ADMIN_ACTIVE_TAB === 'account') echo 'active';?> " id="admin-panel">

				<?php require_once($ADMIN_INCLUDES_DIR.'Admin/Sections/settingsAccount.php');?>

			</div>

			<?php // <!-- AUTHOR SETTINGS TAB PANE --> ?> 
			<div class="row form-section tab-panel <?php if ($ADMIN_ACTIVE_TAB === 'author') echo 'active';?>" id="author-panel">

				<?php require_once($ADMIN_INCLUDES_DIR.'Admin/Sections/settingsAuthor.php');?>

			</div>


			<?php // <!-- ONLY ADMINISTRATORS CAN MANAGE USERS, TOOLS, COMMENTS & KANSO SETTINGS --> ?> 
			
			<?php if ($ADMIN_USER_DATA['role']=== 'administrator') : ?>

				<?php // <!-- ADMIN KANSO TAB PANE --> ?> 
				<div class="row form-section tab-panel <?php if ($ADMIN_ACTIVE_TAB === 'kanso') echo 'active';?>" id="kanso-panel">

					<?php require_once($ADMIN_INCLUDES_DIR.'Admin/Sections/settingsKanso.php');?>

				</div>

				<?php // <!-- ADMIN USERS TAB PANE --> ?> 
				<div class="row form-section tab-panel <?php if ($ADMIN_ACTIVE_TAB === 'users') echo 'active';?>" id="users-panel">

					<?php require_once($ADMIN_INCLUDES_DIR.'Admin/Sections/settingsUsers.php');?>
				
				</div>

				<?php // <!-- ADMIN TOOLS TAB PANE --> ?> 
				<div class="row form-section tab-panel <?php if ($ADMIN_ACTIVE_TAB === 'tools') echo 'active';?>" id="tools-panel">

					<?php require_once($ADMIN_INCLUDES_DIR.'Admin/Sections/settingsTools.php');?>
				
				</div>
			
			<?php endif;?>

		</div>
	</div>