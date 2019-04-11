<?php

// Special case for email previews
if ($ADMIN_PAGE_TYPE === 'emailPreview')
{
	require_once('dash-email-preview.php');

	return;
}

// Special case for invoices
if ($ADMIN_PAGE_TYPE === 'invoice')
{
	require_once('templates/ecommerce/invoice.php');

	return;
}

$_TEMPLATES_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates';

require_once('functions.php');

// Include the header
require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'header.php');

// Include appropriate page templates
if ($ADMIN_PAGE_TYPE === 'login')
{
	require_once('account-login.php');
}
elseif ($ADMIN_PAGE_TYPE === 'register')
{
	require_once('account-register.php');
}
elseif ($ADMIN_PAGE_TYPE === 'forgotpassword')
{
	require_once('account-forgotpass.php');
}
elseif ($ADMIN_PAGE_TYPE === 'forgotusername')
{
	require_once('account-forgotusername.php');
}
elseif ($ADMIN_PAGE_TYPE === 'resetpassword')
{
	require_once('account-resetpassword.php');
}
elseif ($ADMIN_PAGE_TYPE === 'writer')
{
	require_once('dash-writer.php');
}
elseif ($ADMIN_PAGE_TYPE === 'posts' || $ADMIN_PAGE_TYPE === 'pages' || $ADMIN_PAGE_TYPE === 'customposts')
{
	require_once('dash-posts.php');
}
elseif ($ADMIN_PAGE_TYPE === 'tags')
{
	require_once('dash-tags.php');
}
elseif ($ADMIN_PAGE_TYPE === 'categories')
{
	require_once('dash-categories.php');
}
elseif ($ADMIN_PAGE_TYPE === 'comments')
{
	require_once('dash-comments.php');
}
elseif ($ADMIN_PAGE_TYPE === 'commentUsers')
{
	require_once('dash-comment-users.php');
}
elseif ($ADMIN_PAGE_TYPE === 'mediaLibrary')
{
	require_once('dash-media.php');
}
elseif ($ADMIN_PAGE_TYPE === 'leads')
{
	require_once('dash-leads.php');
}
elseif (
	$ADMIN_PAGE_TYPE === 'settings' ||
	$ADMIN_PAGE_TYPE === 'settingsAccount' ||
	$ADMIN_PAGE_TYPE === 'settingsAuthor' ||
	$ADMIN_PAGE_TYPE === 'settingsKanso' ||
	$ADMIN_PAGE_TYPE === 'settingsAccess' ||
	$ADMIN_PAGE_TYPE === 'settingsUsers' ||
	$ADMIN_PAGE_TYPE === 'settingsTools' ||
	$ADMIN_PAGE_TYPE === 'settingsAnalytics')
{
	require_once('dash-settings.php');
}
elseif ($ADMIN_PAGE_TYPE === 'errorLogs')
{
	require_once('dash-error-logs.php');
}
elseif ($ADMIN_PAGE_TYPE === 'emailLogs')
{
	require_once('dash-email-logs.php');
}
elseif ($ADMIN_PAGE_TYPE === 'customPage')
{
	require_once('dash-custom-page.php');
}

// Include the sidebar
if (admin_is_dash())
{
	require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'sidebar.php');
}

// Include the footer
require_once($_TEMPLATES_DIR . DIRECTORY_SEPARATOR . 'footer.php');
