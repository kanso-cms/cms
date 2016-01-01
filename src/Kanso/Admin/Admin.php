<?php
/**
 * Admin.php
 *
 * This file is the what gets loaded into Kanso's
 * View when a request for a page in the admin panel
 * is succefully processed.
 */

/********************************************************************************
* Admin includes
*******************************************************************************/

# Include the header 
require_once($ADMIN_INCLUDES_DIR.'header.php');

# Include appropriate page templates
if ($ADMIN_PAGE_TYPE === 'login') {
	require_once($ADMIN_INCLUDES_DIR.'Account/login.php');
}
else if ($ADMIN_PAGE_TYPE === 'resetpassword') {
	require_once($ADMIN_INCLUDES_DIR.'Account/resetPassword.php');
}
else if ($ADMIN_PAGE_TYPE === 'register') {
	require_once($ADMIN_INCLUDES_DIR.'Account/register.php');
}
else if ($ADMIN_PAGE_TYPE === 'forgotpassword') {
	require_once($ADMIN_INCLUDES_DIR.'Account/forgotPassword.php');
}
else if ($ADMIN_PAGE_TYPE === 'forgotusername') {
	require_once($ADMIN_INCLUDES_DIR.'Account/forgotUsername.php');
}
else if ($ADMIN_PAGE_TYPE === 'articles' || $ADMIN_PAGE_TYPE === 'taxonomy' || $ADMIN_PAGE_TYPE === 'comments') {
	require_once($ADMIN_INCLUDES_DIR.'Admin/articles.php');
}
else if ($ADMIN_PAGE_TYPE === 'settings') {
	require_once($ADMIN_INCLUDES_DIR.'Admin/settings.php');
}
else if ($ADMIN_PAGE_TYPE === 'writer') {
	require_once($ADMIN_INCLUDES_DIR.'Admin/writer.php');
}

# Include the footer
require_once ($ADMIN_INCLUDES_DIR.'footer.php');
