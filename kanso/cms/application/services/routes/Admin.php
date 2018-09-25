<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

/**
 * CMS Application admin routes
 *
 * @author Joe J. Howard
 */

# Admin login
$router->get('/admin/login/',  '\kanso\cms\admin\controllers\Accounts@login', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/login/', '\kanso\cms\admin\controllers\Accounts@login', '\kanso\cms\admin\models\Accounts');

# Admin logout
$router->get('/admin/logout/',  '\kanso\cms\admin\controllers\Accounts@logout', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/logout/', '\kanso\cms\admin\controllers\Accounts@logout', '\kanso\cms\admin\models\Accounts');

# Admin forgot pass
$router->get('/admin/forgot-password/',  '\kanso\cms\admin\controllers\Accounts@forgotPassword', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/forgot-password/', '\kanso\cms\admin\controllers\Accounts@forgotPassword', '\kanso\cms\admin\models\Accounts');

# Admin forgot username
$router->get('/admin/forgot-username/',  '\kanso\cms\admin\controllers\Accounts@forgotUsername', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/forgot-username/', '\kanso\cms\admin\controllers\Accounts@forgotUsername', '\kanso\cms\admin\models\Accounts');

# Admin reset password
$router->get('/admin/reset-password/',  '\kanso\cms\admin\controllers\Accounts@resetPassword', '\kanso\cms\admin\models\Accounts');
$router->post('/admin/reset-password/', '\kanso\cms\admin\controllers\Accounts@resetPassword', '\kanso\cms\admin\models\Accounts');

# Admin posts
$router->get('/admin/posts/',  	  '\kanso\cms\admin\controllers\Dashboard@posts', '\kanso\cms\admin\models\Posts');
$router->get('/admin/posts/(:all)',  '\kanso\cms\admin\controllers\Dashboard@posts', '\kanso\cms\admin\models\Posts');
$router->post('/admin/posts/',  	  '\kanso\cms\admin\controllers\Dashboard@posts', '\kanso\cms\admin\models\Posts');
$router->post('/admin/posts/(:all)', '\kanso\cms\admin\controllers\Dashboard@posts', '\kanso\cms\admin\models\Posts');

# Admin pages
$router->get('/admin/pages/',  	  '\kanso\cms\admin\controllers\Dashboard@pages', '\kanso\cms\admin\models\Posts');
$router->get('/admin/pages/(:all)',  '\kanso\cms\admin\controllers\Dashboard@pages', '\kanso\cms\admin\models\Posts');
$router->post('/admin/pages/',  	  '\kanso\cms\admin\controllers\Dashboard@pages', '\kanso\cms\admin\models\Posts');
$router->post('/admin/pages/(:all)', '\kanso\cms\admin\controllers\Dashboard@pages', '\kanso\cms\admin\models\Posts');

# Admin tags
$router->get('/admin/tags/',        '\kanso\cms\admin\controllers\Dashboard@tags', '\kanso\cms\admin\models\Tags');
$router->get('/admin/tags/(:all)',  '\kanso\cms\admin\controllers\Dashboard@tags', '\kanso\cms\admin\models\Tags');
$router->post('/admin/tags/',  	  '\kanso\cms\admin\controllers\Dashboard@tags', '\kanso\cms\admin\models\Tags');
$router->post('/admin/tags/(:all)', '\kanso\cms\admin\controllers\Dashboard@tags', '\kanso\cms\admin\models\Tags');

# Admin categories
$router->get('/admin/categories/',        '\kanso\cms\admin\controllers\Dashboard@categories', '\kanso\cms\admin\models\Categories');
$router->get('/admin/categories/(:all)',  '\kanso\cms\admin\controllers\Dashboard@categories', '\kanso\cms\admin\models\Categories');
$router->post('/admin/categories/',  	    '\kanso\cms\admin\controllers\Dashboard@categories', '\kanso\cms\admin\models\Categories');
$router->post('/admin/categories/(:all)', '\kanso\cms\admin\controllers\Dashboard@categories', '\kanso\cms\admin\models\Categories');

# Admin comments
$router->get('/admin/comments/',          '\kanso\cms\admin\controllers\Dashboard@comments', '\kanso\cms\admin\models\Comments');
$router->get('/admin/comments/(:all)',    '\kanso\cms\admin\controllers\Dashboard@comments', '\kanso\cms\admin\models\Comments');
$router->post('/admin/comments/',  	      '\kanso\cms\admin\controllers\Dashboard@comments', '\kanso\cms\admin\models\Comments');
$router->post('/admin/comments/(:all)',   '\kanso\cms\admin\controllers\Dashboard@comments', '\kanso\cms\admin\models\Comments');

# Admin comment authors
$router->get('/admin/comment-users/',         '\kanso\cms\admin\controllers\Dashboard@commentUsers', '\kanso\cms\admin\models\CommentUsers');
$router->get('/admin/comment-users/(:all)',   '\kanso\cms\admin\controllers\Dashboard@commentUsers', '\kanso\cms\admin\models\CommentUsers');
$router->post('/admin/comment-users/',  	    '\kanso\cms\admin\controllers\Dashboard@commentUsers', '\kanso\cms\admin\models\CommentUsers');
$router->post('/admin/comments-users/(:all)', '\kanso\cms\admin\controllers\Dashboard@commentUsers', '\kanso\cms\admin\models\CommentUsers');

# Admin settings
$router->get('/admin/settings/',         '\kanso\cms\admin\controllers\Dashboard@settings', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/',        '\kanso\cms\admin\controllers\Dashboard@settings', '\kanso\cms\admin\models\Settings');

# Admin account settings
$router->get('/admin/settings/account/',  '\kanso\cms\admin\controllers\Dashboard@settingsAccount', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/account/', '\kanso\cms\admin\controllers\Dashboard@settingsAccount', '\kanso\cms\admin\models\Settings');

# Admin author settings
$router->get('/admin/settings/author/',  '\kanso\cms\admin\controllers\Dashboard@settingsAuthor', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/author/', '\kanso\cms\admin\controllers\Dashboard@settingsAuthor', '\kanso\cms\admin\models\Settings');

# Admin kanso settings
$router->get('/admin/settings/kanso/',  '\kanso\cms\admin\controllers\Dashboard@settingsKanso', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/kanso/', '\kanso\cms\admin\controllers\Dashboard@settingsKanso', '\kanso\cms\admin\models\Settings');

# Admin access settings
$router->get('/admin/settings/access/',  '\kanso\cms\admin\controllers\Dashboard@settingsAccess', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/access/', '\kanso\cms\admin\controllers\Dashboard@settingsAccess', '\kanso\cms\admin\models\Settings');

# Admin kanso users
$router->get('/admin/settings/users/',  '\kanso\cms\admin\controllers\Dashboard@settingsUsers', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/users/', '\kanso\cms\admin\controllers\Dashboard@settingsUsers', '\kanso\cms\admin\models\Settings');

# Admin kanso tools
$router->get('/admin/settings/tools/',  '\kanso\cms\admin\controllers\Dashboard@settingsTools', '\kanso\cms\admin\models\Settings');
$router->post('/admin/settings/tools/', '\kanso\cms\admin\controllers\Dashboard@settingsTools', '\kanso\cms\admin\models\Settings');

# Admin writer
$router->get('/admin/writer/',        '\kanso\cms\admin\controllers\Dashboard@writer', '\kanso\cms\admin\models\Writer');
$router->get('/admin/writer/(:all)',  '\kanso\cms\admin\controllers\Dashboard@writer', '\kanso\cms\admin\models\Writer');
$router->post('/admin/writer/',       '\kanso\cms\admin\controllers\Dashboard@writer', '\kanso\cms\admin\models\Writer');
$router->post('/admin/writer/(:any)', '\kanso\cms\admin\controllers\Dashboard@writer', '\kanso\cms\admin\models\Writer');

# Admin media
$router->get('/admin/media/',          '\kanso\cms\admin\controllers\Dashboard@mediaLibrary', '\kanso\cms\admin\models\MediaLibrary');
$router->post('/admin/media-library/', '\kanso\cms\admin\controllers\Dashboard@mediaLibrary', '\kanso\cms\admin\models\MediaLibrary');

# Admin error logs
$router->get('/admin/error-logs/',  '\kanso\cms\admin\controllers\Dashboard@errorLogs', '\kanso\cms\admin\models\ErrorLogs');
$router->post('/admin/error-logs/', '\kanso\cms\admin\controllers\Dashboard@errorLogs', '\kanso\cms\admin\models\ErrorLogs');
