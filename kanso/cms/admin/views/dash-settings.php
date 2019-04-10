<!-- PAGE WRAP -->
<div class="dash-wrap js-dash-wrap">

	<!-- HEADING -->
	<section class="page-heading">
		<h1>Settings</h1>
	</section>

	<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'post-message.php'); ?>

	<!-- TAB NAV -->
	<ul class="tab-nav tab-border">
		<li><a href="/admin/settings/account/"   <?php if ($active_tab === 'account') echo 'class="active"'; ?>>Account</a></li>
	    <li><a href="/admin/settings/author/"    <?php if ($active_tab === 'author') echo 'class="active"'; ?>>Author</a></li>
	    <?php if ($USER->role === 'administrator') : ?>
	    <li><a href="/admin/settings/kanso/"     <?php if ($active_tab === 'kanso') echo 'class="active"'; ?>>Kanso</a></li>
	    <li><a href="/admin/settings/access/"    <?php if ($active_tab === 'access') echo 'class="active"'; ?>>Access &amp; Security</a></li>
	    <li><a href="/admin/settings/users/"     <?php if ($active_tab === 'users') echo 'class="active"'; ?>>Users</a></li>
	    <li><a href="/admin/settings/analytics/" <?php if ($active_tab === 'analytics') echo 'class="active"'; ?>>Analytics</a></li>
	    <li><a href="/admin/settings/tools/"     <?php if ($active_tab === 'tools') echo 'class="active"'; ?>>Tools</a></li>
	   	<?php endif; ?>
	</ul>

	<!-- SETTINGS FORM -->
	<?php if ($active_tab === 'account') : ?>
		<!-- ACCOUNT -->
		<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'account.php'); ?>
		
	<?php elseif ($active_tab === 'author') : ?>
		<!-- AUTHOR -->
		<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'author.php'); ?>
		<!-- MEDIA LIBRARY -->
		<div class="avatar-media-wrapper js-triggerable-media">
			<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'media-library.php'); ?>
		</div>

	<?php elseif ($active_tab === 'kanso') : ?>
		<!-- KANSO -->
		<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'kanso.php'); ?>

	<?php elseif ($active_tab === 'access') : ?>
		<!-- ACCESS/SECURITY -->
		<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'access.php'); ?>

	<?php elseif ($active_tab === 'users') : ?>
	 	<!-- USERS -->
	    <?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'users.php'); ?>

	<?php elseif ($active_tab === 'tools') : ?>
	    <!-- TOOLS -->
	    <?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'tools.php'); ?>

	<?php elseif ($active_tab === 'analytics') : ?>
		<!-- ACCESS/SECURITY -->
		<?php require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'analytics.php'); ?>
	<?php endif; ?>

</div>

