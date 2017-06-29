<?php

$_TEMPLATES_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'Templates';

require_once('functions.php');

# Include the header 
require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'header.php');

# Include appropriate page templates
if ($ADMIN_PAGE_TYPE === 'login')
{
	require_once('account-login.php');
}
else if ($ADMIN_PAGE_TYPE === 'register')
{
	require_once('account-register.php');
}
else if ($ADMIN_PAGE_TYPE === 'forgotpassword')
{
	require_once('account-forgotpass.php');
}
else if ($ADMIN_PAGE_TYPE === 'forgotusername')
{
	require_once('account-forgotusername.php');
}
else if ($ADMIN_PAGE_TYPE === 'resetpassword')
{
	require_once('account-resetpassword.php');
}
else if ($ADMIN_PAGE_TYPE === 'writer')
{
	require_once('dash-writer.php');
}
else if ($ADMIN_PAGE_TYPE === 'posts' || $ADMIN_PAGE_TYPE === 'pages' || $ADMIN_PAGE_TYPE === 'customposts')
{
	require_once('dash-posts.php');
}
else if ($ADMIN_PAGE_TYPE === 'tags')
{
	require_once('dash-tags.php');
}
else if ($ADMIN_PAGE_TYPE === 'categories')
{
	require_once('dash-categories.php');
}
else if ($ADMIN_PAGE_TYPE === 'comments')
{
	require_once('dash-comments.php');
}
else if ($ADMIN_PAGE_TYPE === 'commentUsers')
{
	require_once('dash-comment-users.php');
}
else if ($ADMIN_PAGE_TYPE === 'mediaLibrary')
{
	require_once('dash-media.php');
}
else if (
	$ADMIN_PAGE_TYPE === 'settings' || 
	$ADMIN_PAGE_TYPE === 'settingsAccount' || 
	$ADMIN_PAGE_TYPE === 'settingsAuthor' || 
	$ADMIN_PAGE_TYPE === 'settingsKanso' || 
	$ADMIN_PAGE_TYPE === 'settingsUsers' || 
	$ADMIN_PAGE_TYPE === 'settingsTools' )
{
	require_once('dash-settings.php');
}
else if ($ADMIN_PAGE_TYPE === 'customPage')
{
	require_once('dash-custom-page.php');
}


# Include the sidebar
if (admin_is_dash())
{
	require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'sidebar.php');
}

# Include the footer
require_once($_TEMPLATES_DIR.DIRECTORY_SEPARATOR.'footer.php');
