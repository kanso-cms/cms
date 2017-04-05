<?php
/**
 * Admin.php
 *
 * This file is the what gets loaded into Kanso's
 * View when a request for a page in the admin panel
 * is successfully processed.
 */

/********************************************************************************
* Admin includes
*******************************************************************************/

$_TEMPLATES_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'Templates';
$USER = $ADMIN_INCLUDES->user();

# Include the header 
require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'header.php');

# Include appropriate page templates
if ($ADMIN_PAGE_TYPE === 'login') {
	require_once('account-login.php');
}
else if ($ADMIN_PAGE_TYPE === 'register') {
	require_once('account-register.php');
}
else if ($ADMIN_PAGE_TYPE === 'forgotpassword') {
	require_once('account-forgotpass.php');
}
else if ($ADMIN_PAGE_TYPE === 'forgotusername') {
	require_once('account-forgotusername.php');
}
else if ($ADMIN_PAGE_TYPE === 'resetpassword') {
	require_once('account-resetpassword.php');
}
else if ($ADMIN_PAGE_TYPE === 'writer') {
	require_once('dash-writer.php');
}
else if ($ADMIN_PAGE_TYPE === 'articles') {
	require_once('dash-articles.php');
}
else if ($ADMIN_PAGE_TYPE === 'pages') {
	require_once('dash-pages.php');
}
else if ($ADMIN_PAGE_TYPE === 'tags') {
	require_once('dash-tags.php');
}
else if ($ADMIN_PAGE_TYPE === 'categories') {
	require_once('dash-categories.php');
}
else if ($ADMIN_PAGE_TYPE === 'comments') {
	require_once('dash-comments.php');
}
else if ($ADMIN_PAGE_TYPE === 'commentUsers') {
	require_once('dash-comment-users.php');
}
else if ($ADMIN_PAGE_TYPE === 'media') {
	require_once('dash-media.php');
}
else if ($ADMIN_PAGE_TYPE === 'settings' || 
		 $ADMIN_PAGE_TYPE === 'settingsAccount' || 
		 $ADMIN_PAGE_TYPE === 'settingsAuthor' || 
		 $ADMIN_PAGE_TYPE === 'settingsKanso' || 
		 $ADMIN_PAGE_TYPE === 'settingsUsers' || 
		 $ADMIN_PAGE_TYPE === 'settingsTools') {
	require_once('dash-settings.php');
}
else if (isset($CUSTOM_TEMPLATE)) {
	require_once('dash-custom.php');
}

# Include the sidebar
if ($ADMIN_INCLUDES->isDash()) require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'sidebar.php');

# Include the footer
require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'footer.php');
